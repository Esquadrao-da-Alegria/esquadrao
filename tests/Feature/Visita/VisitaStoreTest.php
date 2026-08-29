<?php

namespace Tests\Feature\Visita;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\AgendaLiberacaoCidade;
use App\Models\Ala;
use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Voluntario;
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

    public function test_permite_lider_ativo_com_voluntario_sem_cargo_voluntario(): void
    {
        $user     = $this->criarVoluntario();
        $hospital = $this->criarHospital();
        $lider    = $this->criarUsuarioVoluntarioAtivoComCargo('artista');

        $payload = [
            'hospital_id' => $hospital->id,
            'data'        => '2026-06-20',
            'hora_inicio' => '10:00',
            'hora_fim'    => '12:00',
            'tipo'        => VisitaTipo::Hospital->value,
            'lider_id'    => $lider->id,
        ];

        $this->actingAs($user)
            ->post(route('visitas.store'), $payload)
            ->assertRedirect(route('visitas.index'));
    }

    public function test_rejeita_lider_sem_voluntario_vinculado(): void
    {
        $user     = $this->criarVoluntario();
        $hospital = $this->criarHospital();
        $lider    = User::factory()->create(['status' => User::STATUS_ATIVO]);

        $payload = [
            'hospital_id' => $hospital->id,
            'data'        => '2026-06-20',
            'hora_inicio' => '10:00',
            'hora_fim'    => '12:00',
            'tipo'        => VisitaTipo::Hospital->value,
            'lider_id'    => $lider->id,
        ];

        $this->actingAs($user)
            ->post(route('visitas.store'), $payload)
            ->assertSessionHasErrors('lider_id');
    }

    public function test_cria_visita_com_lider_como_participante(): void
    {
        $user     = $this->criarVoluntario();
        $hospital = $this->criarHospital();
        $lider    = $this->criarVoluntario();

        $payload = [
            'hospital_id' => $hospital->id,
            'data'        => '2026-06-20',
            'hora_inicio' => '10:00',
            'hora_fim'    => '12:00',
            'tipo'        => VisitaTipo::Hospital->value,
            'lider_id'    => $lider->id,
        ];

        $this->actingAs($user)
            ->post(route('visitas.store'), $payload)
            ->assertRedirect(route('visitas.index'));

        $this->assertDatabaseHas('visita_participante', [
            'voluntario_id'       => $lider->id,
            'tipo_participacao'   => TipoParticipacao::Palhaco->value,
            'papel_na_visita'     => PapelNaVisita::Participante->value,
            'status_participacao' => StatusParticipacao::Confirmado->value,
        ]);
    }

    public function test_cria_acao_especial_sem_hospital_sucesso(): void
    {
        $user  = $this->criarVoluntario();
        $lider = $this->criarVoluntario();

        $payload = [
            'hospital_id'          => null,
            'ala_unidade_id'       => null,
            'data'                 => '2026-06-20',
            'hora_inicio'          => '10:00',
            'hora_fim'             => '12:00',
            'tipo'                 => VisitaTipo::AcaoEspecial->value,
            'limite_participantes' => 15,
            'lider_id'             => $lider->id,
            'observacoes'          => 'Ação Especial de Teste',
        ];

        $this->actingAs($user)
            ->post(route('visitas.store'), $payload)
            ->assertRedirect(route('visitas.index'));

        $this->assertDatabaseHas('visitas', [
            'hospital_id'          => null,
            'criado_por_id'        => $user->id,
            'lider_id'             => $lider->id,
            'tipo'                 => VisitaTipo::AcaoEspecial->value,
            'limite_participantes' => 15,
            'status'               => VisitaStatus::Agendada->value,
        ]);
    }

    public function test_cria_visita_hospitalar_sem_hospital_falha(): void
    {
        $user  = $this->criarVoluntario();
        $lider = $this->criarVoluntario();

        $payload = [
            'hospital_id' => null,
            'data'        => '2026-06-20',
            'hora_inicio' => '10:00',
            'hora_fim'    => '12:00',
            'tipo'        => VisitaTipo::Hospital->value,
            'lider_id'    => $lider->id,
        ];

        $this->actingAs($user)
            ->post(route('visitas.store'), $payload)
            ->assertSessionHasErrors('hospital_id');
    }

    public function test_cria_visita_residencia_sem_hospital_falha(): void
    {
        $user  = $this->criarVoluntario();
        $lider = $this->criarVoluntario();

        $payload = [
            'hospital_id' => null,
            'data'        => '2026-06-20',
            'hora_inicio' => '10:00',
            'hora_fim'    => '12:00',
            'tipo'        => VisitaTipo::Residencia->value,
            'lider_id'    => $lider->id,
        ];

        $this->actingAs($user)
            ->post(route('visitas.store'), $payload)
            ->assertSessionHasErrors('hospital_id');
    }

    public function test_cria_visita_com_ala_sem_hospital_falha(): void
    {
        $user     = $this->criarVoluntario();
        $hospital = $this->criarHospital();
        $ala      = Ala::query()->create(['hospital_id' => $hospital->id, 'nome' => 'Ala Teste']);

        $payload = [
            'hospital_id'    => null,
            'ala_unidade_id' => $ala->id,
            'data'           => '2026-06-20',
            'hora_inicio'    => '10:00',
            'hora_fim'       => '12:00',
            'tipo'           => VisitaTipo::AcaoEspecial->value,
            'lider_id'       => $user->id,
        ];

        $this->actingAs($user)
            ->post(route('visitas.store'), $payload)
            ->assertSessionHasErrors('ala_unidade_id');
    }

    private function criarVoluntario(): User
    {
        return $this->criarUsuarioVoluntarioAtivoComCargo('voluntario');
    }

    private function criarUsuarioVoluntarioAtivoComCargo(string $slug): User
    {
        $cargo = Cargo::query()->firstOrCreate(
            ['slug' => $slug],
            ['nome' => ucfirst(str_replace('_', ' ', $slug))],
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

        $hospital = Hospital::query()->create([
            'cidade_id' => $cidade->id,
            'nome'      => 'Hospital Teste ' . uniqid(),
            'cnpj'      => (string) random_int(10000000000000, 99999999999999),
            'endereco'  => 'Rua 1',
            'telefone'  => '51999999999',
            'email'     => 'a@b.com',
            'ativo'     => $ativo,
        ]);

        $this->liberarAgenda($cidade->id, 2026, 6);

        return $hospital;
    }

    private function liberarAgenda(int $cidadeId, int $ano, int $mes): void
    {
        AgendaLiberacaoCidade::query()->updateOrCreate(
            [
                'cidade_id' => $cidadeId,
                'ano'       => $ano,
                'mes'       => $mes,
            ],
            [
                'liberado'        => true,
                'liberado_por_id' => null,
            ],
        );
    }
}
