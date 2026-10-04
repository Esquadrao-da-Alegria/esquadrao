<?php

namespace App\Jobs\Lembrete\Entrega;

// LIBS EXTERNAS
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

// SERVICES
use App\Services\WebPush\Service as WebPushService;

// MODELS
use App\Models\LembreteEntrega;

class Job implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $entregaId,
        private readonly array $notificacao,
    ) {
    }

    public function handle(WebPushService $service): void
    {
        $entrega = LembreteEntrega::query()->with(['subscription', 'lembrete'])->find($this->entregaId);

        if (! $entrega || $entrega->status === LembreteEntrega::STATUS_SUCESSO || ! $entrega->subscription?->estaAtiva()) {
            return;
        }

        if ($entrega->lembrete?->estaCancelado()) {
            $entrega->update([
                'status' => LembreteEntrega::STATUS_FALHA,
                'erro' => 'Lembrete cancelado antes da entrega.',
            ]);
            return;
        }

        try {
            $resultado = $service->enviar($entrega->subscription, $this->notificacao);
            $sucesso = $resultado['sucesso'] ?? false;

            $entrega->update([
                'status' => $sucesso ? LembreteEntrega::STATUS_SUCESSO : LembreteEntrega::STATUS_FALHA,
                'enviado_em' => $sucesso ? now() : null,
                'erro' => $sucesso ? null : ($resultado['erro'] ?? 'Falha ao enviar notificação push.'),
            ]);
        } catch (Throwable $th) {
            $entrega->update([
                'status' => LembreteEntrega::STATUS_FALHA,
                'erro' => 'Falha ao enviar notificação push.',
            ]);

            report($th);
        }
    }
}
