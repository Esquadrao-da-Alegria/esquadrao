<?php

namespace Tests\Feature\Lembrete;

use App\Jobs\Lembrete\Entrega\Job as EntregaJob;
use App\Models\Evento;
use App\Models\Lembrete;
use App\Models\PushSubscription;
use App\Models\User;
use App\Services\Lembrete\Processamento\Service as ProcessamentoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EventoLembreteElegibilidadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_participante_inscrito_recebe_lembretes_de_24h_e_1h(): void
    {
        Queue::fake();

        $participante = User::factory()->create();
        $subscription = PushSubscription::query()->create([
            'user_id' => $participante->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/evento-inscrito',
            'p256dh' => 'chave-1',
            'auth' => 'auth-1',
        ]);

        $evento = Evento::create([
            'titulo' => 'Reunião de Alinhamento',
            'tipo' => 'reuniao',
            'data_inicio' => now()->addHours(24),
            'data_fim' => now()->addHours(26),
            'status' => 'agendado',
            'criado_por_id' => $participante->id,
        ]);
        $evento->participantes()->attach($participante->id, ['status' => 'inscrito', 'inscrito_em' => now()]);

        $lembrete24h = Lembrete::query()->create([
            'atividade_tipo' => 'evento',
            'atividade_id' => $evento->id,
            'tipo' => Lembrete::TIPO_EVENTO_24H,
            'programado_para' => $evento->data_inicio->copy()->subHours(24),
            'status' => Lembrete::STATUS_PENDENTE,
        ]);
        $lembrete1h = Lembrete::query()->create([
            'atividade_tipo' => 'evento',
            'atividade_id' => $evento->id,
            'tipo' => Lembrete::TIPO_EVENTO_1H,
            'programado_para' => $evento->data_inicio->copy()->subHour(),
            'status' => Lembrete::STATUS_PENDENTE,
        ]);

        $service = new ProcessamentoService();
        $service->processar($lembrete24h);
        $service->processar($lembrete1h);

        $this->assertDatabaseHas('lembrete_entregas', [
            'lembrete_id' => $lembrete24h->id,
            'user_id' => $participante->id,
            'push_subscription_id' => $subscription->id,
        ]);
        $this->assertDatabaseHas('lembrete_entregas', [
            'lembrete_id' => $lembrete1h->id,
            'user_id' => $participante->id,
            'push_subscription_id' => $subscription->id,
        ]);
        Queue::assertPushed(EntregaJob::class, 2);
    }

    public function test_evento_cancelado_nao_dispara_lembretes(): void
    {
        Queue::fake();

        $participante = User::factory()->create();
        PushSubscription::query()->create([
            'user_id' => $participante->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/evento-cancelado',
            'p256dh' => 'chave-1',
            'auth' => 'auth-1',
        ]);

        $evento = Evento::create([
            'titulo' => 'Oficina Cancelada',
            'tipo' => 'oficina',
            'data_inicio' => now()->addHour(),
            'data_fim' => now()->addHours(2),
            'status' => 'cancelado',
            'criado_por_id' => $participante->id,
        ]);
        $evento->participantes()->attach($participante->id, ['status' => 'inscrito', 'inscrito_em' => now()]);

        $lembrete = Lembrete::query()->create([
            'atividade_tipo' => 'evento',
            'atividade_id' => $evento->id,
            'tipo' => Lembrete::TIPO_EVENTO_1H,
            'programado_para' => now(),
            'status' => Lembrete::STATUS_PENDENTE,
        ]);

        (new ProcessamentoService())->processar($lembrete);

        $this->assertDatabaseMissing('lembrete_entregas', ['lembrete_id' => $lembrete->id]);
        Queue::assertNothingPushed();
    }

    public function test_inscricao_removida_nao_recebe_lembrete(): void
    {
        Queue::fake();

        $inscrito = User::factory()->create();
        $cancelado = User::factory()->create();

        PushSubscription::query()->create([
            'user_id' => $inscrito->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/evento-inscrito-2',
            'p256dh' => 'chave-1',
            'auth' => 'auth-1',
        ]);
        PushSubscription::query()->create([
            'user_id' => $cancelado->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/evento-cancelado-2',
            'p256dh' => 'chave-2',
            'auth' => 'auth-2',
        ]);

        $evento = Evento::create([
            'titulo' => 'Evento com Cancelamento de Inscrição',
            'tipo' => 'evento',
            'data_inicio' => now()->addHour(),
            'data_fim' => now()->addHours(2),
            'status' => 'agendado',
            'criado_por_id' => $inscrito->id,
        ]);
        $evento->participantes()->attach($inscrito->id, ['status' => 'inscrito', 'inscrito_em' => now()]);
        $evento->participantes()->attach($cancelado->id, ['status' => 'cancelado', 'inscrito_em' => now(), 'cancelado_em' => now()]);

        $lembrete = Lembrete::query()->create([
            'atividade_tipo' => 'evento',
            'atividade_id' => $evento->id,
            'tipo' => Lembrete::TIPO_EVENTO_1H,
            'programado_para' => now(),
            'status' => Lembrete::STATUS_PENDENTE,
        ]);

        (new ProcessamentoService())->processar($lembrete);

        $this->assertDatabaseHas('lembrete_entregas', [
            'lembrete_id' => $lembrete->id,
            'user_id' => $inscrito->id,
        ]);
        $this->assertDatabaseMissing('lembrete_entregas', [
            'lembrete_id' => $lembrete->id,
            'user_id' => $cancelado->id,
        ]);
        Queue::assertPushed(EntregaJob::class, 1);
    }

    public function test_evento_reagendado_nao_dispara_lembrete_com_horario_anterior(): void
    {
        Queue::fake();

        $participante = User::factory()->create();
        PushSubscription::query()->create([
            'user_id' => $participante->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/evento-reagendado',
            'p256dh' => 'chave-1',
            'auth' => 'auth-1',
        ]);

        $evento = Evento::create([
            'titulo' => 'Evento Reagendado',
            'tipo' => 'evento',
            'data_inicio' => now()->addDays(5),
            'data_fim' => now()->addDays(5)->addHours(2),
            'status' => 'agendado',
            'criado_por_id' => $participante->id,
        ]);
        $evento->participantes()->attach($participante->id, ['status' => 'inscrito', 'inscrito_em' => now()]);

        app(\App\Services\Lembrete\Agendamento\Service::class)->evento($evento->fresh());

        $lembreteAntigo = Lembrete::query()
            ->where('atividade_tipo', 'evento')
            ->where('atividade_id', $evento->id)
            ->where('tipo', Lembrete::TIPO_EVENTO_24H)
            ->firstOrFail();

        $evento->update(['data_inicio' => now()->addDays(10), 'data_fim' => now()->addDays(10)->addHours(2)]);
        app(\App\Services\Lembrete\Agendamento\Service::class)->evento($evento->fresh());

        $lembreteAntigo->refresh();
        $this->assertSame(Lembrete::STATUS_CANCELADO, $lembreteAntigo->status);

        (new ProcessamentoService())->processar($lembreteAntigo);

        $this->assertDatabaseMissing('lembrete_entregas', ['lembrete_id' => $lembreteAntigo->id]);
        Queue::assertNothingPushed();
    }

    public function test_processar_duas_vezes_o_mesmo_lembrete_nao_desfaz_o_processamento(): void
    {
        Queue::fake();

        $participante = User::factory()->create();
        PushSubscription::query()->create([
            'user_id' => $participante->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/evento-job-duplicado',
            'p256dh' => 'chave-1',
            'auth' => 'auth-1',
        ]);

        $evento = Evento::create([
            'titulo' => 'Evento com Job Duplicado',
            'tipo' => 'evento',
            'data_inicio' => now()->addHour(),
            'data_fim' => now()->addHours(2),
            'status' => 'agendado',
            'criado_por_id' => $participante->id,
        ]);
        $evento->participantes()->attach($participante->id, ['status' => 'inscrito', 'inscrito_em' => now()]);

        $lembrete = Lembrete::query()->create([
            'atividade_tipo' => 'evento',
            'atividade_id' => $evento->id,
            'tipo' => Lembrete::TIPO_EVENTO_1H,
            'programado_para' => now(),
            'status' => Lembrete::STATUS_PENDENTE,
        ]);

        // Simula duas execuções do ProcessLembretes para o mesmo lembrete antes
        // de a fila ser drenada (ex.: scheduler rodou de novo com o worker atrasado).
        $service = new ProcessamentoService();
        $service->processar($lembrete);
        $service->processar($lembrete->fresh());

        $lembrete->refresh();
        $this->assertSame(Lembrete::STATUS_PROCESSADO, $lembrete->status);
        $this->assertNull($lembrete->cancelado_em);
        $this->assertDatabaseCount('lembrete_entregas', 1);
        Queue::assertPushed(EntregaJob::class, 1);
    }
}
