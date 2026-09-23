<?php

namespace Tests\Feature\WebPush;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_nao_autenticado_nao_pode_registrar_subscription(): void
    {
        $response = $this->postJson('/push-subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/guest',
            'keys' => [
                'p256dh' => 'chave-1',
                'auth' => 'auth-1',
            ],
        ]);

        $response->assertUnauthorized();
    }

    public function test_usuario_autenticado_registra_subscription(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/push-subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-auth',
            'keys' => [
                'p256dh' => 'chave-1',
                'auth' => 'auth-1',
            ],
        ]);

        $response->assertOk()
            ->assertJson(['sucesso' => true]);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-auth',
            'invalidado_em' => null,
        ]);
    }

    public function test_ignora_user_id_enviado_pelo_cliente(): void
    {
        $user = User::factory()->create();
        $outroUser = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/push-subscriptions', [
            'user_id' => $outroUser->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-spoof',
            'keys' => [
                'p256dh' => 'chave-1',
                'auth' => 'auth-1',
            ],
        ]);

        $response->assertOk();

        $subscription = PushSubscription::query()->where('endpoint', 'https://fcm.googleapis.com/fcm/send/device-spoof')->first();
        $this->assertNotNull($subscription);
        $this->assertSame($user->id, $subscription->user_id);
        $this->assertNotSame($outroUser->id, $subscription->user_id);
    }

    public function test_atualiza_subscription_conhecida_e_reativa_se_invalida(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $sub = PushSubscription::query()->create([
            'user_id' => $user1->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-shared',
            'p256dh' => 'chave-antiga',
            'auth' => 'auth-antiga',
            'invalidado_em' => now()->subDay(),
        ]);

        $response = $this->actingAs($user2)->postJson('/push-subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-shared',
            'keys' => [
                'p256dh' => 'chave-nova',
                'auth' => 'auth-nova',
            ],
        ]);

        $response->assertOk();

        $sub->refresh();
        $this->assertSame($user2->id, $sub->user_id);
        $this->assertSame('chave-nova', $sub->p256dh);
        $this->assertNull($sub->invalidado_em);
        $this->assertSame(1, PushSubscription::query()->where('endpoint', 'https://fcm.googleapis.com/fcm/send/device-shared')->count());
    }

    public function test_desativa_subscription_do_dispositivo_atual(): void
    {
        $user = User::factory()->create();

        $sub = PushSubscription::query()->create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-desativar',
            'p256dh' => 'chave-1',
            'auth' => 'auth-1',
        ]);

        $response = $this->actingAs($user)->deleteJson('/push-subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-desativar',
        ]);

        $response->assertOk()
            ->assertJson(['sucesso' => true]);

        $sub->refresh();
        $this->assertNotNull($sub->invalidado_em);
        $this->assertFalse($sub->estaAtiva());
    }

    public function test_nao_desativa_subscription_de_outro_usuario(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $sub = PushSubscription::query()->create([
            'user_id' => $user1->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-outro',
            'p256dh' => 'chave-1',
            'auth' => 'auth-1',
        ]);

        $response = $this->actingAs($user2)->deleteJson('/push-subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-outro',
        ]);

        $response->assertOk();

        $sub->refresh();
        $this->assertNull($sub->invalidado_em);
        $this->assertTrue($sub->estaAtiva());
    }
}
