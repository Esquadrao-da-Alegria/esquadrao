<?php

namespace Tests\Feature\WebPush;

use Tests\TestCase;

class WebPushConfigTest extends TestCase
{
    public function test_possui_valores_padrao_de_configuracao(): void
    {
        $this->assertNotEmpty(config('webpush.vapid.subject'));
        $this->assertSame(24, config('webpush.lembretes.eventos.intervalo_longo_horas'));
        $this->assertSame(1, config('webpush.lembretes.eventos.intervalo_curto_horas'));
        $this->assertSame(60, config('webpush.lembretes.janela_validade_minutos'));
        $this->assertEquals(['reuniao', 'oficina', 'evento'], config('webpush.lembretes.eventos.tipos_permitidos'));
    }
}
