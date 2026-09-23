<?php

namespace Tests\Feature\Dashboard\Evento;

use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Evento;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ParticipacaoSemestralTest extends TestCase
{
    use RefreshDatabase;

    public function test_abre_no_semestre_atual(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 15)->setTime(10, 0));
        $cidade = $this->criarCidade('Porto Alegre');
        $gestor = $this->criarUsuario('administrador', $cidade, 'Gestora');

        $this->actingAs($gestor)
            ->get(route('dashboards.participacao-semestral'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('filtros.ano', 2026)
                ->where('filtros.semestre', 2));
    }

    public function test_exibe_totais_de_presencas_confirmadas_por_tipo_no_semestre(): void
    {
        $cidade = $this->criarCidade('Porto Alegre');
        $gestor = $this->criarUsuario('administrador', $cidade, 'Gestora');
        $voluntario = $this->criarUsuario('voluntario', $cidade, 'Ana');
        $this->criarEvento($cidade, $gestor, 'reuniao', '2026-02-10 19:00:00')
            ->participantes()->attach($voluntario->id, ['status' => 'inscrito', 'presenca' => 'presente']);
        $this->criarEvento($cidade, $gestor, 'oficina', '2026-03-10 19:00:00')
            ->participantes()->attach($voluntario->id, ['status' => 'inscrito', 'presenca' => 'presente']);
        $this->criarEvento($cidade, $gestor, 'reuniao', '2026-04-10 19:00:00')
            ->participantes()->attach($voluntario->id, ['status' => 'inscrito', 'presenca' => 'ausente']);
        $this->criarEvento($cidade, $gestor, 'oficina', '2026-05-10 19:00:00', 'cancelado')
            ->participantes()->attach($voluntario->id, ['status' => 'inscrito', 'presenca' => 'presente']);
        $this->criarEvento($cidade, $gestor, 'evento', '2026-06-10 19:00:00')
            ->participantes()->attach($voluntario->id, ['status' => 'inscrito', 'presenca' => 'presente']);

        $this->actingAs($gestor)
            ->get(route('dashboards.participacao-semestral', ['ano' => 2026, 'semestre' => 1]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Evento/ParticipacaoSemestral/Index')
                ->has('integrantes.data', 2)
                ->where('integrantes.data.0.nome', 'Ana')
                ->where('integrantes.data.0.reunioes', 1)
                ->where('integrantes.data.0.oficinas', 1)
                ->has('integrantes.data.0.eventos', 2));
    }

    public function test_alterna_semestre_e_mantem_integrantes_sem_presenca_com_zero(): void
    {
        $cidade = $this->criarCidade('Porto Alegre');
        $gestor = $this->criarUsuario('administrador', $cidade, 'Gestora');
        $voluntario = $this->criarUsuario('voluntario', $cidade, 'Bruno');
        $this->criarEvento($cidade, $gestor, 'reuniao', '2026-07-10 19:00:00')
            ->participantes()->attach($voluntario->id, ['status' => 'inscrito', 'presenca' => 'presente']);

        $this->actingAs($gestor)
            ->get(route('dashboards.participacao-semestral', ['ano' => 2026, 'semestre' => 1, 'busca' => 'Bruno']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('integrantes.data', 1)
                ->where('integrantes.data.0.reunioes', 0)
                ->where('integrantes.data.0.oficinas', 0));

        $this->actingAs($gestor)
            ->get(route('dashboards.participacao-semestral', ['ano' => 2026, 'semestre' => 2, 'busca' => 'Bruno']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('integrantes.data.0.reunioes', 1)
                ->where('integrantes.data.0.oficinas', 0));
    }

    public function test_respeita_cidade_base_e_autorizacao_de_gestores(): void
    {
        $cidadeA = $this->criarCidade('Porto Alegre');
        $cidadeB = $this->criarCidade('Canoas');
        $coordenador = $this->criarUsuario('coordenador_local', $cidadeA, 'Coordenadora local');
        $administrador = $this->criarUsuario('administrador', $cidadeA, 'Administradora');
        $local = $this->criarUsuario('voluntario', $cidadeA, 'Pessoa local');
        $remoto = $this->criarUsuario('voluntario', $cidadeB, 'Pessoa remota');
        $eventoLocal = $this->criarEvento($cidadeA, $administrador, 'reuniao', '2026-03-10 19:00:00');
        $eventoRemoto = $this->criarEvento($cidadeB, $administrador, 'oficina', '2026-03-10 19:00:00');
        $eventoLocal->participantes()->attach($local->id, ['status' => 'inscrito', 'presenca' => 'presente']);
        $eventoRemoto->participantes()->attach($remoto->id, ['status' => 'inscrito', 'presenca' => 'presente']);
        $eventoRemoto->participantes()->attach($local->id, ['status' => 'inscrito', 'presenca' => 'presente']);

        $this->actingAs($coordenador)
            ->get(route('dashboards.participacao-semestral', ['ano' => 2026, 'semestre' => 1, 'cidade_id' => $cidadeB->id]))
            ->assertForbidden();

        $this->actingAs($coordenador)
            ->get(route('dashboards.participacao-semestral', ['ano' => 2026, 'semestre' => 1, 'busca' => 'Pessoa local']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('integrantes.data', 1)
                ->where('integrantes.data.0.nome', 'Pessoa local')
                ->where('integrantes.data.0.reunioes', 1)
                ->where('integrantes.data.0.oficinas', 0));

        $this->actingAs($administrador)
            ->get(route('dashboards.participacao-semestral', ['ano' => 2026, 'semestre' => 1, 'cidade_id' => $cidadeB->id]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('integrantes.data', 1)
                ->where('integrantes.data.0.nome', 'Pessoa remota')
                ->where('integrantes.data.0.oficinas', 1));
    }

    public function test_coordenador_geral_tem_visao_global_e_voluntario_nao_acessa(): void
    {
        $cidadeA = $this->criarCidade('Porto Alegre');
        $cidadeB = $this->criarCidade('Canoas');
        $coordenador = $this->criarUsuario('coordenador_geral', $cidadeA, 'Coordenador geral');
        $voluntario = $this->criarUsuario('voluntario', $cidadeA, 'Voluntário');
        $remoto = $this->criarUsuario('voluntario', $cidadeB, 'Pessoa remota');

        $this->actingAs($coordenador)
            ->get(route('dashboards.participacao-semestral', ['ano' => 2026, 'semestre' => 1, 'visao_global' => 1, 'busca' => 'Pessoa remota']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('integrantes.data.0.nome', 'Pessoa remota'));

        $this->actingAs($voluntario)
            ->get(route('dashboards.participacao-semestral'))
            ->assertForbidden();
    }

    public function test_filtro_de_inativos_usa_o_status_atual_do_cadastro(): void
    {
        $cidade = $this->criarCidade('Porto Alegre');
        $gestor = $this->criarUsuario('administrador', $cidade, 'Gestora');
        $inativo = $this->criarUsuario('voluntario', $cidade, 'Inativo', 'inativo');

        $this->actingAs($gestor)
            ->get(route('dashboards.participacao-semestral', ['ano' => 2026, 'semestre' => 1, 'busca' => 'Inativo']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('integrantes.data', 0));

        $this->actingAs($gestor)
            ->get(route('dashboards.participacao-semestral', ['ano' => 2026, 'semestre' => 1, 'situacao' => 'inativos', 'busca' => 'Inativo']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('integrantes.data', 1)
                ->where('integrantes.data.0.id', $inativo->id)
                ->where('integrantes.data.0.reunioes', 0)
                ->where('integrantes.data.0.oficinas', 0));
    }

    private function criarCidade(string $nome): Cidade
    {
        $estado = Estado::query()->firstOrCreate(['sigla' => 'RS'], ['nome' => 'Rio Grande do Sul']);

        return Cidade::query()->create(['nome' => $nome, 'estado_id' => $estado->id]);
    }

    private function criarUsuario(string $cargo, Cidade $cidade, string $nome, string $status = 'ativo'): User
    {
        $voluntario = Voluntario::query()->create([
            'nome_completo' => $nome,
            'email' => str($nome)->slug()->append('-'.uniqid().'@teste.com'),
            'cidade_base_id' => $cidade->id,
            'status' => $status,
        ]);
        $usuario = User::factory()->create(['voluntario_id' => $voluntario->id, 'name' => $nome]);
        $modeloCargo = Cargo::query()->firstOrCreate(['slug' => $cargo], ['nome' => $cargo]);
        $usuario->cargos()->attach($modeloCargo);

        return $usuario;
    }

    private function criarEvento(Cidade $cidade, User $criador, string $tipo, string $inicio, string $status = 'finalizado'): Evento
    {
        return Evento::query()->create([
            'titulo' => ucfirst($tipo),
            'tipo' => $tipo,
            'cidade_id' => $cidade->id,
            'data_inicio' => $inicio,
            'data_fim' => now()->parse($inicio)->addHours(2),
            'status' => $status,
            'criado_por_id' => $criador->id,
        ]);
    }
}
