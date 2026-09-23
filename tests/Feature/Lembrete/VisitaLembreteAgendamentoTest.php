<?php

namespace Tests\Feature\Lembrete;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Jobs\Lembrete\Entrega\Job as EntregaJob;
use App\Models\Hospital;
use App\Models\Lembrete;
use App\Models\LembreteEntrega;
use App\Models\PushSubscription;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaParticipante;
use App\Models\VisitaRelatorio;
use App\Services\Lembrete\Processamento\Service as ProcessamentoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class VisitaLembreteAgendamentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_fluxo_criacao_edicao_e_cancelamento_de_visita(): void
    {
        $user = User::factory()->create();
        $fim1 = now()->addDays(2);

        $visita = Visita::query()->create([
            'hospital_id' => null,
            'criado_por_id' => $user->id,
            'lider_id' => $user->id,
            'tipo' => VisitaTipo::Hospital,
            'origem' => VisitaOrigem::Sistema,
            'inicio_em' => $fim1->copy()->subHours(2),
            'fim_em' => $fim1,
            'status' => VisitaStatus::Agendada,
        ]);

        app(\App\Services\Lembrete\Agendamento\Service::class)->visita($visita);

        $lembrete1 = Lembrete::query()
            ->where('atividade_tipo', 'visita')
            ->where('atividade_id', $visita->id)
            ->where('status', Lembrete::STATUS_PENDENTE)
            ->first();

        $this->assertNotNull($lembrete1);
        $this->assertEquals($fim1->format('Y-m-d H:i'), $lembrete1->programado_para->format('Y-m-d H:i'));

        // Edita fim_em
        $fim2 = now()->addDays(3);
        $visita->update(['fim_em' => $fim2]);
        app(\App\Services\Lembrete\Agendamento\Service::class)->visita($visita);

        $lembrete1->refresh();
        $this->assertSame(Lembrete::STATUS_CANCELADO, $lembrete1->status);
        $this->assertNotNull($lembrete1->cancelado_em);

        $lembrete2 = Lembrete::query()
            ->where('atividade_tipo', 'visita')
            ->where('atividade_id', $visita->id)
            ->where('status', Lembrete::STATUS_PENDENTE)
            ->first();

        $this->assertNotNull($lembrete2);
        $this->assertNotEquals($lembrete1->id, $lembrete2->id);
        $this->assertEquals($fim2->format('Y-m-d H:i'), $lembrete2->programado_para->format('Y-m-d H:i'));

        // Cancela a visita
        $visita->update(['status' => VisitaStatus::Cancelada]);
        app(\App\Services\Lembrete\Agendamento\Service::class)->visita($visita);

        $lembrete2->refresh();
        $this->assertSame(Lembrete::STATUS_CANCELADO, $lembrete2->status);
    }

    public function test_elegibilidade_de_destinatarios_de_visita(): void
    {
        Queue::fake();

        $lider = User::factory()->create();
        $partAtivo = User::factory()->create();
        $partComRelatorio = User::factory()->create();
        $partCancelado = User::factory()->create();

        // Subscriptions
        $subAtivo = PushSubscription::query()->create([
            'user_id' => $partAtivo->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/ativo',
            'p256dh' => 'chave-1',
            'auth' => 'auth-1',
        ]);

        $subComRelatorio = PushSubscription::query()->create([
            'user_id' => $partComRelatorio->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/relatorio',
            'p256dh' => 'chave-2',
            'auth' => 'auth-2',
        ]);

        $subCancelado = PushSubscription::query()->create([
            'user_id' => $partCancelado->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/cancelado',
            'p256dh' => 'chave-3',
            'auth' => 'auth-3',
        ]);

        $visita = Visita::query()->create([
            'hospital_id' => null,
            'criado_por_id' => $lider->id,
            'lider_id' => $lider->id,
            'tipo' => VisitaTipo::Hospital,
            'origem' => VisitaOrigem::Sistema,
            'inicio_em' => now()->subHours(2),
            'fim_em' => now()->subMinutes(5),
            'status' => VisitaStatus::Agendada,
        ]);

        // Participante ativo
        VisitaParticipante::query()->create([
            'visita_id' => $visita->id,
            'voluntario_id' => $partAtivo->id,
            'tipo_participacao' => TipoParticipacao::Palhaco,
            'papel_na_visita' => PapelNaVisita::Participante,
            'status_participacao' => StatusParticipacao::Confirmado,
        ]);

        // Participante que enviou relatório
        VisitaParticipante::query()->create([
            'visita_id' => $visita->id,
            'voluntario_id' => $partComRelatorio->id,
            'tipo_participacao' => TipoParticipacao::Palhaco,
            'papel_na_visita' => PapelNaVisita::Participante,
            'status_participacao' => StatusParticipacao::Confirmado,
        ]);
        VisitaRelatorio::query()->create([
            'visita_id' => $visita->id,
            'autor_id' => $partComRelatorio->id,
            'tipo_relatorio' => \App\Enums\TipoRelatorio::Palhaco,
            'resumo' => 'Relatorio enviado',
            'enviado_em' => now(),
            'fora_do_prazo' => false,
        ]);

        // Participante cancelado
        VisitaParticipante::query()->create([
            'visita_id' => $visita->id,
            'voluntario_id' => $partCancelado->id,
            'tipo_participacao' => TipoParticipacao::Palhaco,
            'papel_na_visita' => PapelNaVisita::Participante,
            'status_participacao' => StatusParticipacao::Cancelado,
        ]);

        $lembrete = Lembrete::query()->create([
            'atividade_tipo' => 'visita',
            'atividade_id' => $visita->id,
            'tipo' => Lembrete::TIPO_RELATORIO_VISITA,
            'programado_para' => $visita->fim_em,
            'status' => Lembrete::STATUS_PENDENTE,
        ]);

        $processamentoService = new ProcessamentoService();
        $processamentoService->processar($lembrete);

        // Apenas o participante ativo sem relatório deve ter entrega gerada e job despachado
        $this->assertDatabaseHas('lembrete_entregas', [
            'lembrete_id' => $lembrete->id,
            'user_id' => $partAtivo->id,
            'push_subscription_id' => $subAtivo->id,
        ]);

        $this->assertDatabaseMissing('lembrete_entregas', [
            'lembrete_id' => $lembrete->id,
            'user_id' => $partComRelatorio->id,
        ]);

        $this->assertDatabaseMissing('lembrete_entregas', [
            'lembrete_id' => $lembrete->id,
            'user_id' => $partCancelado->id,
        ]);

        Queue::assertPushed(EntregaJob::class, 1);
    }

    public function test_visita_cancelada_nao_dispara_lembretes(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $sub = PushSubscription::query()->create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/visita-canc',
            'p256dh' => 'chave-1',
            'auth' => 'auth-1',
        ]);

        $visita = Visita::query()->create([
            'hospital_id' => null,
            'criado_por_id' => $user->id,
            'tipo' => VisitaTipo::Hospital,
            'origem' => VisitaOrigem::Sistema,
            'inicio_em' => now()->subHours(2),
            'fim_em' => now()->subMinutes(5),
            'status' => VisitaStatus::Cancelada,
        ]);

        VisitaParticipante::query()->create([
            'visita_id' => $visita->id,
            'voluntario_id' => $user->id,
            'tipo_participacao' => TipoParticipacao::Palhaco,
            'papel_na_visita' => PapelNaVisita::Participante,
            'status_participacao' => StatusParticipacao::Confirmado,
        ]);

        $lembrete = Lembrete::query()->create([
            'atividade_tipo' => 'visita',
            'atividade_id' => $visita->id,
            'tipo' => Lembrete::TIPO_RELATORIO_VISITA,
            'programado_para' => $visita->fim_em,
            'status' => Lembrete::STATUS_PENDENTE,
        ]);

        $service = new ProcessamentoService();
        $service->processar($lembrete);

        $this->assertDatabaseMissing('lembrete_entregas', [
            'lembrete_id' => $lembrete->id,
        ]);

        Queue::assertNothingPushed();
    }
}
