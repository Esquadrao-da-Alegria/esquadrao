<?php

namespace Tests\Feature\Visita;

use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Ala;
use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitaStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_convidado_redireciona_login(): void
    {
        $this->post(route('visitas.store'), [])->assertRedirect(route('login'));
    }

    public function test_cria_visita_com_defaults(): void
    {
        $user     = $this->criarVoluntario();
        $hospital = $this->criarHospital();
        $lider    = $this->criarVoluntario();

        $payload = [
            'hospital_id'    => $hospital->id,
            'ala_unidade_id' => null,
            'data'           => '2026-06-20',
            'hora_inicio'    => '10:00',
            'hora_fim'       => '12:00',
            'tipo'           => VisitaTipo::Hospital->value,
            'lider_id'       => $lider->id,
            'observacoes'    => 'Teste',
        ];

        $this->actingAs($user)
            ->post(route('visitas.store'), $payload)
            ->assertRedirect(route('visitas.index'));

        $this->assertDatabaseHas('visitas', [
            'hospital_id'   => $hospital->id,
            'criado_por_id' => $user->id,
            'lider_id'      => $lider->id,
            'tipo'          => VisitaTipo::Hospital->value,
            'status'        => VisitaStatus::Agendada->value,
            'origem'        => VisitaOrigem::Sistema->value,
            'observacoes'   => 'Teste',
        ]);
    }

    public function test_rejeita_fim_antes_de_inicio(): void
    {
        $user     = $this->criarVoluntario();
        $hospital = $this->criarHospital();

        $payload = [
            'hospital_id' => $hospital->id,
            'data'        => '2026-06-20',
            'hora_inicio' => '14:00',
            'hora_fim'    => '10:00',
            'tipo'        => VisitaTipo::Hospital->value,
            'lider_id'    => $user->id,
        ];

        $this->actingAs($user)
            ->post(route('visitas.store'), $payload)
            ->assertSessionHasErrors('hora_fim');
    }

    public function test_rejeita_ala_de_outro_hospital(): void
    {
        $user     = $this->criarVoluntario();
        $hospital = $this->criarHospital();
        $outro    = $this->criarHospital();
        $alaOutro = Ala::query()->create(['hospital_id' => $outro->id, 'nome' => 'Ala X']);

        $payload = [
            'hospital_id'    => $hospital->id,
            'ala_unidade_id' => $alaOutro->id,
            'data'           => '2026-06-20',
            'hora_inicio'    => '10:00',
            'hora_fim'       => '12:00',
            'tipo'           => VisitaTipo::Hospital->value,
            'lider_id'       => $user->id,
        ];

        $this->actingAs($user)
            ->post(route('visitas.store'), $payload)
            ->assertSessionHasErrors('ala_unidade_id');
    }

    public function test_rejeita_hospital_inativo(): void
    {
        $user     = $this->criarVoluntario();
        $hospital = $this->criarHospital(ativo: false);

        $payload = [
            'hospital_id' => $hospital->id,
            'data'        => '2026-06-20',
            'hora_inicio' => '10:00',
            'hora_fim'    => '12:00',
            'tipo'        => VisitaTipo::Hospital->value,
            'lider_id'    => $user->id,
        ];

        $this->actingAs($user)
            ->post(route('visitas.store'), $payload)
            ->assertSessionHasErrors('hospital_id');
    }

    private function criarVoluntario(): User
    {
        $cargo = Cargo::query()->firstOrCreate(
            ['slug' => 'voluntario'],
            ['nome' => 'Voluntário'],
        );
        $user = User::factory()->create();
        $user->cargos()->syncWithoutDetaching([$cargo->id]);

        return $user;
    }

    private function criarHospital(bool $ativo = true): Hospital
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
            'ativo'     => $ativo,
        ]);
    }
}
