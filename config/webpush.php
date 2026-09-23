<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chaves VAPID para Web Push
    |--------------------------------------------------------------------------
    |
    | As chaves pública e privada são utilizadas para assinar as requisições
    | enviadas aos provedores de push (Google FCM, Mozilla autopush, Apple WebPush).
    |
    */
    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:contato@esquadraodaalegria.org.br'),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações dos Lembretes Push
    |--------------------------------------------------------------------------
    |
    | Intervalos padrão para notificações de eventos e janela de tolerância
    | para evitar envios de lembretes obsoletos caso o scheduler atrase.
    |
    */
    'lembretes' => [
        'eventos' => [
            'intervalo_longo_horas' => (int) env('WEBPUSH_EVENTO_INTERVALO_LONGO', 24),
            'intervalo_curto_horas' => (int) env('WEBPUSH_EVENTO_INTERVALO_CURTO', 1),
            'tipos_permitidos' => ['reuniao', 'oficina', 'evento'],
        ],
        'janela_validade_minutos' => (int) env('WEBPUSH_JANELA_VALIDADE_MINUTOS', 60),
    ],

];
