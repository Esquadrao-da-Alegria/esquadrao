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

    public function test_voluntario_sem_permissao_recebe_403(): void
    {
        $user = $this->criarUsuarioComCargo('voluntario');

        $this->actingAs($user)
            ->get(route('visitas.agenda-liberacao.index'))
            ->assertForbidden();
    }

    public function test_coordenador_local_lista_meses_do_ano(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $user   = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $ano    = (int) now()->year;

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('visitas.agenda-liberacao.index', ['ano' => $ano]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Visita/Agenda/Liberacao/Index')
                ->where('ano', $ano)
                ->has('meses', 12)
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
            ->from(route('visitas.agenda-liberacao.index'))
            ->put(route('visitas.agenda-liberacao.update'), [
                'ano'      => $ano,
                'mes'      => $mes,
                'liberado' => true,
            ])
            ->assertRedirect(route('visitas.agenda-liberacao.index'))
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
            ->from(route('visitas.agenda-liberacao.index'))
            ->put(route('visitas.agenda-liberacao.update'), [
                'ano'      => $ano,
                'mes'      => $mes,
                'liberado' => true,
            ])
            ->assertSessionHasErrors('geral');
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
