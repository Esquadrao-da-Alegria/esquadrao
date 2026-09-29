<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NotificationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $response = $this->get('/configuracoes/notificacoes');

        $response->assertRedirect('/login');
    }

    public function test_usuario_autenticado_pode_visualizar_preferencias_de_notificacao(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/configuracoes/notificacoes');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Preferencias/Notificacoes')
                ->has('vapidPublicKey')
            );
    }
}
