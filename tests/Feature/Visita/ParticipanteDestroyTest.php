<?php

namespace Tests\Feature\Visita;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
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
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ParticipanteDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_participante_pode_auto_cancelar_inscricao(): void
    {
        $user         = $this->criarUsuarioVoluntarioAtivo();
        $visita       = $this->criarVisita();
        $participante = $this->inscreverParticipante($visita, $user);

        $this->cancelar($user, $visita, $participante)
            ->assertOk()
            ->assertJson(['sucesso' => true]);

        $this->assertDatabaseHas('visita_participante', [
            'id'                  => $participante->id,
            'status_participacao' => StatusParticipacao::Cancelado->value,
        ]);
    }

    public function test_lider_nao_pode_auto_cancelar_sem_trocar_lider(): void
    {
        $lider        = $this->criarUsuarioVoluntarioAtivo();
        $visita       = $this->criarVisita(liderId: $lider->id);
        $participante = $this->inscreverParticipante($visita, $lider);

        $this->cancelar($lider, $visita, $participante)
            ->assertStatus(422)
            ->assertJsonPath('erros.0', 'Altere o líder da visita antes de cancelar sua inscrição.');
    }

    public function test_gestor_pode_cancelar_participante(): void
    {
        $participanteUser = $this->criarUsuarioVoluntarioAtivo();
        $diretor          = $this->criarUsuarioComCargo('diretor');
        $visita           = $this->criarVisita();
        $participante     = $this->inscreverParticipante($visita, $participanteUser);

        $this->cancelar($diretor, $visita, $participante)
            ->assertOk()
            ->assertJson(['sucesso' => true]);

        $this->assertDatabaseHas('visita_participante', [
            'id'                  => $participante->id,
            'status_participacao' => StatusParticipacao::Cancelado->value,
        ]);
    }

    public function test_nao_permite_cancelar_participante_de_outra_visita(): void
    {
        $user              = $this->criarUsuarioVoluntarioAtivo();
        $visita            = $this->criarVisita();
        $outraVisita       = $this->criarVisita();
        $participante      = $this->inscreverParticipante($visita, $user);

        $this->cancelar($user, $outraVisita, $participante)
            ->assertStatus(422)
            ->assertJson(['sucesso' => false]);
    }

    private function cancelar(User $user, Visita $visita, VisitaParticipante $participante): TestResponse
    {
        return $this->actingAs($user)->deleteJson(
            route('visitas.participantes.destroy', [$visita, $participante]),
        );
    }

    private function inscreverParticipante(Visita $visita, User $user): VisitaParticipante
    {
        return VisitaParticipante::query()->create([
            'visita_id'           => $visita->id,
            'voluntario_id'       => $user->id,
            'tipo_participacao'   => TipoParticipacao::Palhaco->value,
            'papel_na_visita'     => PapelNaVisita::Participante->value,
            'status_participacao' => StatusParticipacao::Confirmado->value,
        ]);
    }

    private function criarUsuarioVoluntarioAtivo(): User
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

        return Hospital::create([
            'cidade_id' => $cidade->id,
            'nome'      => 'Hospital Teste ' . uniqid(),
            'cnpj'      => (string) random_int(10000000000000, 99999999999999),
            'endereco'  => 'Rua 1',
            'telefone'  => '51999999999',
            'email'     => 'a@b.com',
            'ativo'     => true,
        ]);
    }

    private function criarVisita(?int $liderId = null): Visita
    {
        $user     = $this->criarUsuarioVoluntarioAtivo();
        $hospital = $this->criarHospital();

        return Visita::create([
            'hospital_id'   => $hospital->id,
            'criado_por_id' => $user->id,
            'lider_id'      => $liderId,
            'inicio_em'     => now()->addDay(),
            'fim_em'        => now()->addDay()->addHours(2),
            'tipo'          => VisitaTipo::Hospital,
            'status'        => VisitaStatus::Agendada,
            'origem'        => VisitaOrigem::Sistema,
        ]);
    }
}
