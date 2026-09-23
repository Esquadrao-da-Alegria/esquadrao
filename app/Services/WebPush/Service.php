<?php

namespace App\Services\WebPush;

// LIBS EXTERNAS
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

// MODELS
use App\Models\PushSubscription;

class Service
{
    private WebPush $webPush;

    public function __construct(?WebPush $webPush = null)
    {
        $this->webPush = $webPush ?? new WebPush([
            'VAPID' => [
                'subject' => config('webpush.vapid.subject'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ]);
    }

    /**
     * Envia uma notificação Web Push para a subscription indicada.
     * Retorna array com ['sucesso' => bool, 'invalido' => bool, 'erro' => ?string].
     */
    public function enviar(PushSubscription $subscription, array $payload): array
    {
        if (! $subscription->estaAtiva()) {
            return [
                'sucesso' => false,
                'invalido' => true,
                'erro' => 'Subscription inativa ou invalidada.',
            ];
        }

        $minishlinkSub = Subscription::create([
            'endpoint' => $subscription->endpoint,
            'publicKey' => $subscription->p256dh,
            'authToken' => $subscription->auth,
        ]);

        $this->webPush->queueNotification(
            $minishlinkSub,
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );

        $reports = $this->webPush->flush();

        foreach ($reports as $report) {
            if ($report->isSuccess()) {
                return [
                    'sucesso' => true,
                    'invalido' => false,
                    'erro' => null,
                ];
            }

            $reason = $report->getReason();
            $ehPermanente = $report->isSubscriptionExpired();

            if ($ehPermanente) {
                $subscription->invalidar();
            }

            return [
                'sucesso' => false,
                'invalido' => $ehPermanente,
                'erro' => $reason,
            ];
        }

        return [
            'sucesso' => false,
            'invalido' => false,
            'erro' => 'Nenhum relatório de envio retornado.',
        ];
    }
}
