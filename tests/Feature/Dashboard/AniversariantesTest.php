<?php

namespace Tests\Feature\Dashboard;

use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AniversariantesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_exibe_aniversariantes_ativos_da_cidade_ordenados_e_sem_ano_de_nascimento(): void
    {
        $this->travelTo(now()->setDate(2026, 2, 28)->setTime(10, 0));
        $cidade = $this->criarCidade('Porto Alegre');
        $outraCidade = $this->criarCidade('Canoas');
        $usuario = $this->criarUsuario($cidade, 'Gustavo', '1990-02-28');
        $this->criarUsuario($cidade, 'Lia', '2000-02-29');
        $this->criarUsuario($cidade, 'Ana', '1993-02-05');
        $this->criarUsuario($cidade, 'Sem nascimento', null);
        $this->criarUsuario($cidade, 'Voluntário inativo', '1992-02-12', 'inativo');
        $this->criarUsuario($cidade, 'Conta inativa', '1992-02-14', 'ativo', 'inativo');
        $this->criarUsuario($outraCidade, 'Outra cidade', '1991-02-10');

        $this->actingAs($usuario)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('aniversariantes.itens', 3)
                ->where('aniversariantes.mes', 2)
                ->where('aniversariantes.itens.0.nome', 'Ana')
                ->where('aniversariantes.itens.1.nome', 'Gustavo')
                ->where('aniversariantes.itens.2.nome', 'Lia')
                ->where('aniversariantes.itens.1.eh_hoje', true)
                ->where('aniversariantes.itens.2.eh_hoje', true)
                ->missing('aniversariantes.itens.0.data_nascimento')
                ->missing('aniversariantes.itens.0.ano')
                ->where('aniversariante_atual.nome', 'Gustavo')
                ->where('aniversariante_atual.data', '2026-02-28')
            );

    }

    public function test_gestor_global_pode_consultar_outra_cidade_e_usuario_restrito_nao(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 10)->setTime(10, 0));
        $cidade = $this->criarCidade('Porto Alegre');
        $outraCidade = $this->criarCidade('Canoas');
        $gestor = $this->criarUsuario($cidade, 'Gestor', '1990-01-01', 'ativo', 'ativo', ['administrador']);
        $restrito = $this->criarUsuario($cidade, 'Restrito', '1990-01-01');
        $this->criarUsuario($cidade, 'Pessoa local', '1990-09-04');
        $this->criarUsuario($outraCidade, 'Pessoa remota', '1990-09-05');

        $this->actingAs($gestor)
            ->get(route('dashboard', ['cidade_aniversariantes_id' => $outraCidade->id]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('aniversariantes.cidade_id', $outraCidade->id)
                ->where('aniversariantes.itens.0.nome', 'Pessoa remota')
            );

        $this->actingAs($restrito)
            ->get(route('dashboard', ['cidade_aniversariantes_id' => $outraCidade->id]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('aniversariantes.cidade_id', $cidade->id)
                ->where('aniversariantes.itens.0.nome', 'Pessoa local')
            );
    }

    public function test_mensagem_de_aniversario_e_exclusiva_do_usuario_autenticado(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 10)->setTime(10, 0));
        $cidade = $this->criarCidade('Porto Alegre');
        $gustavo = $this->criarUsuario($cidade, 'Gustavo', '1990-09-10');
        $maria = $this->criarUsuario($cidade, 'Maria', '1991-09-10');
        $outro = $this->criarUsuario($cidade, 'Outro', '1992-09-11');

        $this->actingAs($gustavo)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('aniversariante_atual.nome', 'Gustavo')
            );

        $this->actingAs($maria)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('aniversariante_atual.nome', 'Maria')
            );

        $this->actingAs($outro)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('aniversariante_atual', null)
            );
    }

    private function criarCidade(string $nome): Cidade
    {
        $estado = Estado::query()->firstOrCreate(['sigla' => 'RS'], ['nome' => 'Rio Grande do Sul']);

        return Cidade::query()->create(['nome' => $nome, 'estado_id' => $estado->id]);
    }

    private function criarUsuario(Cidade $cidade, string $nome, ?string $nascimento, string $statusVoluntario = 'ativo', string $statusUsuario = 'ativo', array $cargos = ['voluntario']): User
    {
        $voluntario = Voluntario::query()->create([
            'nome_completo' => $nome,
            'email' => str($nome)->slug()->append('-'.uniqid().'@teste.com'),
            'cidade_base_id' => $cidade->id,
            'data_nascimento' => $nascimento,
            'status' => $statusVoluntario,
        ]);
        $usuario = User::factory()->create([
            'voluntario_id' => $voluntario->id,
            'name' => $nome,
            'status' => $statusUsuario,
        ]);

        foreach ($cargos as $cargo) {
            $model = Cargo::query()->firstOrCreate(['slug' => $cargo], ['nome' => str($cargo)->replace('_', ' ')->title()]);
            $usuario->cargos()->attach($model);
        }

        return $usuario;
    }
}
