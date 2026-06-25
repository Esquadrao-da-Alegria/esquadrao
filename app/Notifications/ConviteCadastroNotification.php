<?php

namespace App\Notifications;

use App\Models\ConviteCadastro;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConviteCadastroNotification extends Notification
{
    use Queueable;

    public function __construct(
        private ConviteCadastro $convite,
        private string $token,
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('convites.show', ['token' => $this->token]);

        return (new MailMessage)
            ->subject('Complete seu cadastro no Esquadrão da Alegria')
            ->greeting('Olá!')
            ->line('Você recebeu um convite para completar seu cadastro como voluntário no Esquadrão da Alegria.')
            ->action('Completar cadastro', $url)
            ->line('Este convite expira em '.$this->convite->expira_em?->format('d/m/Y H:i').'.')
            ->line('Se você não esperava este convite, ignore esta mensagem.');
    }
}
