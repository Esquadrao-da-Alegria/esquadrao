<?php

namespace Tests\Feature\Voluntario;

use App\Models\Cidade;
use App\Models\Estado;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VoluntarioIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function criarAdminLogado(int $cidadeBaseId): User
    {
        $voluntarioLogado = Voluntario::query()->create([
            'nome_completo'  => 'Voluntario Admin',
            'email'          => 'admin_' . uniqid() . '@teste.com',
            'cidade_base_id' => $cidadeBaseId,
            'status'         => 'ativo',
        ]);
        $user = User::factory()->createOne(['voluntario_id' => $voluntarioLogado->id]);
        $cargo = \App\Models\Cargo::create(['nome' => 'Administrador', 'slug' => 'administrador']);
        $user->cargos()->attach($cargo);

        return $user;
    }

    public function test_convidado_e_redirecionado_para_login(): void
    {
        $this->get(route('voluntarios.index'))->assertRedirect(route('login'));
    }

    public function test_filtra_voluntarios_automaticamente_pela_cidade_base_do_voluntario_logado(): void
    {
        $estado = Estado::query()->create(['nome' => 'RS', 'sigla' => 'RS']);
        $cidadePOA = Cidade::query()->forceCreate(['nome' => 'Porto Alegre', 'estado_id' => $estado->id]);
        $cidadeSM  = Cidade::query()->forceCreate(['nome' => 'Santa Maria', 'estado_id' => $estado->id]);

        $userLogado = $this->criarAdminLogado($cidadePOA->id);

        $voluntarioPOA = Voluntario::query()->create([
            'nome_completo'  => 'Voluntario POA',
            'email'          => 'poa@teste.com',
            'cidade_base_id' => $cidadePOA->id,
            'status'         => 'ativo',
        ]);
        User::factory()->createOne(['voluntario_id' => $voluntarioPOA->id]);

        $voluntarioSM = Voluntario::query()->create([
            'nome_completo'  => 'Voluntario SM',
            'email'          => 'sm@teste.com',
            'cidade_base_id' => $cidadeSM->id,
            'status'         => 'ativo',
        ]);
        User::factory()->createOne(['voluntario_id' => $voluntarioSM->id]);

        $this->actingAs($userLogado)
            ->get(route('voluntarios.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Voluntario/Index')
                ->where('cidadeId', $cidadePOA->id)
                ->where('cidadeUsuarioId', $cidadePOA->id)
                ->has('voluntarios.data', 2)
            );
    }

    public function test_permite_selecionar_outra_cidade_na_listagem_de_voluntarios(): void
    {
        $estado = Estado::query()->create(['nome' => 'RS', 'sigla' => 'RS']);
        $cidadePOA = Cidade::query()->forceCreate(['nome' => 'Porto Alegre', 'estado_id' => $estado->id]);
        $cidadeSM  = Cidade::query()->forceCreate(['nome' => 'Santa Maria', 'estado_id' => $estado->id]);

        $userLogado = $this->criarAdminLogado($cidadePOA->id);

        $voluntarioSM = Voluntario::query()->create([
            'nome_completo'  => 'Voluntario SM',
            'email'          => 'sm2@teste.com',
            'cidade_base_id' => $cidadeSM->id,
            'status'         => 'ativo',
        ]);
        User::factory()->createOne(['voluntario_id' => $voluntarioSM->id]);

        $this->actingAs($userLogado)
            ->get(route('voluntarios.index', ['cidade_id' => $cidadeSM->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Voluntario/Index')
                ->where('cidadeId', $cidadeSM->id)
                ->has('voluntarios.data', 1)
            );
    }

    public function test_permite_selecionar_todas_as_cidades_na_listagem_de_voluntarios(): void
    {
        $estado = Estado::query()->create(['nome' => 'RS', 'sigla' => 'RS']);
        $cidadePOA = Cidade::query()->forceCreate(['nome' => 'Porto Alegre', 'estado_id' => $estado->id]);
        $cidadeSM  = Cidade::query()->forceCreate(['nome' => 'Santa Maria', 'estado_id' => $estado->id]);

        $userLogado = $this->criarAdminLogado($cidadePOA->id);

        $voluntarioSM = Voluntario::query()->create([
            'nome_completo'  => 'Voluntario SM',
            'email'          => 'sm3@teste.com',
            'cidade_base_id' => $cidadeSM->id,
            'status'         => 'ativo',
        ]);
        User::factory()->createOne(['voluntario_id' => $voluntarioSM->id]);

        $this->actingAs($userLogado)
            ->get(route('voluntarios.index', ['cidade_id' => 'todas']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Voluntario/Index')
                ->where('cidadeId', 'todas')
                ->has('voluntarios.data', 2)
            );
    }
}
