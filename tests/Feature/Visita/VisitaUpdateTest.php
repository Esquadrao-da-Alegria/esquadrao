<?php

namespace Tests\Feature\Visita;

use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use App\Services\Visita\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitaUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_lider_pode_editar(): void
    {
        $lider  = $this->criarVoluntario();
        $visita = $this->criarVisita($lider, liderId: $lider->id);

        $this->assertTrue(app(Service::class)->podeEditarVisita($lider, $visita));
    }

    public function test_artista_nao_pode_editar(): void
    {
        $lider   = $this->criarVoluntario();
        $artista = $this->criarUsuarioComCargo('artista');
        $visita  = $this->criarVisita($lider, liderId: $lider->id);

        $this->assertFalse(app(Service::class)->podeEditarVisita($artista, $visita));
    }

    public function test_diretor_pode_editar(): void
    {
        $lider   = $this->criarVoluntario();
        $diretor = $this->criarUsuarioComCargo('diretor');
        $visita  = $this->criarVisita($lider, liderId: $lider->id);

        $this->assertTrue(app(Service::class)->podeEditarVisita($diretor, $visita));
    }

    public function test_coordenador_geral_pode_editar(): void
    {
        $lider       = $this->criarVoluntario();
        $coordenador = $this->criarUsuarioComCargo('coordenador_geral');
        $visita      = $this->criarVisita($lider, liderId: $lider->id);

        $this->assertTrue(app(Service::class)->podeEditarVisita($coordenador, $visita));
    }

    public function test_visita_sem_lider_so_gestores_editam(): void
    {
        $criador = $this->criarVoluntario();
        $diretor = $this->criarUsuarioComCargo('diretor');
        $visita  = $this->criarVisita($criador, liderId: null);

        $this->assertFalse(app(Service::class)->podeEditarVisita($criador, $visita));
        $this->assertTrue(app(Service::class)->podeEditarVisita($diretor, $visita));
    }

    public function test_edit_redireciona_sem_permissao(): void
    {
        $lider   = $this->criarVoluntario();
        $artista = $this->criarUsuarioComCargo('artista');
        $visita  = $this->criarVisita($lider, liderId: $lider->id);

        $this->actingAs($artista)
            ->get(route('visitas.edit', $visita))
            ->assertRedirect(route('visitas.index'))
            ->assertSessionHas('mensagem_erro', 'Você não tem permissão para editar esta visita.');
    }

    public function test_update_redireciona_sem_permissao(): void
    {
        $lider   = $this->criarVoluntario();
        $artista = $this->criarUsuarioComCargo('artista');
        $visita  = $this->criarVisita($lider, liderId: $lider->id);

        $this->actingAs($artista)
            ->put(route('visitas.update', $visita), [
                'data'        => '2026-06-25',
                'hora_inicio' => '09:00',
                'hora_fim'    => '11:00',
                'tipo'        => VisitaTipo::Hospital->value,
                'lider_id'    => $lider->id,
                'status'      => VisitaStatus::Agendada->value,
            ])
            ->assertRedirect(route('visitas.index'))
            ->assertSessionHas('mensagem_erro', 'Você não tem permissão para editar esta visita.');
    }

    public function test_atualiza_visita_sem_alterar_hospital(): void
    {
        $lider              = $this->criarVoluntario();
        $visita             = $this->criarVisita($lider, liderId: $lider->id);
        $hospitalIdOriginal = $visita->hospital_id;

        $payload = [
            'data'        => '2026-06-25',
            'hora_inicio' => '09:00',
            'hora_fim'    => '11:00',
            'tipo'        => VisitaTipo::Oficina->value,
            'lider_id'    => $lider->id,
            'status'      => VisitaStatus::Realizada->value,
            'observacoes' => 'Atualizado',
            'hospital_id' => 99999,
        ];

        $this->actingAs($lider)
            ->put(route('visitas.update', $visita), $payload)
            ->assertRedirect(route('visitas.index'));

        $visita->refresh();

        $this->assertSame($hospitalIdOriginal, $visita->hospital_id);
        $this->assertSame(VisitaStatus::Realizada, $visita->status);
        $this->assertSame(VisitaTipo::Oficina, $visita->tipo);
    }

    private function criarVoluntario(): User
    {
        return $this->criarUsuarioComCargo('voluntario');
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

    private function criarVisita(User $criador, int|null|false $liderId = false): Visita
    {
        $hospital = $this->criarHospital();

        return Visita::query()->create([
            'hospital_id'   => $hospital->id,
            'criado_por_id' => $criador->id,
            'lider_id'      => $liderId === false ? $criador->id : $liderId,
            'inicio_em'     => '2026-06-15 10:00:00',
            'fim_em'        => '2026-06-15 12:00:00',
            'tipo'          => VisitaTipo::Hospital,
            'status'        => VisitaStatus::Agendada,
            'origem'        => VisitaOrigem::Sistema,
        ]);
    }
}
