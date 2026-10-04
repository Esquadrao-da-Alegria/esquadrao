<?php

namespace Tests\Feature\Lembrete;

use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\AgendaLiberacaoCidade;
use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Evento;
use App\Models\Hospital;
use App\Models\Lembrete;
use App\Models\User;
use App\Models\Visita;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitaEventoLembreteIntegracaoTest extends TestCase
{
    use RefreshDatabase;

    // ─── Visita: criação, edição e cancelamento pelas rotas reais ────────────

    public function test_criar_visita_pela_rota_agenda_lembrete_de_relatorio(): void
    {
        $lider    = $this->criarVoluntario();
        $hospital = $this->criarHospital();

        $payload = [
            'hospital_id' => $hospital->id,
            'data'        => '2026-06-20',
            'hora_inicio' => '10:00',
            'hora_fim'    => '12:00',
            'tipo'        => VisitaTipo::Hospital->value,
            'lider_id'    => $lider->id,
        ];

        $this->actingAs($lider)
            ->post(route('visitas.store'), $payload)
            ->assertRedirect(route('visitas.index'));

        $visita = Visita::query()->where('hospital_id', $hospital->id)->firstOrFail();

        $this->assertDatabaseHas('lembretes', [
            'atividade_tipo'  => 'visita',
            'atividade_id'    => $visita->id,
            'tipo'            => Lembrete::TIPO_RELATORIO_VISITA,
            'programado_para' => '2026-06-20 12:00:00',
            'status'          => Lembrete::STATUS_PENDENTE,
        ]);
    }

    public function test_editar_horario_de_visita_pela_rota_invalida_lembrete_anterior(): void
    {
        $lider  = $this->criarVoluntario();
        $visita = $this->criarVisita($lider);
        app(\App\Services\Lembrete\Agendamento\Service::class)->visita($visita);

        $this->assertDatabaseHas('lembretes', [
            'atividade_tipo'  => 'visita',
            'atividade_id'    => $visita->id,
            'programado_para' => '2026-06-15 12:00:00',
        ]);

        $this->actingAs($lider)
            ->put(route('visitas.update', $visita), [
                'data'        => '2026-06-15',
                'hora_inicio' => '10:00',
                'hora_fim'    => '14:00',
                'tipo'        => VisitaTipo::Hospital->value,
                'hospital_id' => $visita->hospital_id,
                'lider_id'    => $lider->id,
                'status'      => VisitaStatus::Agendada->value,
            ])
            ->assertRedirect(route('visitas.index'));

        $this->assertDatabaseHas('lembretes', [
            'atividade_tipo'  => 'visita',
            'atividade_id'    => $visita->id,
            'programado_para' => '2026-06-15 12:00:00',
            'status'          => Lembrete::STATUS_CANCELADO,
        ]);

        $this->assertDatabaseHas('lembretes', [
            'atividade_tipo'  => 'visita',
            'atividade_id'    => $visita->id,
            'programado_para' => '2026-06-15 14:00:00',
            'status'          => Lembrete::STATUS_PENDENTE,
        ]);
    }

    public function test_cancelar_visita_pela_rota_invalida_lembrete_pendente(): void
    {
        $lider  = $this->criarVoluntario();
        $visita = $this->criarVisita($lider);
        app(\App\Services\Lembrete\Agendamento\Service::class)->visita($visita);

        $this->actingAs($lider)
            ->post(route('visitas.cancelar', $visita))
            ->assertRedirect(route('visitas.index'));

        $this->assertDatabaseHas('lembretes', [
            'atividade_tipo' => 'visita',
            'atividade_id'   => $visita->id,
            'status'         => Lembrete::STATUS_CANCELADO,
        ]);

        $this->assertDatabaseMissing('lembretes', [
            'atividade_tipo' => 'visita',
            'atividade_id'   => $visita->id,
            'status'         => Lembrete::STATUS_PENDENTE,
        ]);
    }

    // ─── Evento: criação, edição, cancelamento e exclusão pelas rotas reais ──

    public function test_criar_evento_pela_rota_agenda_lembretes_de_24h_e_1h(): void
    {
        $admin = $this->usuarioAdmin();

        $this->actingAs($admin)
            ->post(route('eventos.store'), $this->dadosEvento())
            ->assertRedirect(route('eventos.index'));

        $evento = Evento::query()->where('titulo', 'Oficina de alegria')->firstOrFail();

        $this->assertDatabaseHas('lembretes', [
            'atividade_tipo' => 'evento',
            'atividade_id'   => $evento->id,
            'tipo'           => Lembrete::TIPO_EVENTO_24H,
            'status'         => Lembrete::STATUS_PENDENTE,
        ]);

        $this->assertDatabaseHas('lembretes', [
            'atividade_tipo' => 'evento',
            'atividade_id'   => $evento->id,
            'tipo'           => Lembrete::TIPO_EVENTO_1H,
            'status'         => Lembrete::STATUS_PENDENTE,
        ]);
    }

    public function test_editar_data_de_evento_pela_rota_invalida_lembretes_anteriores(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $admin->id]);
        app(\App\Services\Lembrete\Agendamento\Service::class)->evento($evento->fresh());

        $novaData = now()->addDays(5)->format('Y-m-d H:i:s');

        $this->actingAs($admin)
            ->put(route('eventos.update', $evento), $this->dadosEvento([
                'data_inicio' => $novaData,
                'data_fim'    => now()->addDays(6)->format('Y-m-d H:i:s'),
            ]))
            ->assertRedirect(route('eventos.show', $evento));

        $this->assertDatabaseHas('lembretes', [
            'atividade_tipo' => 'evento',
            'atividade_id'   => $evento->id,
            'tipo'           => Lembrete::TIPO_EVENTO_24H,
            'status'         => Lembrete::STATUS_CANCELADO,
        ]);

        $novoInicio = \Carbon\Carbon::parse($novaData);
        $this->assertDatabaseHas('lembretes', [
            'atividade_tipo'  => 'evento',
            'atividade_id'    => $evento->id,
            'tipo'            => Lembrete::TIPO_EVENTO_24H,
            'programado_para' => $novoInicio->copy()->subHours(24)->format('Y-m-d H:i:s'),
            'status'          => Lembrete::STATUS_PENDENTE,
        ]);
    }

    public function test_cancelar_evento_pela_rota_invalida_lembretes_pendentes(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $admin->id]);
        app(\App\Services\Lembrete\Agendamento\Service::class)->evento($evento->fresh());

        $this->actingAs($admin)
            ->post(route('eventos.cancelar', $evento), ['motivo_cancelamento' => 'Sem quórum'])
            ->assertRedirect(route('eventos.show', $evento));

        $this->assertDatabaseMissing('lembretes', [
            'atividade_tipo' => 'evento',
            'atividade_id'   => $evento->id,
            'status'         => Lembrete::STATUS_PENDENTE,
        ]);
    }

    public function test_excluir_evento_sem_participantes_pela_rota_invalida_lembretes_pendentes(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $admin->id]);
        app(\App\Services\Lembrete\Agendamento\Service::class)->evento($evento->fresh());
        $eventoId = $evento->id;

        $this->actingAs($admin)
            ->delete(route('eventos.destroy', $evento))
            ->assertRedirect(route('eventos.index'));

        $this->assertDatabaseMissing('lembretes', [
            'atividade_tipo' => 'evento',
            'atividade_id'   => $eventoId,
            'status'         => Lembrete::STATUS_PENDENTE,
        ]);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    private ?int $cidadeId = null;

    private function dadosEvento(array $overrides = []): array
    {
        if ($this->cidadeId === null) {
            $this->cidadeId = $this->criarCidade('Campinas')->id;
        }

        return array_merge([
            'titulo'               => 'Oficina de alegria',
            'tipo'                 => 'oficina',
            'descricao'            => 'Descrição',
            'local'                => 'Sede',
            'cidade_id'            => $this->cidadeId,
            'data_inicio'          => now()->addDay()->format('Y-m-d H:i:s'),
            'data_fim'             => now()->addDays(2)->format('Y-m-d H:i:s'),
            'limite_participantes' => 10,
        ], $overrides);
    }

    private function usuarioAdmin(): User
    {
        $user  = User::factory()->createOne();
        $cargo = Cargo::query()->firstOrCreate(['slug' => 'administrador'], ['nome' => 'Administrador']);
        $user->cargos()->attach($cargo);

        return $user;
    }

    private function criarCidade(string $nome): Cidade
    {
        $estado = Estado::query()->firstOrCreate(['sigla' => 'SP'], ['nome' => 'São Paulo']);

        return Cidade::query()->where('nome', $nome)->where('estado_id', $estado->id)->first()
            ?? Cidade::query()->forceCreate(['nome' => $nome, 'estado_id' => $estado->id]);
    }

    private function criarVoluntario(): User
    {
        $cargo = Cargo::query()->firstOrCreate(['slug' => 'voluntario'], ['nome' => 'Voluntário']);

        $voluntario = Voluntario::query()->create([
            'nome_completo' => 'Voluntário ' . uniqid(),
            'email'         => uniqid('vol_') . '@test.com',
            'status'        => User::STATUS_ATIVO,
        ]);

        $user = User::factory()->create([
            'voluntario_id' => $voluntario->id,
            'status'        => User::STATUS_ATIVO,
        ]);
        $user->cargos()->syncWithoutDetaching([$cargo->id]);

        return $user->fresh('cargos');
    }

    private function criarHospital(): Hospital
    {
        $estado = Estado::query()->firstOrCreate(['sigla' => 'RS'], ['nome' => 'RS']);
        $cidade = Cidade::query()->where('nome', 'POA')->where('estado_id', $estado->id)->first()
            ?? Cidade::query()->forceCreate(['nome' => 'POA', 'estado_id' => $estado->id]);

        $hospital = Hospital::query()->create([
            'cidade_id' => $cidade->id,
            'nome'      => 'Hospital Teste ' . uniqid(),
            'cnpj'      => (string) random_int(10000000000000, 99999999999999),
            'endereco'  => 'Rua 1',
            'telefone'  => '51999999999',
            'email'     => 'a@b.com',
            'ativo'     => true,
        ]);

        AgendaLiberacaoCidade::query()->updateOrCreate(
            ['cidade_id' => $cidade->id, 'ano' => 2026, 'mes' => 6],
            ['liberado' => true, 'liberado_por_id' => null],
        );

        return $hospital;
    }

    private function criarVisita(User $lider): Visita
    {
        $hospital = $this->criarHospital();

        return Visita::query()->create([
            'hospital_id'   => $hospital->id,
            'criado_por_id' => $lider->id,
            'lider_id'      => $lider->id,
            'inicio_em'     => '2026-06-15 10:00:00',
            'fim_em'        => '2026-06-15 12:00:00',
            'tipo'          => VisitaTipo::Hospital,
            'status'        => VisitaStatus::Agendada,
            'origem'        => VisitaOrigem::Sistema,
        ]);
    }
}
