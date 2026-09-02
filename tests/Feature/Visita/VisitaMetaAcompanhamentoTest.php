<?php

namespace Tests\Feature\Visita;

use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Ala;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Hospital;
use App\Models\MetaMensalHospital;
use App\Models\MetaSemanalHospital;
use App\Models\User;
use App\Models\Visita;
use App\Models\Voluntario;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VisitaMetaAcompanhamentoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_exibe_saldo_da_meta_com_visitas_agendadas_e_realizadas_da_cidade(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 2, 12));

        $cidade = $this->criarCidade('Porto Alegre');
        $outraCidade = $this->criarCidade('Canoas');
        $user = $this->criarUsuario($cidade->id);
        $hospital = $this->criarHospital($cidade, 'Hospital Central');
        $ala = Ala::query()->create(['hospital_id' => $hospital->id, 'nome' => 'Pediatria']);
        $hospitalOutraCidade = $this->criarHospital($outraCidade, 'Hospital Externo');

        MetaMensalHospital::query()->create([
            'hospital_id' => $hospital->id,
            'ano'         => 2026,
            'mes'         => 9,
            'quantidade'  => 4,
        ]);
        MetaSemanalHospital::query()->create([
            'hospital_id'    => $hospital->id,
            'ala_unidade_id' => $ala->id,
            'ano'            => 2026,
            'mes'            => 9,
            'semana'         => 1,
            'quantidade'     => 3,
        ]);
        MetaMensalHospital::query()->create([
            'hospital_id' => $hospitalOutraCidade->id,
            'ano'         => 2026,
            'mes'         => 9,
            'quantidade'  => 2,
        ]);

        $this->criarVisita($hospital, $user, '2026-09-02 10:00:00', VisitaStatus::Agendada, $ala);
        $this->criarVisita($hospital, $user, '2026-09-03 10:00:00', VisitaStatus::Realizada, $ala);
        $this->criarVisita($hospital, $user, '2026-09-04 10:00:00', VisitaStatus::Cancelada, $ala);
        $this->criarVisita($hospitalOutraCidade, $user, '2026-09-02 10:00:00', VisitaStatus::Agendada);

        $this->actingAs($user)
            ->get(route('visitas.index', ['mes' => '2026-09', 'cidade_id' => $cidade->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('acompanhamentoMetas', 1)
                ->where('acompanhamentoMetas.0.hospital', 'Hospital Central')
                ->where('acompanhamentoMetas.0.ala', 'Pediatria')
                ->where('acompanhamentoMetas.0.semana', 1)
                ->where('acompanhamentoMetas.0.meta_semanal', 3)
                ->where('acompanhamentoMetas.0.planejadas_semana', 2)
                ->where('acompanhamentoMetas.0.meta_mensal', 4)
                ->where('acompanhamentoMetas.0.planejadas_mes', 2)
            );
    }

    public function test_mes_futuro_usa_primeira_semana_ainda_nao_contemplada(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 2, 12));

        $cidade = $this->criarCidade('Porto Alegre');
        $user = $this->criarUsuario($cidade->id);
        $hospital = $this->criarHospital($cidade, 'Hospital Central');

        MetaMensalHospital::query()->create([
            'hospital_id' => $hospital->id,
            'ano'         => 2026,
            'mes'         => 10,
            'quantidade'  => 3,
        ]);
        foreach ([1 => 1, 2 => 2] as $semana => $quantidade) {
            MetaSemanalHospital::query()->create([
                'hospital_id'    => $hospital->id,
                'ala_unidade_id' => null,
                'ano'            => 2026,
                'mes'            => 10,
                'semana'         => $semana,
                'quantidade'     => $quantidade,
            ]);
        }
        $this->criarVisita($hospital, $user, '2026-10-02 10:00:00', VisitaStatus::Agendada);

        $this->actingAs($user)
            ->get(route('visitas.index', ['mes' => '2026-10', 'cidade_id' => $cidade->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('acompanhamentoMetas', 1)
                ->where('acompanhamentoMetas.0.semana', 2)
                ->where('acompanhamentoMetas.0.meta_semanal', 2)
                ->where('acompanhamentoMetas.0.planejadas_semana', 0)
            );
    }

    private function criarCidade(string $nome): Cidade
    {
        $estado = Estado::query()->firstOrCreate(['sigla' => 'RS'], ['nome' => 'Rio Grande do Sul']);

        return Cidade::query()->forceCreate([
            'nome'      => $nome,
            'estado_id' => $estado->id,
        ]);
    }

    private function criarUsuario(int $cidadeId): User
    {
        $voluntario = Voluntario::query()->create([
            'nome_completo'  => 'Voluntário Teste',
            'email'          => uniqid('vol_') . '@teste.com',
            'cidade_base_id' => $cidadeId,
            'status'         => User::STATUS_ATIVO,
        ]);

        return User::factory()->create([
            'voluntario_id' => $voluntario->id,
            'status'        => User::STATUS_ATIVO,
        ]);
    }

    private function criarHospital(Cidade $cidade, string $nome): Hospital
    {
        return Hospital::query()->create([
            'cidade_id' => $cidade->id,
            'nome'      => $nome,
            'cnpj'      => (string) random_int(10000000000000, 99999999999999),
            'endereco'  => 'Rua Teste, 1',
            'telefone'  => '51999999999',
            'email'     => uniqid('hospital_') . '@teste.com',
            'ativo'     => true,
        ]);
    }

    private function criarVisita(
        Hospital $hospital,
        User $user,
        string $inicioEm,
        VisitaStatus $status,
        ?Ala $ala = null,
    ): Visita {
        return Visita::query()->create([
            'hospital_id'    => $hospital->id,
            'ala_unidade_id' => $ala?->id,
            'criado_por_id'  => $user->id,
            'lider_id'       => $user->id,
            'inicio_em'      => $inicioEm,
            'fim_em'         => Carbon::parse($inicioEm)->addHours(2),
            'tipo'           => VisitaTipo::Hospital,
            'status'         => $status,
            'origem'         => VisitaOrigem::Sistema,
        ]);
    }
}
