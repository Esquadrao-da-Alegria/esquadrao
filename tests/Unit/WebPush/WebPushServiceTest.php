<?php

namespace Tests\Unit\WebPush;

use App\Models\PushSubscription;
use App\Models\User;
use App\Services\WebPush\Service as WebPushService;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\WebPush;
use Mockery;
use Tests\TestCase;

class WebPushServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_envia_push_com_sucesso(): void
    {
        $user = User::factory()->create();
        $sub = PushSubscription::query()->create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-ok',
            'p256dh' => 'chave-1',
            'auth' => 'auth-1',
        ]);

        $mockWebPush = Mockery::mock(WebPush::class);
        $request = new Request('POST', $sub->endpoint);
        $response = new Response(200, [], 'OK');
        $report = new MessageSentReport($request, $response, true, 'OK');

        $mockWebPush->shouldReceive('queueNotification')
            ->once();

        $mockWebPush->shouldReceive('flush')
            ->once()
            ->andReturn((function () use ($report) {
                yield $report;
            })());

        $service = new WebPushService($mockWebPush);
        $resultado = $service->enviar($sub, ['title' => 'Teste', 'body' => 'Corpo']);

        $this->assertTrue($resultado['sucesso']);
        $this->assertFalse($resultado['invalido']);
        $this->assertNull($resultado['erro']);
        $this->assertTrue($sub->fresh()->estaAtiva());
    }

    public function test_invalida_subscription_quando_provedor_retorna_410_gone(): void
    {
        $user = User::factory()->create();
        $sub = PushSubscription::query()->create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-expired',
            'p256dh' => 'chave-2',
            'auth' => 'auth-2',
        ]);

        $mockWebPush = Mockery::mock(WebPush::class);
        $request = new Request('POST', $sub->endpoint);
        $response = new Response(410, [], 'Gone');
        $report = new MessageSentReport($request, $response, false, 'Subscription expired');

        $mockWebPush->shouldReceive('queueNotification')
            ->once();

        $mockWebPush->shouldReceive('flush')
            ->once()
            ->andReturn((function () use ($report) {
                yield $report;
            })());

        $service = new WebPushService($mockWebPush);
        $resultado = $service->enviar($sub, ['title' => 'Teste', 'body' => 'Corpo']);

        $this->assertFalse($resultado['sucesso']);
        $this->assertTrue($resultado['invalido']);
        $this->assertNotNull($resultado['erro']);

        $sub->refresh();
        $this->assertFalse($sub->estaAtiva());
        $this->assertNotNull($sub->invalidado_em);
    }

    public function test_falha_transiente_nao_invalida_subscription(): void
    {
        $user = User::factory()->create();
        $sub = PushSubscription::query()->create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-temp-error',
            'p256dh' => 'chave-3',
            'auth' => 'auth-3',
        ]);

        $mockWebPush = Mockery::mock(WebPush::class);
        $request = new Request('POST', $sub->endpoint);
        $response = new Response(500, [], 'Internal Server Error');
        $report = new MessageSentReport($request, $response, false, 'Internal Server Error');

        $mockWebPush->shouldReceive('queueNotification')
            ->once();

        $mockWebPush->shouldReceive('flush')
            ->once()
            ->andReturn((function () use ($report) {
                yield $report;
            })());

        $service = new WebPushService($mockWebPush);
        $resultado = $service->enviar($sub, ['title' => 'Teste', 'body' => 'Corpo']);

        $this->assertFalse($resultado['sucesso']);
        $this->assertFalse($resultado['invalido']);

        $sub->refresh();
        $this->assertTrue($sub->estaAtiva());
        $this->assertNull($sub->invalidado_em);
    }

    public function test_subscription_previamente_invalidada_nao_dispara_envio(): void
    {
        $user = User::factory()->create();
        $sub = PushSubscription::query()->create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-ja-invalido',
            'p256dh' => 'chave-4',
            'auth' => 'auth-4',
            'invalidado_em' => now()->subHour(),
        ]);

        $mockWebPush = Mockery::mock(WebPush::class);
        $mockWebPush->shouldNotReceive('queueNotification');
        $mockWebPush->shouldNotReceive('flush');

        $service = new WebPushService($mockWebPush);
        $resultado = $service->enviar($sub, ['title' => 'Teste', 'body' => 'Corpo']);

        $this->assertFalse($resultado['sucesso']);
        $this->assertTrue($resultado['invalido']);
    }
}
