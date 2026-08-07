<?php

namespace Tests\Feature\Visita;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Cargo;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaParticipante;
use App\Models\Voluntario;
use App\Services\Visita\Form\Service as FormService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitaEdicaoRegressaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_formulario_de_edicao_preserva_horario_local_da_visita(): void
    {
        $visita = new Visita([
            'inicio_em' => '2026-08-10 19:00:00',
            'fim_em'    => '2026-08-10 21:00:00',
            'tipo'      => VisitaTipo::Hospital,
            'status'    => VisitaStatus::Agendada,
            'origem'    => VisitaOrigem::Sistema,
        ]);

        $dados = app(FormService::class)->buscarDados($visita);

        $this->assertSame('2026-08-10 19:00:00', $dados['visita']['inicio_em']);
        $this->assertSame('2026-08-10 21:00:00', $dados['visita']['fim_em']);
    }

    public function test_remover_participante_retorna_lista_atualizada_para_tela(): void
    {
        $diretor = $this->criarDiretor();
        $visita = $this->criarVisita($diretor);
        $participante = $this->criarVoluntario();

        $participacao = VisitaParticipante::query()->create([
            'visita_id'           => $visita->id,
            'voluntario_id'       => $participante->id,
            'tipo_participacao'   => TipoParticipacao::Palhaco->value,
            'papel_na_visita'     => PapelNaVisita::Participante->value,
            'status_participacao' => StatusParticipacao::Confirmado->value,
        ]);

        $response = $this->actingAs($diretor)->deleteJson(
            route('visitas.participantes.destroy', [$visita, $participacao]),
        );

        $response
            ->assertOk()
            ->assertJsonPath('sucesso', true)
            ->assertJsonPath('dados.participantes.0.id', $participacao->id)
            ->assertJsonPath('dados.participantes.0.status_participacao', StatusParticipacao::Cancelado->value);
    }

    private function criarDiretor(): User
    {
        $cargo = Cargo::query()->firstOrCreate(
            ['slug' => 'diretor'],
            ['nome' => 'Diretor'],
        );

        $user = User::factory()->create();
        $user->cargos()->syncWithoutDetaching([$cargo->id]);

        return $user->fresh('cargos');
    }

    private function criarVoluntario(): User
    {
        $voluntario = Voluntario::query()->create([
            'nome_completo' => 'Voluntário ' . uniqid(),
            'email'         => uniqid('vol_') . '@test.com',
            'status'        => User::STATUS_ATIVO,
        ]);

        return User::factory()->create([
            'voluntario_id' => $voluntario->id,
            'status'        => User::STATUS_ATIVO,
        ]);
    }

    private function criarVisita(User $criador): Visita
    {
        $hospital = Hospital::query()->create([
            'nome'     => 'Hospital Teste ' . uniqid(),
            'cnpj'     => (string) random_int(10000000000000, 99999999999999),
            'endereco' => 'Rua 1',
            'telefone' => '51999999999',
            'email'    => uniqid('hospital_') . '@test.com',
            'ativo'    => true,
        ]);

        return Visita::query()->create([
            'hospital_id'   => $hospital->id,
            'criado_por_id' => $criador->id,
            'inicio_em'     => '2026-08-10 19:00:00',
            'fim_em'        => '2026-08-10 21:00:00',
            'tipo'          => VisitaTipo::Hospital,
            'status'        => VisitaStatus::Agendada,
            'origem'        => VisitaOrigem::Sistema,
        ]);
    }
}
