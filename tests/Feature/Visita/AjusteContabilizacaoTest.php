<?php

namespace Tests\Feature\Visita;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
use App\Enums\TipoRelatorio;
use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaParticipante;
use App\Models\VisitaRelatorio;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AjusteContabilizacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_somente_admin_ajusta_participacao_em_visita_realizada(): void
    {
        [$admin, $voluntario, $visita] = $this->cenario();
        $dados = ['tipo' => 'correcao_participacao', 'voluntario_id' => $voluntario->id, 'tipo_participacao' => 'palhaco', 'justificativa' => 'Inscrição ausente por falha operacional confirmada.'];

        $this->actingAs($voluntario)->post(route('visitas.ajustes-contabilizacao.store', $visita), $dados)->assertForbidden();
        $this->actingAs($admin)->post(route('visitas.ajustes-contabilizacao.store', $visita), $dados)->assertRedirect();

        $this->assertDatabaseHas('visita_participante', ['visita_id' => $visita->id, 'voluntario_id' => $voluntario->id, 'status_participacao' => 'confirmado']);
        $this->assertDatabaseHas('visitas_ajustes_contabilizacao', ['visita_id' => $visita->id, 'voluntario_id' => $voluntario->id, 'administrador_id' => $admin->id]);
    }

    public function test_aceite_preserva_relatorio_atrasado_e_bloqueia_autoajuste(): void
    {
        [$admin, $voluntario, $visita] = $this->cenario();
        VisitaParticipante::query()->create(['visita_id' => $visita->id, 'voluntario_id' => $voluntario->id, 'tipo_participacao' => TipoParticipacao::Paisana, 'papel_na_visita' => PapelNaVisita::Participante, 'status_participacao' => StatusParticipacao::Confirmado]);
        $relatorio = VisitaRelatorio::query()->create(['visita_id' => $visita->id, 'autor_id' => $voluntario->id, 'tipo_relatorio' => TipoRelatorio::Geral, 'resumo' => 'Relatório', 'enviado_em' => now(), 'fora_do_prazo' => true]);

        $this->actingAs($admin)->post(route('visitas.ajustes-contabilizacao.store', $visita), ['tipo' => 'aceite_relatorio_fora_prazo', 'relatorio_id' => $relatorio->id, 'justificativa' => 'Prazo excepcional autorizado após análise administrativa.'])->assertRedirect();

        $this->assertTrue($relatorio->fresh()->fora_do_prazo);
        $this->assertDatabaseHas('visitas_ajustes_contabilizacao', ['relatorio_id' => $relatorio->id, 'voluntario_id' => $voluntario->id]);

        $this->actingAs($admin)->post(route('visitas.ajustes-contabilizacao.store', $visita), ['tipo' => 'correcao_participacao', 'voluntario_id' => $admin->id, 'tipo_participacao' => 'palhaco', 'justificativa' => 'Tentativa de ajuste em benefício do administrador.'])->assertSessionHasErrors('voluntario_id');
    }

    private function cenario(): array
    {
        $estado = Estado::query()->create(['nome' => 'Rio Grande do Sul', 'sigla' => 'RS']);
        $cidade = Cidade::query()->forceCreate(['nome' => 'Porto Alegre', 'estado_id' => $estado->id]);
        $hospital = Hospital::query()->create(['cidade_id' => $cidade->id, 'nome' => 'Hospital', 'cnpj' => '12345678000199', 'endereco' => 'Rua 1', 'telefone' => '51999999999', 'email' => 'hospital@teste.com', 'ativo' => true]);
        $admin = $this->usuario($cidade, true);
        $voluntario = $this->usuario($cidade);
        $visita = Visita::query()->create(['hospital_id' => $hospital->id, 'criado_por_id' => $admin->id, 'inicio_em' => now()->subDays(3), 'fim_em' => now()->subDays(3)->addHours(2), 'tipo' => VisitaTipo::Hospital, 'status' => VisitaStatus::Realizada, 'origem' => VisitaOrigem::Sistema]);

        return [$admin, $voluntario, $visita];
    }

    private function usuario(Cidade $cidade, bool $admin = false): User
    {
        $voluntario = Voluntario::query()->create(['nome_completo' => uniqid('Voluntário '), 'email' => uniqid().'@teste.com', 'cidade_base_id' => $cidade->id, 'status' => 'ativo']);
        $user = User::factory()->create(['voluntario_id' => $voluntario->id]);
        if ($admin) {
            $cargo = Cargo::query()->create(['nome' => 'Administrador', 'slug' => 'administrador']);
            $user->cargos()->attach($cargo);
        }

        return $user;
    }
}
