<?php

namespace Tests\Feature\Hospital;

use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Hospital;
use App\Models\MetaMensalHospital;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_convidado_redireciona_login(): void
    {
        $this->get(route('hospitais.metas.index'))->assertRedirect(route('login'));
    }

    public function test_voluntario_sem_permissao_recebe_403(): void
    {
        $user = $this->criarUsuarioComCargo('voluntario');

        $this->actingAs($user)
            ->get(route('hospitais.metas.index'))
            ->assertForbidden();
    }

    public function test_coordenador_local_acessa_tela_de_metas(): void
    {
        $cidade   = $this->criarCidade('Santa Maria');
        $user     = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $hospital = $this->criarHospital($cidade->id);

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('hospitais.metas.index', ['ano' => 2026, 'mes' => 6]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Hospital/Meta/Index')
                ->where('ano', 2026)
                ->where('mes', 6)
                ->has('semanas', 5)
                ->where('semanas.0', [
                    'semana'     => 1,
                    'dia_inicio' => 1,
                    'dia_fim'    => 6,
                ])
            );

        $this->assertDatabaseHas('hospitais', [
            'id'        => $hospital->id,
            'cidade_id' => $cidade->id,
            'ativo'     => true,
        ]);
    }

    public function test_salva_meta_mensal(): void
    {
        $cidade   = $this->criarCidade('Santa Maria');
        $user     = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $hospital = $this->criarHospital($cidade->id);

        $payload = [
            'ano'       => 2026,
            'mes'       => 6,
            'hospitais' => [
                [
                    'hospital_id'    => $hospital->id,
                    'meta_mensal'    => 10,
                    'metas_por_ala'  => false,
                    'metas_semanais' => [],
                ],
            ],
        ];

        $this->actingAs($user)
            ->from(route('hospitais.metas.index'))
            ->put(route('hospitais.metas.update'), $payload)
            ->assertRedirect(route('hospitais.metas.index', ['ano' => 2026, 'mes' => 6]))
            ->assertSessionHas('mensagem_sucesso');

        $this->assertDatabaseHas('metas_mensais_hospitais', [
            'hospital_id' => $hospital->id,
            'ano'         => 2026,
            'mes'         => 6,
            'quantidade'  => 10,
        ]);
    }

    public function test_rejeita_soma_semanal_diferente_da_mensal(): void
    {
        $cidade   = $this->criarCidade('Santa Maria');
        $user     = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $hospital = $this->criarHospital($cidade->id);

        $payload = [
            'ano'       => 2026,
            'mes'       => 6,
            'hospitais' => [
                [
                    'hospital_id'    => $hospital->id,
                    'meta_mensal'    => 10,
                    'metas_por_ala'  => false,
                    'metas_semanais' => [
                        ['semana' => 1, 'quantidade' => 4],
                        ['semana' => 2, 'quantidade' => 4],
                    ],
                ],
            ],
        ];

        $this->actingAs($user)
            ->from(route('hospitais.metas.index'))
            ->put(route('hospitais.metas.update'), $payload)
            ->assertSessionHasErrors('geral');

        $this->assertSame(0, MetaMensalHospital::query()->count());
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

    private function criarHospital(int $cidadeId): Hospital
    {
        return Hospital::query()->create([
            'cidade_id' => $cidadeId,
            'nome'      => 'Hospital Teste ' . uniqid(),
            'cnpj'      => (string) random_int(10000000000000, 99999999999999),
            'endereco'  => 'Rua 1',
            'telefone'  => '51999999999',
            'email'     => 'a@b.com',
            'ativo'     => true,
        ]);
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
