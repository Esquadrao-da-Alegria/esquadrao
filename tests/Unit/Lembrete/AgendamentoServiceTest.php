<?php

namespace Tests\Unit\Lembrete;

use App\Enums\VisitaStatus;
use App\Models\Evento;
use App\Models\Lembrete;
use App\Models\User;
use App\Models\Visita;
use App\Services\Lembrete\Agendamento\Service as AgendamentoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendamentoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_agenda_lembrete_de_visita_para_fim_em(): void
    {
        $user = User::factory()->create();
        $fimEm = Carbon::parse('2026-10-01 16:30:00');
        $visita = Visita::query()->create([
            'hospital_id' => null,
            'criado_por_id' => $user->id,
            'tipo' => \App\Enums\VisitaTipo::Hospital,
            'origem' => \App\Enums\VisitaOrigem::Sistema,
            'inicio_em' => $fimEm->copy()->subHours(2),
            'fim_em' => $fimEm,
            'status' => VisitaStatus::Agendada,
        ]);

        $service = new AgendamentoService();
        $service->visita($visita);

        $this->assertDatabaseHas('lembretes', [
            'atividade_tipo' => 'visita',
            'atividade_id' => $visita->id,
            'tipo' => Lembrete::TIPO_RELATORIO_VISITA,
            'programado_para' => '2026-10-01 16:30:00',
            'status' => Lembrete::STATUS_PENDENTE,
        ]);
    }

    public function test_agenda_lembretes_de_evento_para_24h_e_1h(): void
    {
        $user = User::factory()->create();
        $inicio = Carbon::parse('2026-10-05 14:00:00');
        $evento = Evento::query()->create([
            'criado_por_id' => $user->id,
            'titulo' => 'Reunião de Alinhamento',
            'tipo' => 'reuniao',
            'data_inicio' => $inicio,
            'data_fim' => $inicio->copy()->addHours(2),
            'status' => 'agendado',
        ]);

        $service = new AgendamentoService();
        $service->evento($evento);

        $this->assertDatabaseHas('lembretes', [
            'atividade_tipo' => 'evento',
            'atividade_id' => $evento->id,
            'tipo' => Lembrete::TIPO_EVENTO_24H,
            'programado_para' => '2026-10-04 14:00:00',
            'status' => Lembrete::STATUS_PENDENTE,
        ]);

        $this->assertDatabaseHas('lembretes', [
            'atividade_tipo' => 'evento',
            'atividade_id' => $evento->id,
            'tipo' => Lembrete::TIPO_EVENTO_1H,
            'programado_para' => '2026-10-05 13:00:00',
            'status' => Lembrete::STATUS_PENDENTE,
        ]);
    }

    public function test_nao_agenda_evento_com_tipo_nao_permitido(): void
    {
        $user = User::factory()->create();
        $inicio = Carbon::parse('2026-10-05 14:00:00');
        $evento = Evento::query()->create([
            'criado_por_id' => $user->id,
            'titulo' => 'Tipo Estranho',
            'tipo' => 'outro_tipo',
            'data_inicio' => $inicio,
            'data_fim' => $inicio->copy()->addHours(2),
            'status' => 'agendado',
        ]);

        $service = new AgendamentoService();
        $service->evento($evento);

        $this->assertDatabaseMissing('lembretes', [
            'atividade_tipo' => 'evento',
            'atividade_id' => $evento->id,
        ]);
    }
}
