<?php

namespace Tests\Feature\WebPush;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_subscription_e_associa_ao_usuario(): void
    {
        $user = User::factory()->create();

        $subscription = PushSubscription::query()->create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-1',
            'p256dh' => 'chave-p256dh-teste',
            'auth' => 'chave-auth-teste',
        ]);

        $this->assertTrue($subscription->user->is($user));
        $this->assertTrue($subscription->estaAtiva());
        $this->assertNull($subscription->invalidado_em);
    }

    public function test_endpoint_deve_ser_unico(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        PushSubscription::query()->create([
            'user_id' => $user1->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-unique',
            'p256dh' => 'chave-1',
            'auth' => 'auth-1',
        ]);

        $this->expectException(QueryException::class);

        PushSubscription::query()->create([
            'user_id' => $user2->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-unique',
            'p256dh' => 'chave-2',
            'auth' => 'auth-2',
        ]);
    }

    public function test_invalida_subscription_e_filtra_ativas(): void
    {
        $user = User::factory()->create();

        $subAtiva = PushSubscription::query()->create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-ativa',
            'p256dh' => 'chave-1',
            'auth' => 'auth-1',
        ]);

        $subInvalida = PushSubscription::query()->create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-invalida',
            'p256dh' => 'chave-2',
            'auth' => 'auth-2',
        ]);

        $subInvalida->invalidar();

        $this->assertFalse($subInvalida->estaAtiva());
        $this->assertNotNull($subInvalida->invalidado_em);

        $ativas = PushSubscription::ativas()->pluck('id');

        $this->assertTrue($ativas->contains($subAtiva->id));
        $this->assertFalse($ativas->contains($subInvalida->id));
    }
}
