<?php

namespace Tests\Feature\Dashboard;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
use App\Enums\TipoRelatorio;
use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Evento;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaParticipante;
use App\Models\VisitaRelatorio;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_visao_geral_exibe_somente_a_agenda_e_as_pendencias_do_usuario_autenticado(): void
    {
        $this->travelTo(now()->setDate(2026, 8, 11)->setTime(10, 0));
        $cidade = $this->criarCidade();
        $usuario = $this->criarUsuario($cidade);
        $outro = $this->criarUsuario($cidade);
        $hospital = $this->criarHospital($cidade);

        $proximaVisita = $this->criarVisita($hospital, $usuario, now()->addDay(), VisitaStatus::Agendada);
        $this->participar($proximaVisita, $usuario);
        $this->participar($this->criarVisita($hospital, $outro, now()->addDays(2), VisitaStatus::Agendada), $outro);

        $evento = $this->criarEvento($cidade, $usuario, now()->addHours(5), 'oficina', 'agendado');
        $evento->participantes()->attach($usuario->id, ['status' => 'inscrito']);

        $pendente = $this->criarVisita($hospital, $usuario, now()->subHours(13), VisitaStatus::Realizada);
        $this->participar($pendente, $usuario, TipoParticipacao::Paisana);
        $pendenteOutro = $this->criarVisita($hospital, $outro, now()->subHours(13), VisitaStatus::Realizada);
        $this->participar($pendenteOutro, $outro, TipoParticipacao::Paisana);

        $this->actingAs($usuario)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('contexto.nome', $usuario->voluntario->nome_completo)
                ->where('contexto.cidade', $cidade->nome)
                ->has('proximas_atividades', 2)
                ->where('proximas_atividades.0.id', $evento->id)
                ->where('proximas_atividades.0.categoria', 'oficina')
                ->where('proximas_atividades.1.id', $proximaVisita->id)
                ->has('pendencias', 1)
                ->where('pendencias.0.id', 'relatorio-'.$pendente->id)
                ->where('pendencias.0.estado_prazo', 'prazo_proximo'));
    }

    public function test_relatorio_do_grupo_remove_pendencia_de_palhaco_mas_nao_de_paisana(): void
    {
        $cidade = $this->criarCidade();
        $autor = $this->criarUsuario($cidade);
        $palhaco = $this->criarUsuario($cidade);
        $paisana = $this->criarUsuario($cidade);
        $visita = $this->criarVisita($this->criarHospital($cidade), $autor, now()->subDay(), VisitaStatus::Realizada);
        $this->participar($visita, $autor);
        $this->participar($visita, $palhaco);
        $this->participar($visita, $paisana, TipoParticipacao::Paisana);
        VisitaRelatorio::query()->create([
            'visita_id' => $visita->id,
            'autor_id' => $autor->id,
            'tipo_relatorio' => TipoRelatorio::Geral,
            'resumo' => 'Relatório do grupo',
            'enviado_em' => now(),
            'fora_do_prazo' => false,
        ]);

        $this->actingAs($palhaco)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page->has('pendencias', 0));

        $this->actingAs($paisana)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page->has('pendencias', 1));
    }

    public function test_resumo_usa_visitas_validas_do_mes_e_presencas_do_semestre(): void
    {
        $this->travelTo(now()->setDate(2026, 8, 11));
        $cidade = $this->criarCidade();
        $usuario = $this->criarUsuario($cidade);
        $visita = $this->criarVisita($this->criarHospital($cidade), $usuario, now()->subDays(2), VisitaStatus::Realizada);
        $this->participar($visita, $usuario, TipoParticipacao::Paisana);
        VisitaRelatorio::query()->create([
            'visita_id' => $visita->id,
            'autor_id' => $usuario->id,
            'tipo_relatorio' => TipoRelatorio::Geral,
            'resumo' => 'Relatório válido',
            'enviado_em' => now()->subDay(),
            'fora_do_prazo' => false,
        ]);
        foreach (['oficina', 'reuniao'] as $tipo) {
            $evento = $this->criarEvento($cidade, $usuario, now()->subDay(), $tipo, 'finalizado');
            $evento->participantes()->attach($usuario->id, ['status' => 'inscrito', 'presenca' => 'presente']);
        }

        $this->actingAs($usuario)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('resumo.visitas_validas_mes', 1)
                ->where('resumo.oficinas_semestre', 1)
                ->where('resumo.reunioes_semestre', 1)
                ->where('resumo.meta.situacao', 'aplicavel'));
    }

    public function test_visitas_contabilizadas_sao_validas_e_refletem_na_meta_da_visao_geral(): void
    {
        $this->travelTo(now()->setDate(2026, 8, 18));
        $cidade = $this->criarCidade();
        $usuario = $this->criarUsuario($cidade);
        $hospital = $this->criarHospital($cidade);

        $visita1 = $this->criarVisita($hospital, $usuario, now()->subDays(5), VisitaStatus::Contabilizada);
        $visita2 = $this->criarVisita($hospital, $usuario, now()->subDays(3), VisitaStatus::Contabilizada);
        $this->participar($visita1, $usuario);
        $this->participar($visita2, $usuario);

        $this->actingAs($usuario)->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('resumo.visitas_validas_mes', 2)
                ->where('resumo.meta.situacao', 'aplicavel')
                ->where('resumo.meta.atual', 2)
                ->where('resumo.meta.objetivo', 2)
                ->has('pendencias', 0));
    }

    public function test_visitas_nao_contabilizadas_e_canceladas_nao_entram_nas_visitas_validas_da_visao_geral(): void
    {
        $this->travelTo(now()->setDate(2026, 8, 18));
        $cidade = $this->criarCidade();
        $usuario = $this->criarUsuario($cidade);
        $hospital = $this->criarHospital($cidade);

        $naoContabilizada = $this->criarVisita($hospital, $usuario, now()->subDays(4), VisitaStatus::NaoContabilizada);
        $cancelada = $this->criarVisita($hospital, $usuario, now()->subDays(2), VisitaStatus::Cancelada);
        $this->participar($naoContabilizada, $usuario);
        $this->participar($cancelada, $usuario);

        $this->actingAs($usuario)->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('resumo.visitas_validas_mes', 0)
                ->where('resumo.meta.atual', 0)
                ->has('pendencias', 0));
    }

    public function test_conta_sem_vinculo_recebe_estado_seguro_sem_metricas_pessoais(): void
    {
        $usuario = User::factory()->create();
        $cargo = Cargo::query()->create(['slug' => 'administrador', 'nome' => 'Administrador']);
        $usuario->cargos()->attach($cargo);

        $this->actingAs($usuario)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('contexto.possui_vinculo', false)
                ->where('resumo.visitas_validas_mes', null)
                ->has('proximas_atividades', 0)
                ->has('pendencias', 0)
                ->missing('acoes_rapidas'));
    }

    private function criarUsuario(Cidade $cidade): User
    {
        $voluntario = Voluntario::query()->create([
            'nome_completo' => uniqid('Voluntário '),
            'email' => uniqid().'@teste.com',
            'cidade_base_id' => $cidade->id,
            'status' => 'ativo',
        ]);
        $usuario = User::factory()->create(['voluntario_id' => $voluntario->id]);
        $cargo = Cargo::query()->firstOrCreate(['slug' => 'voluntario'], ['nome' => 'Voluntário']);
        $usuario->cargos()->attach($cargo);
        return $usuario;
    }

    private function criarCidade(): Cidade
    {
        $estado = Estado::query()->firstOrCreate(['sigla' => 'RS'], ['nome' => 'Rio Grande do Sul']);
        return Cidade::query()->forceCreate(['nome' => uniqid('Cidade '), 'estado_id' => $estado->id]);
    }

    private function criarHospital(Cidade $cidade): Hospital
    {
        return Hospital::query()->create(['cidade_id' => $cidade->id, 'nome' => 'Hospital', 'cnpj' => uniqid(), 'endereco' => 'Rua 1', 'telefone' => '51999999999', 'email' => uniqid().'@hospital.com', 'ativo' => true]);
    }

    private function criarVisita(Hospital $hospital, User $gestor, mixed $inicio, VisitaStatus $status): Visita
    {
        return Visita::query()->create([
            'hospital_id' => $hospital->id,
            'criado_por_id' => $gestor->id,
            'inicio_em' => $inicio,
            'fim_em' => $inicio->copy()->addHours(2),
            'tipo' => VisitaTipo::Hospital,
            'status' => $status,
            'origem' => VisitaOrigem::Sistema,
        ]);
    }

    private function participar(Visita $visita, User $usuario, TipoParticipacao $tipo = TipoParticipacao::Palhaco): void
    {
        VisitaParticipante::query()->create([
            'visita_id' => $visita->id,
            'voluntario_id' => $usuario->id,
            'tipo_participacao' => $tipo,
            'papel_na_visita' => PapelNaVisita::Participante,
            'status_participacao' => StatusParticipacao::Confirmado,
        ]);
    }

    private function criarEvento(Cidade $cidade, User $gestor, mixed $inicio, string $tipo, string $status): Evento
    {
        return Evento::query()->create([
            'titulo' => ucfirst($tipo),
            'tipo' => $tipo,
            'local' => 'Sede',
            'cidade_id' => $cidade->id,
            'data_inicio' => $inicio,
            'data_fim' => $inicio->copy()->addHours(2),
            'status' => $status,
            'criado_por_id' => $gestor->id,
        ]);
    }
}
