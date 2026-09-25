<?php

namespace Tests\Feature\Hospital;

use App\Models\Cargo;
use App\Models\Ala;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Hospital;
use App\Models\MetaMensalHospital;
use App\Models\MetaPeriodoHospital;
use App\Models\MetaPadraoHospital;
use App\Models\MetaSemanalHospital;
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
        $hospital = $this->criarHospital($this->criarCidade('Santa Maria')->id);

        $this->get(route('hospitais.metas.index', $hospital))->assertRedirect(route('login'));
    }

    public function test_voluntario_sem_permissao_recebe_403(): void
    {
        $user = $this->criarUsuarioComCargo('voluntario');
        $hospital = $this->criarHospital($this->criarCidade('Santa Maria')->id);

        $this->actingAs($user)
            ->get(route('hospitais.metas.index', $hospital))
            ->assertForbidden();
    }

    public function test_coordenador_local_acessa_tela_de_metas(): void
    {
        $cidade   = $this->criarCidade('Santa Maria');
        $user     = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $hospital = $this->criarHospital($cidade->id);

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('hospitais.metas.index', ['hospital' => $hospital, 'ano' => 2026, 'mes' => 6]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Hospital/Meta/Index')
                ->where('ano', 2026)
                ->where('mes', 6)
                ->has('hospitais', 1)
                ->where('hospitais.0.id', $hospital->id)
                ->has('semanas', 5)
                ->where('semanas.0', [
                    'semana'          => 1,
                    'dia_inicio'      => 1,
                    'dia_fim'         => 6,
                    'nome_dia_inicio' => 'segunda-feira',
                    'nome_dia_fim'    => 'sábado',
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
            'meta_mensal'    => 10,
            'metas_por_ala'  => false,
            'metas_semanais' => [],
        ];

        $this->actingAs($user)
            ->from(route('hospitais.metas.index', $hospital))
            ->put(route('hospitais.metas.update', $hospital), $payload)
            ->assertRedirect(route('hospitais.metas.index', ['hospital' => $hospital, 'ano' => 2026, 'mes' => 6]))
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
            'meta_mensal'    => 10,
            'metas_por_ala'  => false,
            'metas_semanais' => [
                ['semana' => 1, 'quantidade' => 4],
                ['semana' => 2, 'quantidade' => 4],
            ],
        ];

        $this->actingAs($user)
            ->from(route('hospitais.metas.index', $hospital))
            ->put(route('hospitais.metas.update', $hospital), $payload)
            ->assertSessionHasErrors('geral');

        $this->assertSame(0, MetaMensalHospital::query()->count());
    }

    public function test_coordenador_local_nao_acessa_metas_de_outra_cidade(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $outraCidade = $this->criarCidade('Porto Alegre');
        $user = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $hospital = $this->criarHospital($outraCidade->id);

        $this->actingAs($user)
            ->get(route('hospitais.metas.index', $hospital))
            ->assertForbidden();
    }

    public function test_gestores_visualizam_hospitais_e_acessam_metas(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $hospital = $this->criarHospital($cidade->id);

        foreach (['administrador', 'diretor', 'coordenador_geral', 'coordenador_local'] as $cargo) {
            $user = $this->criarUsuarioComCargoCidade($cargo, $cidade->id);

            $this->actingAs($user)
                ->withoutVite()
                ->get(route('hospitais.index'))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('Hospital/Index')
                    ->has('hospitais', 1)
                    ->where('hospitais.0.id', $hospital->id));

            $this->actingAs($user)
                ->withoutVite()
                ->get(route('hospitais.metas.index', $hospital))
                ->assertOk();
        }
    }

    public function test_gestor_global_acessa_meta_de_hospital_de_outra_cidade(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $outraCidade = $this->criarCidade('Porto Alegre');
        $user = $this->criarUsuarioComCargoCidade('administrador', $cidade->id);
        $hospital = $this->criarHospital($outraCidade->id);

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('hospitais.metas.index', $hospital))
            ->assertOk();
    }

    public function test_administrador_de_suporte_sem_cidade_visualiza_hospitais_e_metas(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $hospital = $this->criarHospital($cidade->id);
        $user = $this->criarUsuarioComCargo('administrador');

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('hospitais.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('eh_gestor', true)
                ->where('pode_editar_dados', true)
                ->where('hospitais.0.id', $hospital->id));

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('hospitais.metas.index', $hospital))
            ->assertOk();
    }

    public function test_salva_metas_semanais_por_ala_do_hospital(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $user = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $hospital = $this->criarHospital($cidade->id);
        $ala = Ala::query()->create(['hospital_id' => $hospital->id, 'nome' => 'Pediatria']);

        $this->actingAs($user)
            ->put(route('hospitais.metas.update', $hospital), [
                'ano' => 2026,
                'mes' => 6,
                'meta_mensal' => 10,
                'metas_por_ala' => true,
                'metas_semanais' => [
                    ['semana' => 1, 'quantidade' => 5, 'ala_unidade_id' => $ala->id],
                    ['semana' => 2, 'quantidade' => 5, 'ala_unidade_id' => $ala->id],
                ],
            ])
            ->assertSessionHas('mensagem_sucesso');

        $this->assertSame(2, MetaSemanalHospital::query()
            ->where('hospital_id', $hospital->id)
            ->where('ala_unidade_id', $ala->id)
            ->count());
    }

    public function test_rejeita_meta_com_ala_de_outro_hospital(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $user = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $hospital = $this->criarHospital($cidade->id);
        $outroHospital = $this->criarHospital($cidade->id);
        $ala = Ala::query()->create(['hospital_id' => $outroHospital->id, 'nome' => 'Pediatria']);

        $this->actingAs($user)
            ->put(route('hospitais.metas.update', $hospital), [
                'ano' => 2026,
                'mes' => 6,
                'meta_mensal' => 5,
                'metas_por_ala' => true,
                'metas_semanais' => [
                    ['semana' => 1, 'quantidade' => 5, 'ala_unidade_id' => $ala->id],
                ],
            ])
            ->assertSessionHasErrors('geral');

        $this->assertDatabaseMissing('metas_mensais_hospitais', [
            'hospital_id' => $hospital->id,
            'ano' => 2026,
            'mes' => 6,
        ]);
    }

    public function test_salva_metas_quinzenais_sem_perder_a_meta_mensal(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $user = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $hospital = $this->criarHospital($cidade->id);

        $this->actingAs($user)
            ->put(route('hospitais.metas.update', $hospital), [
                'ano' => 2026,
                'mes' => 6,
                'meta_mensal' => 4,
                'periodicidade' => 'quinzenal',
                'metas_por_ala' => false,
                'metas_periodos' => [
                    ['periodo' => 1, 'quantidade' => 2],
                    ['periodo' => 2, 'quantidade' => 2],
                ],
            ])
            ->assertSessionHas('mensagem_sucesso');

        $this->assertDatabaseHas('metas_mensais_hospitais', [
            'hospital_id' => $hospital->id,
            'ano' => 2026,
            'mes' => 6,
            'quantidade' => 4,
            'periodicidade' => 'quinzenal',
        ]);
        $this->assertSame(2, MetaPeriodoHospital::query()
            ->where('hospital_id', $hospital->id)
            ->count());

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('hospitais.metas.index', ['hospital' => $hospital, 'ano' => 2026, 'mes' => 6]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('hospitais.0.periodicidade', 'quinzenal')
                ->has('hospitais.0.metas_semanais', 2)
                ->where('hospitais.0.metas_semanais.0.semana', 1)
                ->where('hospitais.0.metas_semanais.1.semana', 2));
    }

    public function test_carrega_duas_quinzenas_por_ala(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $user = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $hospital = $this->criarHospital($cidade->id);
        $ala = Ala::query()->create(['hospital_id' => $hospital->id, 'nome' => 'Pediatria']);

        $this->actingAs($user)
            ->put(route('hospitais.metas.update', $hospital), [
                'ano' => 2026,
                'mes' => 6,
                'meta_mensal' => 4,
                'periodicidade' => 'quinzenal',
                'metas_por_ala' => true,
                'metas_periodos' => [
                    ['periodo' => 1, 'quantidade' => 2, 'ala_unidade_id' => $ala->id],
                    ['periodo' => 2, 'quantidade' => 2, 'ala_unidade_id' => $ala->id],
                ],
            ])
            ->assertSessionHas('mensagem_sucesso');

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('hospitais.metas.index', ['hospital' => $hospital, 'ano' => 2026, 'mes' => 6]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('hospitais.0.metas_por_ala', true)
                ->has('hospitais.0.metas_semanais', 2)
                ->where('hospitais.0.metas_semanais.0.ala_unidade_id', $ala->id)
                ->where('hospitais.0.metas_semanais.1.ala_unidade_id', $ala->id));
    }

    public function test_salva_meta_como_padrao_para_os_proximos_meses(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $user = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $hospital = $this->criarHospital($cidade->id);

        $this->actingAs($user)
            ->put(route('hospitais.metas.update', $hospital), [
                'ano' => 2026,
                'mes' => 6,
                'meta_mensal' => 4,
                'periodicidade' => 'quinzenal',
                'metas_por_ala' => false,
                'salvar_como_padrao' => true,
                'metas_periodos' => [
                    ['periodo' => 1, 'quantidade' => 2],
                    ['periodo' => 2, 'quantidade' => 2],
                ],
            ])
            ->assertSessionHas('mensagem_sucesso');

        $this->assertDatabaseHas('metas_padrao_hospitais', [
            'hospital_id' => $hospital->id,
            'quantidade' => 4,
            'periodicidade' => 'quinzenal',
            'metas_por_ala' => false,
        ]);
        $this->assertSame(2, MetaPadraoHospital::query()->firstOrFail()->periodos()->count());
    }

    public function test_mes_sem_configuracao_propria_exibe_meta_padrao(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $user = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $hospital = $this->criarHospital($cidade->id);
        $padrao = MetaPadraoHospital::query()->create([
            'hospital_id' => $hospital->id,
            'quantidade' => 4,
            'periodicidade' => 'quinzenal',
            'metas_por_ala' => false,
        ]);
        $padrao->periodos()->createMany([
            ['periodo' => 1, 'quantidade' => 2],
            ['periodo' => 2, 'quantidade' => 2],
        ]);

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('hospitais.metas.index', ['hospital' => $hospital, 'ano' => 2026, 'mes' => 7]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('hospitais.0.meta_mensal', 4)
                ->where('hospitais.0.periodicidade', 'quinzenal')
                ->has('hospitais.0.metas_semanais', 2));
    }

    public function test_meta_mensal_especifica_prevalece_sobre_o_padrao(): void
    {
        $cidade = $this->criarCidade('Santa Maria');
        $user = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $hospital = $this->criarHospital($cidade->id);
        MetaPadraoHospital::query()->create([
            'hospital_id' => $hospital->id,
            'quantidade' => 4,
            'periodicidade' => 'quinzenal',
            'metas_por_ala' => false,
        ]);
        MetaMensalHospital::query()->create([
            'hospital_id' => $hospital->id,
            'ano' => 2026,
            'mes' => 7,
            'quantidade' => 3,
            'periodicidade' => 'semanal',
        ]);

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('hospitais.metas.index', ['hospital' => $hospital, 'ano' => 2026, 'mes' => 7]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('hospitais.0.meta_mensal', 3)
                ->where('hospitais.0.periodicidade', 'semanal'));
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
