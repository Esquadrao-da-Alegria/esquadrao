<?php

namespace Tests\Feature\Voluntario;

use App\Models\Cargo;
use App\Models\ConviteCadastro;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReativacaoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_inativos_ficam_ocultos_por_padrao_e_aparecem_no_filtro_especifico(): void
    {
        $administrador = $this->administrador();
        [$voluntario] = $this->voluntarioInativo();

        $this->actingAs($administrador)
            ->get(route('voluntarios.index', ['cidade_id' => 'todas']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('voluntarios.data', fn ($dados) => collect($dados)->doesntContain('id', $voluntario->id))
            );

        $this->get(route('voluntarios.index', ['cidade_id' => 'todas', 'status' => 'inativos']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filtros.status', 'inativos')
                ->has('voluntarios.data', 1)
                ->where('voluntarios.data.0.id', $voluntario->id)
            );
    }

    public function test_filtro_de_inativos_respeita_busca(): void
    {
        $administrador = $this->administrador();
        [$encontrado] = $this->voluntarioInativo('Pessoa Encontrada', 'encontrada@teste.com');
        $this->voluntarioInativo('Outra Pessoa', 'outra@teste.com');

        $this->actingAs($administrador)
            ->get(route('voluntarios.index', ['cidade_id' => 'todas', 'status' => 'inativos', 'busca' => 'Encontrada']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('voluntarios.data', 1)
                ->where('voluntarios.data.0.id', $encontrado->id)
            );
    }

    public function test_conta_inativa_tambem_coloca_voluntario_no_filtro_de_inativos(): void
    {
        $administrador = $this->administrador();
        [$voluntario, $usuario] = $this->voluntarioInativo();
        $voluntario->update(['status' => User::STATUS_ATIVO]);

        $this->actingAs($administrador)
            ->get(route('voluntarios.index', ['cidade_id' => 'todas']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('voluntarios.data', fn ($dados) => collect($dados)->doesntContain('id', $voluntario->id))
            );

        $this->get(route('voluntarios.index', ['cidade_id' => 'todas', 'status' => 'inativos']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('voluntarios.data', 1)
                ->where('voluntarios.data.0.id', $voluntario->id)
            );

        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'status' => User::STATUS_INATIVO]);
    }

    public function test_administrador_reativa_voluntario_preservando_credenciais_cargos_e_convite(): void
    {
        $administrador = $this->administrador();
        [$voluntario, $usuario] = $this->voluntarioInativo();
        $cargo = Cargo::query()->create(['nome' => 'Apoio', 'slug' => 'apoio']);
        $usuario->cargos()->attach($cargo);
        $senha = $usuario->password;
        $convite = ConviteCadastro::query()->create([
            'voluntario_id' => $voluntario->id,
            'token' => hash('sha256', 'convite-utilizado'),
            'email' => $voluntario->email,
            'status' => ConviteCadastro::STATUS_UTILIZADO,
            'utilizado_em' => now()->subMonth(),
        ]);

        $this->actingAs($administrador)
            ->patch(route('voluntarios.reativar', $voluntario))
            ->assertRedirect(route('voluntarios.index', ['aba' => 'voluntarios', 'status' => 'inativos']));

        $this->assertDatabaseHas('voluntarios', ['id' => $voluntario->id, 'status' => User::STATUS_ATIVO]);
        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'status' => User::STATUS_ATIVO, 'inativado_em' => null, 'password' => $senha]);
        $this->assertDatabaseHas('voluntario_cargo', ['voluntario_id' => $usuario->id, 'cargo_id' => $cargo->id]);
        $this->assertDatabaseHas('convites_cadastro', ['id' => $convite->id, 'status' => ConviteCadastro::STATUS_UTILIZADO]);

        $this->get(route('voluntarios.index', ['cidade_id' => 'todas']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('voluntarios.data', fn ($dados) => collect($dados)->contains('id', $voluntario->id))
            );

        $this->post(route('logout'));
        $this->post(route('login.store'), [
            'email' => $usuario->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_usuario_comum_nao_pode_reativar_voluntario(): void
    {
        [$voluntario] = $this->voluntarioInativo();

        $this->actingAs(User::factory()->createOne())
            ->patch(route('voluntarios.reativar', $voluntario))
            ->assertForbidden();

        $this->assertDatabaseHas('voluntarios', ['id' => $voluntario->id, 'status' => User::STATUS_INATIVO]);
    }

    public function test_reativacao_sem_usuario_vinculado_e_bloqueada_com_mensagem_clara(): void
    {
        $administrador = $this->administrador();
        $voluntario = Voluntario::query()->create([
            'nome_completo' => 'Sem usuário',
            'email' => 'sem.usuario@teste.com',
            'status' => User::STATUS_INATIVO,
        ]);

        $this->actingAs($administrador)
            ->patch(route('voluntarios.reativar', $voluntario))
            ->assertRedirect()
            ->assertSessionHas('mensagem_erro');

        $this->assertDatabaseHas('voluntarios', ['id' => $voluntario->id, 'status' => User::STATUS_INATIVO]);
    }

    private function administrador(): User
    {
        $usuario = User::factory()->createOne();
        $cargo = Cargo::query()->firstOrCreate(['slug' => 'administrador'], ['nome' => 'Administrador']);
        $usuario->cargos()->attach($cargo);

        return $usuario;
    }

    private function voluntarioInativo(string $nome = 'Voluntária inativa', string $email = 'inativa@teste.com'): array
    {
        $voluntario = Voluntario::query()->create([
            'nome_completo' => $nome,
            'email' => $email,
            'status' => User::STATUS_INATIVO,
        ]);
        $usuario = User::factory()->createOne([
            'voluntario_id' => $voluntario->id,
            'name' => $nome,
            'email' => $email,
            'status' => User::STATUS_INATIVO,
            'inativado_em' => now()->subDay(),
        ]);

        return [$voluntario, $usuario];
    }
}
