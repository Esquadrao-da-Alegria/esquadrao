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
use App\Models\Visita;
use App\Models\VisitaParticipante;
use App\Models\Voluntario;
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

    public function test_administrador_pode_editar(): void
    {
        $lider         = $this->criarVoluntario();
        $administrador = $this->criarUsuarioComCargo('administrador');
        $visita        = $this->criarVisita($lider, liderId: $lider->id);

        $this->assertTrue(app(Service::class)->podeEditarVisita($administrador, $visita));
    }

    public function test_voluntario_comum_nao_pode_editar(): void
    {
        $lider      = $this->criarVoluntario();
        $voluntario = $this->criarVoluntario();
        $visita     = $this->criarVisita($lider, liderId: $lider->id);

        $this->assertFalse(app(Service::class)->podeEditarVisita($voluntario, $visita));
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

    public function test_coordenador_local_nao_edita_visita_de_outra_cidade(): void
    {
        $cidadeA       = $this->criarCidade('Cidade A');
        $cidadeB       = $this->criarCidade('Cidade B');
        $coordenador   = $this->criarUsuarioComCargoCidade('coordenador_local', $cidadeA->id);
        $lider         = $this->criarVoluntario();
        $visita        = $this->criarVisita($lider, liderId: $lider->id, cidadeId: $cidadeB->id);

        $this->assertFalse(app(Service::class)->podeEditarVisita($coordenador, $visita));
    }

    public function test_coordenador_local_edita_visita_da_sua_cidade(): void
    {
        $cidade        = $this->criarCidade('Cidade A');
        $coordenador   = $this->criarUsuarioComCargoCidade('coordenador_local', $cidade->id);
        $lider         = $this->criarVoluntario();
        $visita        = $this->criarVisita($lider, liderId: $lider->id, cidadeId: $cidade->id);

        $this->assertTrue(app(Service::class)->podeEditarVisita($coordenador, $visita));
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

    public function test_altera_hospital_e_ala_de_visita_agendada(): void
    {
        $lider        = $this->criarVoluntario();
        $novoLider    = $this->criarVoluntario();
        $visita       = $this->criarVisita($lider, liderId: $lider->id);
        $novoHospital = $this->criarHospital();
        $novaAla      = Ala::query()->create([
            'hospital_id' => $novoHospital->id,
            'nome'        => 'Ala do novo hospital',
        ]);

        $payload = [
            ...$this->payloadAtualizacao($novoLider),
            'data'                  => '2026-06-26',
            'hora_inicio'           => '14:00',
            'hora_fim'              => '17:00',
            'tipo'                  => VisitaTipo::Residencia->value,
            'hospital_id'           => $novoHospital->id,
            'ala_unidade_id'        => $novaAla->id,
            'limite_participantes'  => 8,
            'observacoes'           => 'Visita reorganizada',
        ];

        $this->actingAs($lider)
            ->put(route('visitas.update', $visita), $payload)
            ->assertRedirect(route('visitas.index'));

        $visita->refresh();

        $this->assertSame($novoHospital->id, $visita->hospital_id);
        $this->assertSame($novaAla->id, $visita->ala_unidade_id);
        $this->assertSame($novoLider->id, $visita->lider_id);
        $this->assertSame(VisitaTipo::Residencia, $visita->tipo);
        $this->assertSame('2026-06-26 14:00:00', $visita->inicio_em->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-26 17:00:00', $visita->fim_em->format('Y-m-d H:i:s'));
        $this->assertSame(8, $visita->limite_participantes);
        $this->assertSame('Visita reorganizada', $visita->observacoes);
    }

    public function test_altera_ala_de_visita_agendada(): void
    {
        $lider = $this->criarVoluntario();
        $visita = $this->criarVisita($lider, liderId: $lider->id);
        $ala = Ala::query()->create([
            'hospital_id' => $visita->hospital_id,
            'nome'        => 'Ala prioritária',
        ]);

        $payload = [
            ...$this->payloadAtualizacao($lider),
            'hospital_id'    => $visita->hospital_id,
            'ala_unidade_id' => $ala->id,
        ];

        $this->actingAs($lider)
            ->put(route('visitas.update', $visita), $payload)
            ->assertRedirect(route('visitas.index'));

        $this->assertSame($ala->id, $visita->fresh()->ala_unidade_id);
    }

    public function test_visita_hospitalar_agendada_continua_exigindo_hospital(): void
    {
        $lider = $this->criarVoluntario();
        $visita = $this->criarVisita($lider, liderId: $lider->id);

        $payload = [
            ...$this->payloadAtualizacao($lider),
            'hospital_id' => null,
        ];

        $this->actingAs($lider)
            ->from(route('visitas.edit', $visita))
            ->put(route('visitas.update', $visita), $payload)
            ->assertRedirect(route('visitas.edit', $visita))
            ->assertSessionHasErrors('hospital_id');

        $this->assertNotNull($visita->fresh()->hospital_id);
    }

    public function test_nao_altera_ala_de_visita_que_nao_esta_agendada(): void
    {
        $lider = $this->criarVoluntario();
        $visita = $this->criarVisita($lider, liderId: $lider->id);
        $alaOriginal = Ala::query()->create([
            'hospital_id' => $visita->hospital_id,
            'nome'        => 'Ala original',
        ]);
        $novaAla = Ala::query()->create([
            'hospital_id' => $visita->hospital_id,
            'nome'        => 'Nova ala',
        ]);
        $visita->update([
            'ala_unidade_id' => $alaOriginal->id,
            'status'         => VisitaStatus::Realizada->value,
        ]);

        $payload = [
            ...$this->payloadAtualizacao($lider),
            'hospital_id'    => $visita->hospital_id,
            'ala_unidade_id' => $novaAla->id,
            'status'         => VisitaStatus::Realizada->value,
        ];

        $this->actingAs($lider)
            ->from(route('visitas.edit', $visita))
            ->put(route('visitas.update', $visita), $payload)
            ->assertRedirect(route('visitas.edit', $visita))
            ->assertSessionHasErrors('geral');

        $this->assertSame($alaOriginal->id, $visita->fresh()->ala_unidade_id);
    }

    public function test_nao_altera_hospital_de_visita_que_nao_esta_agendada(): void
    {
        $lider = $this->criarVoluntario();
        $visita = $this->criarVisita($lider, liderId: $lider->id);
        $outroHospital = $this->criarHospital();
        $visita->update(['status' => VisitaStatus::Realizada->value]);

        $payload = [
            ...$this->payloadAtualizacao($lider),
            'hospital_id' => $outroHospital->id,
            'status'      => VisitaStatus::Realizada->value,
        ];

        $this->actingAs($lider)
            ->from(route('visitas.edit', $visita))
            ->put(route('visitas.update', $visita), $payload)
            ->assertRedirect(route('visitas.edit', $visita))
            ->assertSessionHasErrors('geral');

        $this->assertNotSame($outroHospital->id, $visita->fresh()->hospital_id);
    }

    public function test_valida_agenda_do_destino_ao_trocar_hospital(): void
    {
        $lider = $this->criarVoluntario();
        $visita = $this->criarVisita($lider, liderId: $lider->id);
        $outroHospital = $this->criarHospital();

        AgendaLiberacaoCidade::query()
            ->where('cidade_id', $outroHospital->cidade_id)
            ->where('ano', 2026)
            ->where('mes', 6)
            ->update(['liberado' => false]);

        $payload = [
            ...$this->payloadAtualizacao($lider),
            'hospital_id' => $outroHospital->id,
        ];

        $this->actingAs($lider)
            ->from(route('visitas.edit', $visita))
            ->put(route('visitas.update', $visita), $payload)
            ->assertRedirect(route('visitas.edit', $visita))
            ->assertSessionHasErrors('geral');

        $this->assertNotSame($outroHospital->id, $visita->fresh()->hospital_id);
    }

    public function test_coordenador_local_nao_transfere_visita_para_outra_cidade(): void
    {
        $cidadeA = $this->criarCidade('Cidade A');
        $cidadeB = $this->criarCidade('Cidade B');
        $coordenador = $this->criarUsuarioComCargoCidade('coordenador_local', $cidadeA->id);
        $lider = $this->criarVoluntario();
        $visita = $this->criarVisita($lider, liderId: $lider->id, cidadeId: $cidadeA->id);
        $outroHospital = $this->criarHospital(cidadeId: $cidadeB->id);

        $payload = [
            ...$this->payloadAtualizacao($lider),
            'hospital_id' => $outroHospital->id,
        ];

        $this->actingAs($coordenador)
            ->from(route('visitas.edit', $visita))
            ->put(route('visitas.update', $visita), $payload)
            ->assertRedirect(route('visitas.edit', $visita))
            ->assertSessionHasErrors('hospital_id');

        $this->assertNotSame($outroHospital->id, $visita->fresh()->hospital_id);
    }

    public function test_troca_lider_e_preserva_participacao_do_lider_anterior(): void
    {
        $liderAnterior = $this->criarVoluntario();
        $novoLider     = $this->criarVoluntario();
        $visita        = $this->criarVisita($liderAnterior, liderId: $liderAnterior->id);

        $this->inscreverParticipante($visita, $liderAnterior);

        $this->actingAs($liderAnterior)
            ->put(route('visitas.update', $visita), $this->payloadAtualizacao($novoLider))
            ->assertRedirect(route('visitas.index'));

        $this->assertDatabaseHas('visita_participante', [
            'visita_id'           => $visita->id,
            'voluntario_id'       => $liderAnterior->id,
            'status_participacao' => StatusParticipacao::Confirmado->value,
        ]);
        $this->assertDatabaseHas('visita_participante', [
            'visita_id'           => $visita->id,
            'voluntario_id'       => $novoLider->id,
            'status_participacao' => StatusParticipacao::Confirmado->value,
        ]);
        $this->assertSame($novoLider->id, $visita->fresh()->lider_id);
    }

    public function test_troca_lider_reativa_participacao_cancelada(): void
    {
        $liderAnterior = $this->criarVoluntario();
        $novoLider     = $this->criarVoluntario();
        $visita        = $this->criarVisita($liderAnterior, liderId: $liderAnterior->id);

        $this->inscreverParticipante($visita, $novoLider, StatusParticipacao::Cancelado);

        $this->actingAs($liderAnterior)
            ->put(route('visitas.update', $visita), $this->payloadAtualizacao($novoLider))
            ->assertRedirect(route('visitas.index'));

        $this->assertDatabaseCount('visita_participante', 1);
        $this->assertDatabaseHas('visita_participante', [
            'visita_id'           => $visita->id,
            'voluntario_id'       => $novoLider->id,
            'status_participacao' => StatusParticipacao::Confirmado->value,
        ]);
    }

    public function test_novo_lider_pode_exceder_limite_de_participantes(): void
    {
        $liderAnterior = $this->criarVoluntario();
        $novoLider     = $this->criarVoluntario();
        $visita        = $this->criarVisita($liderAnterior, liderId: $liderAnterior->id);

        foreach (range(1, 5) as $_) {
            $this->inscreverParticipante($visita, User::factory()->create());
        }

        $this->actingAs($liderAnterior)
            ->put(route('visitas.update', $visita), $this->payloadAtualizacao($novoLider))
            ->assertRedirect(route('visitas.index'));

        $this->assertSame(6, VisitaParticipante::query()
            ->where('visita_id', $visita->id)
            ->where('status_participacao', StatusParticipacao::Confirmado->value)
            ->count());
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

    private function payloadAtualizacao(User $lider): array
    {
        return [
            'data'        => '2026-06-25',
            'hora_inicio' => '09:00',
            'hora_fim'    => '11:00',
            'tipo'        => VisitaTipo::Hospital->value,
            'lider_id'    => $lider->id,
            'status'      => VisitaStatus::Agendada->value,
        ];
    }

    private function inscreverParticipante(
        Visita $visita,
        User $user,
        StatusParticipacao $status = StatusParticipacao::Confirmado,
    ): VisitaParticipante {
        return VisitaParticipante::query()->create([
            'visita_id'           => $visita->id,
            'voluntario_id'       => $user->id,
            'tipo_participacao'   => TipoParticipacao::Palhaco->value,
            'papel_na_visita'     => PapelNaVisita::Participante->value,
            'status_participacao' => $status->value,
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

    private function criarCidade(string $nome): Cidade
    {
        $estado = Estado::query()->firstOrCreate(
            ['sigla' => 'RS'],
            ['nome' => 'RS'],
        );

        $existente = Cidade::query()
            ->where('nome', $nome)
            ->where('estado_id', $estado->id)
            ->first();

        if ($existente) {
            return $existente;
        }

        return Cidade::query()->forceCreate([
            'nome'      => $nome,
            'estado_id' => $estado->id,
        ]);
    }

    private function criarUsuarioComCargoCidade(string $slug, int $cidadeId): User
    {
        $cargo = Cargo::query()->firstOrCreate(
            ['slug' => $slug],
            ['nome' => ucfirst(str_replace('_', ' ', $slug))],
        );

        $voluntario = Voluntario::query()->create([
            'nome_completo'  => 'Voluntário ' . uniqid(),
            'email'          => uniqid('vol_') . '@test.com',
            'status'         => User::STATUS_ATIVO,
            'cidade_base_id' => $cidadeId,
        ]);

        $user = User::factory()->create([
            'voluntario_id' => $voluntario->id,
            'status'        => User::STATUS_ATIVO,
        ]);
        $user->cargos()->syncWithoutDetaching([$cargo->id]);

        return $user->fresh(['cargos', 'voluntario']);
    }

    private function criarHospital(bool $ativo = true, ?int $cidadeId = null): Hospital
    {
        $estado = Estado::query()->firstOrCreate(
            ['sigla' => 'RS'],
            ['nome' => 'RS'],
        );
        $cidade = $cidadeId
            ? Cidade::query()->findOrFail($cidadeId)
            : (
                Cidade::query()
                    ->where('nome', 'POA')
                    ->where('estado_id', $estado->id)
                    ->first()
                ?? Cidade::query()->forceCreate(['nome' => 'POA', 'estado_id' => $estado->id])
            );

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

    private function criarVisita(User $criador, int|null|false $liderId = false, ?int $cidadeId = null): Visita
    {
        $hospital = $this->criarHospital(cidadeId: $cidadeId);

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
