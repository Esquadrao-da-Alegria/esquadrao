<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private const ROTAS_GERENCIAIS = [
        'dashboards.visao-geral',
        'dashboards.visitas-por-hospital',
        'dashboards.visitas-por-participante',
    ];

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $this->get(route('dashboards.meu'))->assertRedirect(route('login'));
    }

    public function test_dashboard_legado_mantem_acesso_ao_dashboard_individual(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard'));
    }

    public function test_voluntario_autenticado_acessa_dashboard_individual(): void
    {
        $this->actingAs($this->criarUsuarioComCargo('voluntario'))
            ->get(route('dashboards.meu'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard/Meu'));
    }

    public function test_voluntario_comum_nao_acessa_dashboards_gerenciais(): void
    {
        $user = $this->criarUsuarioComCargo('voluntario');

        foreach (self::ROTAS_GERENCIAIS as $rota) {
            $this->actingAs($user)->get(route($rota))->assertForbidden();
        }
    }

    public function test_administrador_acessa_todos_os_dashboards_gerenciais(): void
    {
        $this->assertAcessaDashboardsGerenciais($this->criarUsuarioComCargo('administrador'));
    }

    public function test_perfis_globais_abrem_dashboards_na_propria_cidade_por_padrao(): void
    {
        $cidade = $this->criarCidade();
        $user = $this->criarUsuarioComCargo('administrador', $cidade->id);

        foreach (['dashboards.visitas-por-hospital', 'dashboards.visitas-por-participante'] as $rota) {
            $this->actingAs($user)
                ->get(route($rota))
                ->assertInertia(fn (Assert $page) => $page
                    ->where('filtros.cidade_id', $cidade->id)
                    ->where('filtros.visao_global', false));
        }
    }

    public function test_administrador_de_suporte_sem_cidade_mantem_visao_global(): void
    {
        $user = User::factory()->create();
        $this->vincularCargo($user, 'administrador');

        foreach (['dashboards.visitas-por-hospital', 'dashboards.visitas-por-participante'] as $rota) {
            $this->actingAs($user)
                ->get(route($rota, ['visao_global' => 1]))
                ->assertInertia(fn (Assert $page) => $page
                    ->where('filtros.cidade_id', null)
                    ->where('filtros.visao_global', true));
        }
    }

    public function test_coordenador_geral_acessa_todos_os_dashboards_gerenciais(): void
    {
        $this->assertAcessaDashboardsGerenciais($this->criarUsuarioComCargo('coordenador_geral'));
    }

    public function test_diretor_nao_acessa_dashboards_gerenciais(): void
    {
        $user = $this->criarUsuarioComCargo('diretor');

        foreach (self::ROTAS_GERENCIAIS as $rota) {
            $this->actingAs($user)->get(route($rota))->assertForbidden();
        }
    }

    public function test_coordenador_local_com_cidade_acessa_dashboards_gerenciais(): void
    {
        $this->assertAcessaDashboardsGerenciais(
            $this->criarUsuarioComCargo('coordenador_local', $this->criarCidade()->id)
        );
    }

    public function test_coordenador_local_sem_cidade_nao_acessa_dashboards_gerenciais(): void
    {
        $user = $this->criarUsuarioComCargo('coordenador_local');

        foreach (self::ROTAS_GERENCIAIS as $rota) {
            $this->actingAs($user)->get(route($rota))->assertForbidden();
        }
    }

    public function test_multiplos_cargos_combinam_permissoes(): void
    {
        $user = $this->criarUsuarioComCargo('coordenador_local');
        $this->vincularCargo($user, 'administrador');

        $this->assertAcessaDashboardsGerenciais($user);
    }

    public function test_inertia_compartilha_somente_as_permissoes_esperadas(): void
    {
        $user = $this->criarUsuarioComCargo('voluntario');

        $this->actingAs($user)
            ->get(route('dashboards.meu'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('permissoes_dashboards', [
                    'dashboard.meu' => true,
                    'dashboard.visao_geral' => false,
                    'dashboard.visitas_por_hospital' => false,
                    'dashboard.visitas_por_participante' => false,
                ]));
    }

    private function assertAcessaDashboardsGerenciais(User $user): void
    {
        foreach (self::ROTAS_GERENCIAIS as $rota) {
            $this->actingAs($user)->get(route($rota))->assertOk();
        }
    }

    private function criarUsuarioComCargo(string $slug, ?int $cidadeId = null): User
    {
        $voluntario = Voluntario::query()->create([
            'nome_completo' => 'Voluntário '.uniqid(),
            'email' => uniqid().'@example.com',
            'cidade_base_id' => $cidadeId,
            'status' => 'ativo',
        ]);

        $user = User::factory()->create(['voluntario_id' => $voluntario->id]);
        $this->vincularCargo($user, $slug);

        return $user;
    }

    private function vincularCargo(User $user, string $slug): void
    {
        $cargo = Cargo::query()->firstOrCreate(
            ['slug' => $slug],
            ['nome' => str($slug)->replace('_', ' ')->title()]
        );

        $user->cargos()->attach($cargo);
        $user->unsetRelation('cargos');
    }

    private function criarCidade(): Cidade
    {
        $estado = Estado::query()->create([
            'nome' => 'Rio Grande do Sul',
            'sigla' => 'RS',
        ]);

        $cidade = new Cidade(['nome' => 'Porto Alegre']);
        $cidade->estado_id = $estado->id;
        $cidade->save();

        return $cidade;
    }
}
