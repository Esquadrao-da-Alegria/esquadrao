<?php

namespace Tests\Feature\Lembrete;

use App\Models\Lembrete;
use App\Models\LembreteEntrega;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LembreteEntregaUnicidadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_lembrete_e_entrega_com_sucesso(): void
    {
        $user = User::factory()->create();

        $sub = PushSubscription::query()->create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-1',
            'p256dh' => 'chave-1',
            'auth' => 'auth-1',
        ]);

        $lembrete = Lembrete::query()->create([
            'atividade_tipo' => 'evento',
            'atividade_id' => 10,
            'tipo' => Lembrete::TIPO_EVENTO_24H,
            'programado_para' => now()->addDay(),
            'status' => Lembrete::STATUS_PENDENTE,
        ]);

        $entrega = LembreteEntrega::query()->create([
            'lembrete_id' => $lembrete->id,
            'atividade_tipo' => 'evento',
            'atividade_id' => 10,
            'tipo' => Lembrete::TIPO_EVENTO_24H,
            'user_id' => $user->id,
            'push_subscription_id' => $sub->id,
            'status' => LembreteEntrega::STATUS_SUCESSO,
            'enviado_em' => now(),
        ]);

        $this->assertTrue($entrega->lembrete->is($lembrete));
        $this->assertTrue($entrega->user->is($user));
        $this->assertTrue($entrega->subscription->is($sub));
    }

    public function test_impede_entrega_duplicada_por_atividade_tipo_e_subscription(): void
    {
        $user = User::factory()->create();

        $sub = PushSubscription::query()->create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-2',
            'p256dh' => 'chave-2',
            'auth' => 'auth-2',
        ]);

        $lembrete = Lembrete::query()->create([
            'atividade_tipo' => 'visita',
            'atividade_id' => 55,
            'tipo' => Lembrete::TIPO_RELATORIO_VISITA,
            'programado_para' => now()->addHours(2),
            'status' => Lembrete::STATUS_PENDENTE,
        ]);

        LembreteEntrega::query()->create([
            'lembrete_id' => $lembrete->id,
            'atividade_tipo' => 'visita',
            'atividade_id' => 55,
            'tipo' => Lembrete::TIPO_RELATORIO_VISITA,
            'user_id' => $user->id,
            'push_subscription_id' => $sub->id,
            'status' => LembreteEntrega::STATUS_SUCESSO,
            'enviado_em' => now(),
        ]);

        $this->expectException(QueryException::class);

        // Tentativa concorrente / reexecução para a mesma atividade, tipo e subscription
        LembreteEntrega::query()->create([
            'lembrete_id' => $lembrete->id,
            'atividade_tipo' => 'visita',
            'atividade_id' => 55,
            'tipo' => Lembrete::TIPO_RELATORIO_VISITA,
            'user_id' => $user->id,
            'push_subscription_id' => $sub->id,
            'status' => LembreteEntrega::STATUS_PENDENTE,
        ]);
    }

    public function test_permite_mesma_subscription_em_atividades_diferentes_ou_tipos_diferentes(): void
    {
        $user = User::factory()->create();

        $sub = PushSubscription::query()->create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/device-3',
            'p256dh' => 'chave-3',
            'auth' => 'auth-3',
        ]);

        $lembrete24h = Lembrete::query()->create([
            'atividade_tipo' => 'evento',
            'atividade_id' => 10,
            'tipo' => Lembrete::TIPO_EVENTO_24H,
            'programado_para' => now()->addDay(),
            'status' => Lembrete::STATUS_PENDENTE,
        ]);

        $lembrete1h = Lembrete::query()->create([
            'atividade_tipo' => 'evento',
            'atividade_id' => 10,
            'tipo' => Lembrete::TIPO_EVENTO_1H,
            'programado_para' => now()->addHour(),
            'status' => Lembrete::STATUS_PENDENTE,
        ]);

        $entrega24h = LembreteEntrega::query()->create([
            'lembrete_id' => $lembrete24h->id,
            'atividade_tipo' => 'evento',
            'atividade_id' => 10,
            'tipo' => Lembrete::TIPO_EVENTO_24H,
            'user_id' => $user->id,
            'push_subscription_id' => $sub->id,
            'status' => LembreteEntrega::STATUS_SUCESSO,
        ]);

        $entrega1h = LembreteEntrega::query()->create([
            'lembrete_id' => $lembrete1h->id,
            'atividade_tipo' => 'evento',
            'atividade_id' => 10,
            'tipo' => Lembrete::TIPO_EVENTO_1H,
            'user_id' => $user->id,
            'push_subscription_id' => $sub->id,
            'status' => LembreteEntrega::STATUS_SUCESSO,
        ]);

        $this->assertNotSame($entrega24h->id, $entrega1h->id);
    }
}
