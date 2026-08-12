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

class MeuDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrador_de_suporte_sem_voluntario_acessa_estado_vazio(): void
    {
        $usuario = User::factory()->create();
        $cargo = Cargo::query()->create(['slug' => 'administrador', 'nome' => 'Administrador']);
        $usuario->cargos()->attach($cargo);

        $this->actingAs($usuario)
            ->get(route('dashboards.meu'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Meu')
                ->where('voluntario.possui_vinculo', false)
                ->where('indicadores.visitas_validas', 0)
                ->has('historico.data', 0));
    }

    public function test_exibe_somente_as_participacoes_do_usuario_autenticado(): void
    {
        $cidade = $this->criarCidade();
        $usuario = $this->criarUsuario($cidade, ['voluntario']);
        $outro = $this->criarUsuario($cidade, ['voluntario']);
        $hospital = $this->criarHospital($cidade);
        $visitaUsuario = $this->criarVisita($hospital, $usuario, '2026-08-05 10:00:00');
        $visitaOutro = $this->criarVisita($hospital, $outro, '2026-08-06 10:00:00');
        $this->participar($visitaUsuario, $usuario);
        $this->participar($visitaOutro, $outro);
        $this->relatar($visitaUsuario, $usuario, 10);
        $this->relatar($visitaOutro, $outro, 50);

        $this->actingAs($usuario)
            ->get(route('dashboards.meu', ['periodo_tipo' => 'mes', 'ano' => 2026, 'mes' => 8]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Meu')
                ->where('indicadores.visitas_validas', 1)
                ->where('indicadores.impacto_estimado', 10)
                ->has('historico.data', 1)
                ->where('historico.data.0.id', $visitaUsuario->id));
    }

    public function test_multiplos_relatorios_nao_duplicam_visita_e_impacto_usa_media_consolidada(): void
    {
        $cidade = $this->criarCidade();
        $usuario = $this->criarUsuario($cidade, ['voluntario']);
        $outro = $this->criarUsuario($cidade, ['voluntario']);
        $visita = $this->criarVisita($this->criarHospital($cidade), $usuario, '2026-08-05 10:00:00');
        $this->participar($visita, $usuario);
        $this->relatar($visita, $usuario, 10);
        $this->relatar($visita, $usuario, 20);
        $this->relatar($visita, $outro, 30);

        $this->actingAs($usuario)
            ->get(route('dashboards.meu', ['periodo_tipo' => 'mes', 'ano' => 2026, 'mes' => 8]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('indicadores.visitas_validas', 1)
                ->where('indicadores.visitas_realizadas', 1)
                ->where('indicadores.impacto_estimado', 20));
    }

    public function test_dashboard_pessoal_aplica_relatorio_do_grupo_apenas_para_palhacos(): void
    {
        $cidade = $this->criarCidade();
        $palhacoAutor = $this->criarUsuario($cidade, ['voluntario']);
        $palhaco = $this->criarUsuario($cidade, ['voluntario']);
        $paisana = $this->criarUsuario($cidade, ['voluntario']);
        $visita = $this->criarVisita($this->criarHospital($cidade), $palhacoAutor, '2026-08-05 10:00:00');
        $this->participar($visita, $palhacoAutor, StatusParticipacao::Confirmado, TipoParticipacao::Palhaco);
        $this->participar($visita, $palhaco, StatusParticipacao::Confirmado, TipoParticipacao::Palhaco);
        $this->participar($visita, $paisana, StatusParticipacao::Confirmado, TipoParticipacao::Paisana);
        $this->relatar($visita, $palhacoAutor, 10);

        $this->actingAs($palhaco)
            ->get(route('dashboards.meu', ['periodo_tipo' => 'mes', 'ano' => 2026, 'mes' => 8]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('indicadores.visitas_validas', 1)
                ->where('historico.data.0.motivo', 'Visita contabilizada por relatório válido do grupo de palhaços'));

        $this->actingAs($paisana)
            ->get(route('dashboards.meu', ['periodo_tipo' => 'mes', 'ano' => 2026, 'mes' => 8]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('indicadores.visitas_validas', 0)
                ->where('indicadores.relatorios_pendentes', 1));
    }

    public function test_administrador_voluntario_recebe_meta_de_visitas(): void
    {
        $usuario = $this->criarUsuario($this->criarCidade(), ['voluntario', 'administrador']);

        $this->actingAs($usuario)
            ->get(route('dashboards.meu'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('voluntario.tipo_atuacao', 'visitas')
                ->where('indicadores.meta_mensal', 2));
    }

    public function test_periodo_personalizado_bloqueia_intervalo_superior_a_vinte_e_quatro_meses(): void
    {
        $usuario = $this->criarUsuario($this->criarCidade(), ['voluntario']);

        $this->actingAs($usuario)
            ->get(route('dashboards.meu', [
                'periodo_tipo' => 'personalizado',
                'data_inicio' => '2024-01-01',
                'data_fim' => '2026-02-01',
            ]))
            ->assertSessionHasErrors('data_fim');
    }

    public function test_proximas_atividades_exigem_confirmacao_ou_inscricao_ativa(): void
    {
        $cidade = $this->criarCidade();
        $usuario = $this->criarUsuario($cidade, ['voluntario']);
        $hospital = $this->criarHospital($cidade);
        $confirmada = $this->criarVisita($hospital, $usuario, now()->addDays(2)->format('Y-m-d H:i:s'), VisitaStatus::Agendada);
        $pendente = $this->criarVisita($hospital, $usuario, now()->addDays(3)->format('Y-m-d H:i:s'), VisitaStatus::Agendada);
        $this->participar($confirmada, $usuario);
        $this->participar($pendente, $usuario, StatusParticipacao::Pendente);
        $eventoAtivo = $this->criarEvento($cidade, $usuario, now()->addDays(4)->format('Y-m-d H:i:s'));
        $eventoCancelado = $this->criarEvento($cidade, $usuario, now()->addDays(5)->format('Y-m-d H:i:s'));
        $eventoAtivo->participantes()->attach($usuario->id, ['status' => 'inscrito']);
        $eventoCancelado->participantes()->attach($usuario->id, ['status' => 'cancelado']);

        $this->actingAs($usuario)
            ->get(route('dashboards.meu'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('proximas_atividades', 2)
                ->where('proximas_atividades.0.id', $confirmada->id)
                ->where('proximas_atividades.1.id', $eventoAtivo->id));
    }

    private function criarUsuario(Cidade $cidade, array $cargos): User
    {
        $voluntario = Voluntario::query()->create(['nome_completo' => uniqid('Voluntário '), 'email' => uniqid().'@teste.com', 'cidade_base_id' => $cidade->id, 'status' => 'ativo']);
        $user = User::factory()->create(['voluntario_id' => $voluntario->id]);
        foreach ($cargos as $slug) {
            $cargo = Cargo::query()->firstOrCreate(['slug' => $slug], ['nome' => $slug]);
            $user->cargos()->attach($cargo);
        }
        $user->unsetRelation('cargos');
        return $user;
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

    private function criarVisita(Hospital $hospital, User $gestor, string $inicio, VisitaStatus $status = VisitaStatus::Realizada): Visita
    {
        return Visita::query()->create(['hospital_id' => $hospital->id, 'criado_por_id' => $gestor->id, 'inicio_em' => $inicio, 'fim_em' => date('Y-m-d H:i:s', strtotime($inicio.' +2 hours')), 'tipo' => VisitaTipo::Hospital, 'status' => $status, 'origem' => VisitaOrigem::Sistema]);
    }

    private function participar(Visita $visita, User $user, StatusParticipacao $status = StatusParticipacao::Confirmado, TipoParticipacao $tipo = TipoParticipacao::Palhaco): void
    {
        VisitaParticipante::query()->create(['visita_id' => $visita->id, 'voluntario_id' => $user->id, 'tipo_participacao' => $tipo, 'papel_na_visita' => PapelNaVisita::Participante, 'status_participacao' => $status]);
    }

    private function relatar(Visita $visita, User $autor, int $impacto): void
    {
        VisitaRelatorio::query()->create(['visita_id' => $visita->id, 'autor_id' => $autor->id, 'tipo_relatorio' => TipoRelatorio::Geral, 'resumo' => 'Teste', 'pessoas_impactadas' => $impacto, 'enviado_em' => now(), 'fora_do_prazo' => false]);
    }

    private function criarEvento(Cidade $cidade, User $gestor, string $inicio): Evento
    {
        return Evento::query()->create(['titulo' => 'Reunião', 'tipo' => 'reuniao', 'cidade_id' => $cidade->id, 'data_inicio' => $inicio, 'data_fim' => date('Y-m-d H:i:s', strtotime($inicio.' +2 hours')), 'status' => 'agendado', 'criado_por_id' => $gestor->id]);
    }
}
