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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ParticipanteStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_convidado_e_redirecionado_para_login(): void
    {
        $visita = $this->criarVisita();

        $this->post(route('visitas.participantes.store', $visita), [
            'tipo_participacao' => 'palhaco',
        ])->assertRedirect(route('login'));
    }

    public function test_inscreve_voluntario_com_sucesso(): void
    {
        $user   = $this->criarUsuario();
        $visita = $this->criarVisita();

        $this->participar($user, $visita)
            ->assertOk()
            ->assertJson(['sucesso' => true]);

        $this->assertDatabaseHas('visita_participante', [
            'visita_id'     => $visita->id,
            'voluntario_id' => $user->id,
        ]);
    }

    public function test_rejeita_quando_limite_atingido(): void
    {
        $visita = $this->criarVisita();
        $user   = $this->criarUsuario();

        $this->lotarVisita($visita);

        $this->participar($user, $visita)
            ->assertStatus(422)
            ->assertJsonPath('erros.0', 'Visita atingiu limite de participantes');
    }

    public function test_rejeita_usuario_ja_inscrito(): void
    {
        $user   = $this->criarUsuario();
        $visita = $this->criarVisita();

        $this->participar($user, $visita)->assertOk();

        $this->participar($user, $visita)
            ->assertStatus(422)
            ->assertJson(['sucesso' => false]);
    }

    public function test_rejeita_visita_cancelada(): void
    {
        $user   = $this->criarUsuario();
        $visita = $this->criarVisita(VisitaStatus::Cancelada);

        $this->participar($user, $visita)
            ->assertStatus(422)
            ->assertJson(['sucesso' => false]);
    }

    private function participar(User $user, Visita $visita, string $tipo = 'palhaco'): TestResponse
    {
        return $this->actingAs($user)->postJson(
            route('visitas.participantes.store', $visita),
            ['tipo_participacao' => $tipo],
        );
    }

    private function lotarVisita(Visita $visita): void
    {
        foreach (range(1, 5) as $_) {
            VisitaParticipante::create([
                'visita_id'           => $visita->id,
                'voluntario_id'       => User::factory()->create()->id,
                'tipo_participacao'   => TipoParticipacao::Palhaco->value,
                'papel_na_visita'     => PapelNaVisita::Participante->value,
                'status_participacao' => StatusParticipacao::Confirmado->value,
            ]);
        }
    }

    private function criarUsuario(): User
    {
        return User::factory()->create();
    }

    private function criarHospital(): Hospital
    {
        $estado = Estado::create(['nome' => 'RS', 'sigla' => 'RS']);
        $cidade = Cidade::forceCreate(['nome' => 'POA', 'estado_id' => $estado->id]);

        return Hospital::create([
            'cidade_id' => $cidade->id,
            'nome'      => 'Hospital Teste',
            'cnpj'      => '12345678000199',
            'endereco'  => 'Rua 1',
            'telefone'  => '51999999999',
            'email'     => 'a@b.com',
            'ativo'     => true,
        ]);
    }

    private function criarVisita(VisitaStatus $status = VisitaStatus::Agendada): Visita
    {
        $user     = $this->criarUsuario();
        $hospital = $this->criarHospital();

        return Visita::create([
            'hospital_id'   => $hospital->id,
            'criado_por_id' => $user->id,
            'inicio_em'     => now()->addDay(),
            'fim_em'        => now()->addDay()->addHours(2),
            'tipo'          => VisitaTipo::Hospital,
            'status'        => $status,
            'origem'        => VisitaOrigem::Sistema,
        ]);
    }
}
