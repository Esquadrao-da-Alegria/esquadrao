<?php

namespace Tests\Feature\Visita\Relatorio;

use App\Enums\TipoRelatorio;
use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Ala;
use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaRelatorio;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_autor_atualiza_relatorio(): void
    {
        $autor     = $this->criarVoluntario();
        $visita    = $this->criarVisita($autor);
        $relatorio = $this->criarRelatorio($visita, $autor);

        $this->actingAs($autor)
            ->put(route('visitas.relatorios.update', [$visita, $relatorio]), $this->payloadRelatorio([
                'resumo' => 'Resumo atualizado pelo autor',
            ]))
            ->assertRedirect(route('visitas.relatorios.show', [$visita, $relatorio]));

        $this->assertDatabaseHas('visitas_relatorios', [
            'id'     => $relatorio->id,
            'resumo' => 'Resumo atualizado pelo autor',
        ]);
    }

    public function test_autor_atualiza_unidades_visitadas(): void
    {
        $autor     = $this->criarVoluntario();
        $visita    = $this->criarVisita($autor);
        $relatorio = $this->criarRelatorio($visita, $autor);

        $this->actingAs($autor)
            ->put(route('visitas.relatorios.update', [$visita, $relatorio]), $this->payloadRelatorio([
                'unidades_visitadas' => 'Pediatria e Oncologia',
            ]))
            ->assertRedirect(route('visitas.relatorios.show', [$visita, $relatorio]));

        $this->assertDatabaseHas('visitas_relatorios', [
            'id'                 => $relatorio->id,
            'unidades_visitadas' => 'Pediatria e Oncologia',
        ]);
    }

    public function test_usuario_comum_nao_atualiza_relatorio_de_outro(): void
    {
        $autor     = $this->criarVoluntario();
        $outro     = $this->criarUsuarioComCargo('artista');
        $visita    = $this->criarVisita($autor);
        $relatorio = $this->criarRelatorio($visita, $autor);

        $this->actingAs($outro)
            ->from(route('visitas.relatorios.edit', [$visita, $relatorio]))
            ->put(route('visitas.relatorios.update', [$visita, $relatorio]), $this->payloadRelatorio([
                'resumo' => 'Tentativa indevida',
            ]))
            ->assertRedirect(route('visitas.relatorios.edit', [$visita, $relatorio]))
            ->assertSessionHasErrors('geral');

        $this->assertDatabaseHas('visitas_relatorios', [
            'id'     => $relatorio->id,
            'resumo' => 'Relatório existente',
        ]);
    }

    public function test_gestor_pode_atualizar_relatorio_de_outro(): void
    {
        $autor     = $this->criarVoluntario();
        $diretor   = $this->criarUsuarioComCargo('diretor');
        $visita    = $this->criarVisita($autor);
        $relatorio = $this->criarRelatorio($visita, $autor);

        $this->actingAs($diretor)
            ->put(route('visitas.relatorios.update', [$visita, $relatorio]), $this->payloadRelatorio([
                'resumo' => 'Atualizado pelo diretor',
            ]))
            ->assertRedirect(route('visitas.relatorios.show', [$visita, $relatorio]));

        $this->assertDatabaseHas('visitas_relatorios', [
            'id'     => $relatorio->id,
            'resumo' => 'Atualizado pelo diretor',
        ]);
    }

    public function test_update_falha_em_visita_cancelada(): void
    {
        $autor     = $this->criarVoluntario();
        $visita    = $this->criarVisita($autor, status: VisitaStatus::Cancelada);
        $relatorio = $this->criarRelatorio($visita, $autor);

        $this->actingAs($autor)
            ->from(route('visitas.relatorios.edit', [$visita, $relatorio]))
            ->put(route('visitas.relatorios.update', [$visita, $relatorio]), $this->payloadRelatorio([
                'resumo' => 'Não deve salvar',
            ]))
            ->assertRedirect(route('visitas.relatorios.edit', [$visita, $relatorio]))
            ->assertSessionHasErrors('geral');

        $this->assertDatabaseHas('visitas_relatorios', [
            'id'     => $relatorio->id,
            'resumo' => 'Relatório existente',
        ]);
    }

    public function test_rejeita_relatorio_de_outra_visita_na_url(): void
    {
        $autor      = $this->criarVoluntario();
        $visitaA    = $this->criarVisita($autor);
        $visitaB    = $this->criarVisita($autor);
        $relatorioB = $this->criarRelatorio($visitaB, $autor);

        $this->actingAs($autor)
            ->get(route('visitas.relatorios.show', [$visitaA, $relatorioB]))
            ->assertNotFound();
    }

    public function test_rejeita_update_de_relatorio_de_outra_visita(): void
    {
        $autor      = $this->criarVoluntario();
        $visitaA    = $this->criarVisita($autor);
        $visitaB    = $this->criarVisita($autor);
        $relatorioB = $this->criarRelatorio($visitaB, $autor);

        $this->actingAs($autor)
            ->put(route('visitas.relatorios.update', [$visitaA, $relatorioB]), $this->payloadRelatorio())
            ->assertNotFound();
    }

    public function test_update_persiste_ala_unidade_id(): void
    {
        $autor     = $this->criarVoluntario();
        $visita    = $this->criarVisita($autor);
        $relatorio = $this->criarRelatorio($visita, $autor);
        $ala       = $this->criarAla($visita->hospital_id, 'Oncologia');

        $this->actingAs($autor)
            ->put(route('visitas.relatorios.update', [$visita, $relatorio]), $this->payloadRelatorio([
                'ala_unidade_id' => $ala->id,
            ]))
            ->assertRedirect(route('visitas.relatorios.show', [$visita, $relatorio]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('visitas_relatorios', [
            'id'             => $relatorio->id,
            'ala_unidade_id' => $ala->id,
        ]);
    }

    private function criarAla(int $hospitalId, string $nome): Ala
    {
        return Ala::query()->create([
            'hospital_id' => $hospitalId,
            'nome'        => $nome,
        ]);
    }

    private function payloadRelatorio(array $override = []): array
    {
        return array_merge([
            'tipo_relatorio' => TipoRelatorio::Geral->value,
            'resumo'         => 'Resumo do relatório de teste.',
        ], $override);
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

    private function criarVoluntario(): User
    {
        $cargo = Cargo::query()->firstOrCreate(
            ['slug' => 'voluntario'],
            ['nome' => 'Voluntário'],
        );

        $voluntario = Voluntario::query()->create([
            'nome_completo' => 'Voluntário ' . uniqid(),
            'email'         => uniqid('vol_') . '@test.com',
            'status'        => User::STATUS_ATIVO,
        ]);

        $user = User::factory()->create([
            'voluntario_id' => $voluntario->id,
            'status'        => User::STATUS_ATIVO,
        ]);
        $user->cargos()->syncWithoutDetaching([$cargo->id]);

        return $user->fresh('cargos');
    }

    private function criarUsuarioComCargo(string $slug): User
    {
        $cargo = Cargo::query()->firstOrCreate(
            ['slug' => $slug],
            ['nome' => ucfirst(str_replace('_', ' ', $slug))],
        );
        $user = User::factory()->create();
        $user->cargos()->syncWithoutDetaching([$cargo->id]);

        return $user->fresh('cargos');
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

    private function criarVisita(User $criador, VisitaStatus $status = VisitaStatus::Agendada): Visita
    {
        $hospital = $this->criarHospital();

        return Visita::query()->create([
            'hospital_id'   => $hospital->id,
            'criado_por_id' => $criador->id,
            'lider_id'      => $criador->id,
            'inicio_em'     => '2026-06-15 10:00:00',
            'fim_em'        => '2026-06-15 12:00:00',
            'tipo'          => VisitaTipo::Hospital,
            'status'        => $status,
            'origem'        => VisitaOrigem::Sistema,
        ]);
    }
}
