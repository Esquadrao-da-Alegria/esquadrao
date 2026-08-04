<?php

namespace App\Notifications;

use App\Models\Visita;
use App\Models\VisitaRelatorio;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RelatorioVisitaNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Visita $visita,
        public VisitaRelatorio $relatorio,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $hospitalNome = $this->visita->hospital?->nome ?? 'Visita';
        $autorNome    = $this->relatorio->autor?->name ?? 'Um integrante';
        $url          = route('visitas.relatorios.show', [
            'visita'    => $this->visita->id,
            'relatorio' => $this->relatorio->id,
        ]);

        $resumoCurto = mb_strlen($this->relatorio->resumo) > 200
            ? mb_substr($this->relatorio->resumo, 0, 200) . '…'
            : $this->relatorio->resumo;

        return (new MailMessage)
            ->subject("[Esquadrão da Alegria] Novo relatório de visita - {$hospitalNome}")
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Um novo relatório de visita foi enviado por {$autorNome} para a visita realizada no {$hospitalNome}.")
            ->line("Resumo do relatório:")
            ->line('"' . $resumoCurto . '"')
            ->action('Visualizar relatório completo', $url)
            ->line('Obrigado por sua dedicação e participação nas atividades!');
    }
}
