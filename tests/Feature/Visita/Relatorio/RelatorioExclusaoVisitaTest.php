<?php

namespace Tests\Feature\Visita\Relatorio;

use App\Enums\TipoRelatorio;
use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaRelatorio;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioExclusaoVisitaTest extends TestCase
{
    use RefreshDatabase;

    public function test_visita_com_relatorio_nao_pode_ser_excluida_fisicamente(): void
    {
        $autor     = User::factory()->create();
        $visita    = $this->criarVisita($autor);
        $relatorio = $this->criarRelatorio($visita, $autor);

        try {
            $visita->delete();
            $this->fail('Esperava QueryException ao excluir visita com relatório.');
        } catch (QueryException) {
            // FK restrictOnDelete
        }

        $this->assertDatabaseHas('visitas', [
            'id' => $visita->id,
        ]);

        $this->assertDatabaseHas('visitas_relatorios', [
            'id'        => $relatorio->id,
            'visita_id' => $visita->id,
        ]);
    }

    public function test_visita_sem_relatorio_pode_ser_excluida_fisicamente(): void
    {
        $criador = User::factory()->create();
        $visita  = $this->criarVisita($criador);
        $id      = $visita->id;

        $visita->delete();

        $this->assertDatabaseMissing('visitas', [
            'id' => $id,
        ]);
    }

    private function criarRelatorio(Visita $visita, User $autor): VisitaRelatorio
    {
        return VisitaRelatorio::query()->create([
            'visita_id'      => $visita->id,
            'autor_id'       => $autor->id,
            'tipo_relatorio' => TipoRelatorio::Geral,
            'resumo'         => 'Relatório existente',
            'enviado_em'     => now(),
            'fora_do_prazo'  => false,
        ]);
    }

    private function criarHospital(): Hospital
    {
        $estado = Estado::query()->firstOrCreate(
            ['sigla' => 'RS'],
            ['nome' => 'RS'],
        );
        $cidade = Cidade::query()
            ->where('nome', 'POA')
            ->where('estado_id', $estado->id)
            ->first()
            ?? Cidade::query()->forceCreate(['nome' => 'POA', 'estado_id' => $estado->id]);

        return Hospital::query()->create([
            'cidade_id' => $cidade->id,
            'nome'      => 'Hospital Teste ' . uniqid(),
            'cnpj'      => (string) random_int(10000000000000, 99999999999999),
            'endereco'  => 'Rua 1',
            'telefone'  => '51999999999',
            'email'     => 'a@b.com',
            'ativo'     => true,
        ]);
    }

    private function criarVisita(User $criador): Visita
    {
        $hospital = $this->criarHospital();

        return Visita::query()->create([
            'hospital_id'   => $hospital->id,
            'criado_por_id' => $criador->id,
            'lider_id'      => $criador->id,
            'inicio_em'     => '2026-06-15 10:00:00',
            'fim_em'        => '2026-06-15 12:00:00',
            'tipo'          => VisitaTipo::Hospital,
            'status'        => VisitaStatus::Agendada,
            'origem'        => VisitaOrigem::Sistema,
        ]);
    }
}
