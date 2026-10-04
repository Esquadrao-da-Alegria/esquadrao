<?php

namespace Tests\Feature\Lembrete;

use App\Jobs\Lembrete\Entrega\Job as EntregaJob;
use App\Models\Lembrete;
use App\Models\LembreteEntrega;
use App\Models\PushSubscription;
use App\Models\User;
use App\Services\WebPush\Service as WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class LembreteEntregaJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_executa_entrega_com_sucesso(): void
    {
        $user = User::factory()->create();
        $sub = PushSubscription::query()->create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-job-1',
            'p256dh' => 'chave-1',
            'auth' => 'auth-1',
        ]);

        $lembrete = Lembrete::query()->create([
            'atividade_tipo' => 'evento',
            'atividade_id' => 1,
            'tipo' => Lembrete::TIPO_EVENTO_24H,
            'programado_para' => now()->addDay(),
            'status' => Lembrete::STATUS_PENDENTE,
        ]);

        $entrega = LembreteEntrega::query()->create([
            'lembrete_id' => $lembrete->id,
            'atividade_tipo' => 'evento',
            'atividade_id' => 1,
            'tipo' => Lembrete::TIPO_EVENTO_24H,
            'user_id' => $user->id,
            'push_subscription_id' => $sub->id,
            'status' => LembreteEntrega::STATUS_PENDENTE,
        ]);

        $mockService = Mockery::mock(WebPushService::class);
        $mockService->shouldReceive('enviar')
            ->once()
            ->andReturn(['sucesso' => true, 'invalido' => false, 'erro' => null]);

        $job = new EntregaJob($entrega->id, ['title' => 'Teste', 'body' => 'Corpo']);
        $job->handle($mockService);

        $entrega->refresh();
        $this->assertSame(LembreteEntrega::STATUS_SUCESSO, $entrega->status);
        $this->assertNotNull($entrega->enviado_em);
    }

    public function test_falha_de_uma_subscription_nao_afeta_outras(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $sub1 = PushSubscription::query()->create([
            'user_id' => $user1->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-job-fail',
            'p256dh' => 'chave-1',
            'auth' => 'auth-1',
        ]);

        $sub2 = PushSubscription::query()->create([
            'user_id' => $user2->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-job-pass',
            'p256dh' => 'chave-2',
            'auth' => 'auth-2',
        ]);

        $lembrete = Lembrete::query()->create([
            'atividade_tipo' => 'evento',
            'atividade_id' => 2,
            'tipo' => Lembrete::TIPO_EVENTO_24H,
            'programado_para' => now()->addDay(),
            'status' => Lembrete::STATUS_PENDENTE,
        ]);

        $entrega1 = LembreteEntrega::query()->create([
            'lembrete_id' => $lembrete->id,
            'atividade_tipo' => 'evento',
            'atividade_id' => 2,
            'tipo' => Lembrete::TIPO_EVENTO_24H,
            'user_id' => $user1->id,
            'push_subscription_id' => $sub1->id,
            'status' => LembreteEntrega::STATUS_PENDENTE,
        ]);

        $entrega2 = LembreteEntrega::query()->create([
            'lembrete_id' => $lembrete->id,
            'atividade_tipo' => 'evento',
            'atividade_id' => 2,
            'tipo' => Lembrete::TIPO_EVENTO_24H,
            'user_id' => $user2->id,
            'push_subscription_id' => $sub2->id,
            'status' => LembreteEntrega::STATUS_PENDENTE,
        ]);

        $mockService = Mockery::mock(WebPushService::class);
        $mockService->shouldReceive('enviar')
            ->with(Mockery::on(fn ($s) => $s->id === $sub1->id), Mockery::any())
            ->once()
            ->andReturn(['sucesso' => false, 'invalido' => true, 'erro' => 'Expired']);

        $mockService->shouldReceive('enviar')
            ->with(Mockery::on(fn ($s) => $s->id === $sub2->id), Mockery::any())
            ->once()
            ->andReturn(['sucesso' => true, 'invalido' => false, 'erro' => null]);

        $job1 = new EntregaJob($entrega1->id, ['title' => 'Teste']);
        $job1->handle($mockService);

        $job2 = new EntregaJob($entrega2->id, ['title' => 'Teste']);
        $job2->handle($mockService);

        $entrega1->refresh();
        $entrega2->refresh();

        $this->assertSame(LembreteEntrega::STATUS_FALHA, $entrega1->status);
        $this->assertSame(LembreteEntrega::STATUS_SUCESSO, $entrega2->status);
    }
}
