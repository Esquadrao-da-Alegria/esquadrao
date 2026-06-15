<?php

namespace Tests\Feature\Visita;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaParticipante;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitaParticipanteModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_participante_casta_enums_ao_ler_do_banco(): void
    {
        $participante = $this->criarParticipante();

        $this->assertSame(TipoParticipacao::Palhaco, $participante->tipo_participacao);
        $this->assertSame(PapelNaVisita::Participante, $participante->papel_na_visita);
        $this->assertSame(StatusParticipacao::Confirmado, $participante->status_participacao);
    }

    public function test_participante_pertence_a_visita_e_voluntario(): void
    {
        $participante = $this->criarParticipante();
        $participante->load(['visita', 'voluntario']);

        $this->assertInstanceOf(Visita::class, $participante->visita);
        $this->assertInstanceOf(User::class, $participante->voluntario);
    }

    public function test_nao_permite_mesmo_voluntario_duas_vezes_na_mesma_visita(): void
    {
        $visita = $this->criarVisita();
        $voluntario = User::factory()->create();

        VisitaParticipante::query()->create([
            'visita_id' => $visita->id,
            'voluntario_id' => $voluntario->id,
            'tipo_participacao' => TipoParticipacao::Palhaco,
            'papel_na_visita' => PapelNaVisita::Participante,
            'status_participacao' => StatusParticipacao::Confirmado,
        ]);

        $this->expectException(QueryException::class);

        VisitaParticipante::query()->create([
            'visita_id' => $visita->id,
            'voluntario_id' => $voluntario->id,
            'tipo_participacao' => TipoParticipacao::Paisana,
            'papel_na_visita' => PapelNaVisita::Participante,
            'status_participacao' => StatusParticipacao::Pendente,
        ]);
    }

    private function criarVisita(): Visita
    {
        $estado = Estado::query()->create(['nome' => 'RS', 'sigla' => 'RS']);
        $cidade = Cidade::query()->forceCreate(['nome' => 'POA', 'estado_id' => $estado->id]);
        $hospital = Hospital::query()->create([
            'cidade_id' => $cidade->id,
            'nome' => 'Hospital Teste',
            'cnpj' => '12345678000199',
            'endereco' => 'Rua 1',
            'telefone' => '51999999999',
            'email' => 'a@b.com',
            'ativo' => true,
        ]);

        return Visita::query()->create([
            'hospital_id' => $hospital->id,
            'criado_por_id' => User::factory()->create()->id,
            'inicio_em' => now()->addDay(),
            'fim_em' => now()->addDay()->addHours(2),
            'tipo' => VisitaTipo::Hospital,
            'status' => VisitaStatus::Agendada,
            'origem' => VisitaOrigem::Sistema,
        ]);
    }

    private function criarParticipante(): VisitaParticipante
    {
        return VisitaParticipante::query()->create([
            'visita_id' => $this->criarVisita()->id,
            'voluntario_id' => User::factory()->create()->id,
            'tipo_participacao' => TipoParticipacao::Palhaco,
            'papel_na_visita' => PapelNaVisita::Participante,
            'status_participacao' => StatusParticipacao::Confirmado,
        ]);
    }
}
