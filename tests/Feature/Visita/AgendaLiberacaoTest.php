<?php

namespace Tests\Feature\Visita;

use App\Models\AgendaLiberacaoCidade;
use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AgendaLiberacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_convidado_redireciona_login(): void
    {
        $this->get(route('visitas.agenda-liberacao.index'))->assertRedirect(route('login'));
    }

    public function test_redirecionamento_legado_nao_intercepta_atualizacao(): void
    {
        $rotaIndex = app('router')->getRoutes()->getByName('visitas.agenda-liberacao.index');
        $rotaUpdate = app('router')->getRoutes()->getByName('visitas.agenda-liberacao.update');

        $this->assertSame(['GET', 'HEAD'], $rotaIndex->methods());
        $this->assertSame(['PUT'], $rotaUpdate->methods());
    }

    public function test_voluntario_sem_permissao_recebe_403(): void
    {
        $user = $this->criarUsuarioComCargo('voluntario');
        $cidade = $this->criarCidade('Santa Maria');

        $this->actingAs($user)
            ->put(route('visitas.agenda-liberacao.update'), [
                'cidade_id' => $cidade->id,
                'ano' => now()->year,
                'mes' => now()->month,
                'liberado' => true,
            ])
            ->assertForbidden();
    }

    public function test_coordenador_local_visualiza_situacao_do_mes_em_visitas(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $user   = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $ano    = (int) now()->year;

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('visitas.index', [
                'mes' => sprintf('%04d-%02d', $ano, now()->month),
                'cidade_id' => $cidade->id,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Visita/Index')
                ->where('ehGestor', true)
                ->has('agendaLiberacao')
            );
    }

    public function test_libera_mes_futuro(): void
    {
        $cidade     = $this->criarCidade('Santa Maria');
        $user       = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $referencia = now()->copy()->addMonth()->startOfMonth();
        $ano        = (int) $referencia->year;
        $mes        = (int) $referencia->month;

        AgendaLiberacaoCidade::query()->updateOrCreate(
            ['cidade_id' => $cidade->id, 'ano' => $ano, 'mes' => $mes],
            ['liberado' => false, 'liberado_por_id' => null],
        );

        $this->actingAs($user)
            ->from(route('visitas.index'))
            ->put(route('visitas.agenda-liberacao.update'), [
                'cidade_id' => $cidade->id,
                'ano'      => $ano,
                'mes'      => $mes,
                'liberado' => true,
            ])
            ->assertRedirect(route('visitas.index'))
            ->assertSessionHas('mensagem_sucesso');

        $this->assertDatabaseHas('agenda_liberacoes_cidades', [
            'cidade_id'       => $cidade->id,
            'ano'             => $ano,
            'mes'             => $mes,
            'liberado'        => true,
            'liberado_por_id' => $user->id,
        ]);
    }

    public function test_rejeita_alterar_mes_passado(): void
    {
        $cidade     = $this->criarCidade('Santa Maria');
        $user       = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $referencia = now()->copy()->subMonth()->startOfMonth();
        $ano        = (int) $referencia->year;
        $mes        = (int) $referencia->month;

        $this->actingAs($user)
            ->from(route('visitas.index'))
            ->put(route('visitas.agenda-liberacao.update'), [
                'cidade_id' => $cidade->id,
                'ano'      => $ano,
                'mes'      => $mes,
                'liberado' => true,
            ])
            ->assertSessionHasErrors('geral');
    }

    public function test_mes_sem_registro_inicia_bloqueado(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $user = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $referencia = now()->copy()->addMonths(2)->startOfMonth();

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('visitas.index', [
                'mes' => $referencia->format('Y-m'),
                'cidade_id' => $cidade->id,
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('agendaLiberacao.liberado', false)
                ->where('agendaLiberacao.editavel', true));
    }

    public function test_coordenador_local_nao_altera_agenda_de_outra_cidade(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $outraCidade = $this->criarCidade('Porto Alegre');
        $user = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);

        $this->actingAs($user)
            ->put(route('visitas.agenda-liberacao.update'), [
                'cidade_id' => $outraCidade->id,
                'ano' => now()->year,
                'mes' => now()->month,
                'liberado' => true,
            ])
            ->assertForbidden();
    }

    public function test_todos_os_gestores_podem_liberar_agendamento(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $referencia = now()->copy()->addMonth()->startOfMonth();

        foreach (['administrador', 'diretor', 'coordenador_geral', 'coordenador_local'] as $cargo) {
            $user = $this->criarUsuarioComCargoCidade($cargo, $cidade->id);

            $this->actingAs($user)
                ->put(route('visitas.agenda-liberacao.update'), [
                    'cidade_id' => $cidade->id,
                    'ano' => $referencia->year,
                    'mes' => $referencia->month,
                    'liberado' => true,
                ])
                ->assertSessionHas('mensagem_sucesso');
        }
    }

    public function test_administrador_sem_cidade_pode_liberar_cidade_selecionada(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $user = $this->criarUsuarioComCargo('administrador');
        $referencia = now()->copy()->addMonth()->startOfMonth();

        $this->actingAs($user)
            ->put(route('visitas.agenda-liberacao.update'), [
                'cidade_id' => $cidade->id,
                'ano' => $referencia->year,
                'mes' => $referencia->month,
                'liberado' => true,
            ])
            ->assertSessionHas('mensagem_sucesso');

        $this->assertDatabaseHas('agenda_liberacoes_cidades', [
            'cidade_id' => $cidade->id,
            'ano' => $referencia->year,
            'mes' => $referencia->month,
            'liberado' => true,
        ]);
    }

    public function test_voluntario_visualiza_bloqueio_e_nao_abre_formulario_por_url(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $user = $this->criarUsuarioComCargoCidade('voluntario', $cidade->id);
        $referencia = now()->copy()->addMonth()->startOfMonth();

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('visitas.index', [
                'mes' => $referencia->format('Y-m'),
                'cidade_id' => $cidade->id,
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('agendaLiberacao.liberado', false));

        $this->actingAs($user)
            ->get(route('visitas.create', [
                'mes' => $referencia->format('Y-m'),
                'cidade_id' => $cidade->id,
            ]))
            ->assertRedirect(route('visitas.index', [
                'mes' => $referencia->format('Y-m'),
                'cidade_id' => $cidade->id,
            ]))
            ->assertSessionHas('mensagem_alerta');
    }

    public function test_voluntario_abre_formulario_quando_mes_esta_liberado(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $user = $this->criarUsuarioComCargoCidade('voluntario', $cidade->id);
        $referencia = now()->copy()->addMonth()->startOfMonth();

        AgendaLiberacaoCidade::query()->create([
            'cidade_id' => $cidade->id,
            'ano' => $referencia->year,
            'mes' => $referencia->month,
            'liberado' => true,
        ]);

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('visitas.create', [
                'mes' => $referencia->format('Y-m'),
                'cidade_id' => $cidade->id,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Visita/Create'));
    }

    public function test_gestor_bloqueia_novamente_mes_liberado(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $user = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $referencia = now()->copy()->addMonth()->startOfMonth();

        AgendaLiberacaoCidade::query()->create([
            'cidade_id' => $cidade->id,
            'ano' => $referencia->year,
            'mes' => $referencia->month,
            'liberado' => true,
            'liberado_por_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->put(route('visitas.agenda-liberacao.update'), [
                'cidade_id' => $cidade->id,
                'ano' => $referencia->year,
                'mes' => $referencia->month,
                'liberado' => false,
            ])
            ->assertSessionHas('mensagem_sucesso');

        $this->assertDatabaseHas('agenda_liberacoes_cidades', [
            'cidade_id' => $cidade->id,
            'ano' => $referencia->year,
            'mes' => $referencia->month,
            'liberado' => false,
            'liberado_por_id' => null,
        ]);
    }

    private function criarCidade(string $nome): Cidade
    {
        $estado = Estado::query()->firstOrCreate(
            ['sigla' => 'RS'],
            ['nome' => 'RS'],
        );

        return Cidade::query()->firstOrCreate(
            ['nome' => $nome, 'estado_id' => $estado->id],
            ['nome' => $nome, 'estado_id' => $estado->id],
        );
    }

    private function criarUsuarioComCargo(string $slug): User
    {
        $cargo = Cargo::query()->firstOrCreate(
            ['slug' => $slug],
            ['nome' => ucfirst(str_replace('_', ' ', $slug))],
        );

        $user = User::factory()->create(['status' => User::STATUS_ATIVO]);
        $user->cargos()->syncWithoutDetaching([$cargo->id]);

        return $user->fresh('cargos');
    }

    private function criarUsuarioComCargoCidade(string $slug, int $cidadeId): User
    {
        $cargo = Cargo::query()->firstOrCreate(
            ['slug' => $slug],
            ['nome' => ucfirst(str_replace('_', ' ', $slug))],
        );

        $voluntario = Voluntario::query()->create([
            'nome_completo'  => 'Voluntário ' . uniqid(),
            'email'          => uniqid('vol_') . '@test.com',
            'status'         => User::STATUS_ATIVO,
            'cidade_base_id' => $cidadeId,
        ]);

        $user = User::factory()->create([
            'voluntario_id' => $voluntario->id,
            'status'        => User::STATUS_ATIVO,
        ]);
        $user->cargos()->syncWithoutDetaching([$cargo->id]);

        return $user->fresh(['cargos', 'voluntario']);
    }
}
