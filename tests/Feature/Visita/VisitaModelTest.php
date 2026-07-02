<?php

namespace Tests\Feature\Visita;

use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitaModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_visita_casta_enums_ao_ler_do_banco(): void
    {
        $visita = $this->criarVisita();

        $this->assertSame(VisitaTipo::Hospital, $visita->tipo);
        $this->assertSame(VisitaStatus::Agendada, $visita->status);
        $this->assertSame(VisitaOrigem::Sistema, $visita->origem);
    }

    public function test_visita_casta_inicio_e_fim_como_datetime(): void
    {
        $visita = $this->criarVisita();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $visita->inicio_em);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $visita->fim_em);
    }

    public function test_visita_pertence_a_hospital_criador_e_lider(): void
    {
        $hospital = $this->criarHospital();
        $criador = User::factory()->create();
        $lider = User::factory()->create();

        $visita = Visita::query()->create([
            'hospital_id' => $hospital->id,
            'criado_por_id' => $criador->id,
            'lider_id' => $lider->id,
            'inicio_em' => now()->addDay(),
            'fim_em' => now()->addDay()->addHours(2),
            'tipo' => VisitaTipo::Hospital,
            'status' => VisitaStatus::Agendada,
            'origem' => VisitaOrigem::Sistema,
        ]);

        $visita->load(['hospital', 'criadoPor', 'lider']);

        $this->assertTrue($visita->hospital->is($hospital));
        $this->assertTrue($visita->criadoPor->is($criador));
        $this->assertTrue($visita->lider->is($lider));
    }

    private function criarHospital(): Hospital
    {
        $estado = Estado::query()->create(['nome' => 'RS', 'sigla' => 'RS']);
        $cidade = Cidade::query()->forceCreate(['nome' => 'Porto Alegre', 'estado_id' => $estado->id]);

        return Hospital::query()->create([
            'cidade_id' => $cidade->id,
            'nome' => 'Hospital Teste',
            'cnpj' => '12345678000199',
            'endereco' => 'Rua Teste, 1',
            'telefone' => '51999999999',
            'email' => 'teste@hospital.com',
            'ativo' => true,
        ]);
    }

    private function criarVisita(): Visita
    {
        return Visita::query()->create([
            'hospital_id' => $this->criarHospital()->id,
            'criado_por_id' => User::factory()->create()->id,
            'inicio_em' => now()->addDay(),
            'fim_em' => now()->addDay()->addHours(2),
            'tipo' => VisitaTipo::Hospital,
            'status' => VisitaStatus::Agendada,
            'origem' => VisitaOrigem::Sistema,
        ]);
    }
}
