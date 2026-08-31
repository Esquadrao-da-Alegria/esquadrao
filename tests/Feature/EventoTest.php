<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Evento;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EventoTest extends TestCase
{
    use RefreshDatabase;

    private ?int $cidadeId = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function criarUsuario(): User
    {
        return User::factory()->createOne();
    }

    private function usuarioAdmin(): User
    {
        $user  = $this->criarUsuario();
        $cargo = Cargo::create(['nome' => 'Administrador', 'slug' => 'administrador']);
        $user->cargos()->attach($cargo);

        return $user;
    }

    private function criarCidade(): Cidade
    {
        $estado = Estado::create(['nome' => 'São Paulo', 'sigla' => 'SP']);
        $cidade = new Cidade();
        $cidade->nome = 'Campinas';
        $cidade->estado_id = $estado->id;
        $cidade->save();
        return $cidade;
    }

    private function dadosEvento(array $overrides = []): array
    {
        if ($this->cidadeId === null) {
            $this->cidadeId = $this->criarCidade()->id;
        }

        return array_merge([
            'titulo'               => 'Oficina de alegria',
            'tipo'                 => 'oficina',
            'descricao'            => 'Descrição',
            'local'                => 'Sede',
            'cidade_id'            => $this->cidadeId,
            'data_inicio'          => now()->addDay()->format('Y-m-d H:i:s'),
            'data_fim'             => now()->addDays(2)->format('Y-m-d H:i:s'),
            'limite_participantes' => 10,
        ], $overrides);
    }

    // ─── CRUD básico ──────────────────────────────────────────────────────────

    public function test_usuario_autenticado_consegue_listar_eventos(): void
    {
        $user = $this->criarUsuario();
        Evento::create([...$this->dadosEvento(), 'criado_por_id' => $user->id]);

        $this->actingAs($user)->get(route('eventos.index'))->assertOk();
    }

    public function test_admin_consegue_criar_evento(): void
    {
        $admin = $this->usuarioAdmin();

        $this->actingAs($admin)->post(route('eventos.store'), $this->dadosEvento())
            ->assertRedirect(route('eventos.index'));

        $this->assertDatabaseHas('eventos', [
            'titulo'        => 'Oficina de alegria',
            'status'        => 'agendado',
            'criado_por_id' => $admin->id,
        ]);
    }

    public function test_admin_consegue_editar_evento(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $admin->id]);

        $this->actingAs($admin)
            ->put(route('eventos.update', $evento), $this->dadosEvento(['titulo' => 'Reunião atualizada']))
            ->assertRedirect(route('eventos.show', $evento));

        $this->assertDatabaseHas('eventos', ['id' => $evento->id, 'titulo' => 'Reunião atualizada']);
    }

    public function test_admin_consegue_cancelar_evento(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $admin->id]);

        $this->actingAs($admin)->post(route('eventos.cancelar', $evento), ['motivo_cancelamento' => 'Sem quórum'])
            ->assertRedirect(route('eventos.show', $evento));

        $this->assertDatabaseHas('eventos', ['id' => $evento->id, 'status' => 'cancelado', 'motivo_cancelamento' => 'Sem quórum']);
    }

    public function test_usuario_autenticado_consegue_se_inscrever(): void
    {
        $user   = $this->criarUsuario();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $user->id]);

        $this->actingAs($user)->post(route('eventos.inscricao.store', $evento))->assertRedirect();

        $this->assertDatabaseHas('evento_participantes', ['evento_id' => $evento->id, 'user_id' => $user->id, 'status' => 'inscrito']);
    }

    public function test_usuario_nao_consegue_se_inscrever_duas_vezes_ativamente(): void
    {
        $user   = $this->criarUsuario();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $user->id]);

        $this->actingAs($user)->post(route('eventos.inscricao.store', $evento));
        $this->actingAs($user)->post(route('eventos.inscricao.store', $evento));

        $this->assertSame(1, DB::table('evento_participantes')->where('evento_id', $evento->id)->where('user_id', $user->id)->count());
    }

    public function test_usuario_nao_consegue_se_inscrever_em_evento_cancelado(): void
    {
        $user   = $this->criarUsuario();
        $evento = Evento::create([...$this->dadosEvento(['status' => 'cancelado']), 'criado_por_id' => $user->id]);

        $this->actingAs($user)->post(route('eventos.inscricao.store', $evento))->assertSessionHas('mensagem_erro', 'Este evento foi cancelado.');
    }

    public function test_usuario_consegue_cancelar_sua_inscricao(): void
    {
        $user   = $this->criarUsuario();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $user->id]);
        $evento->participantes()->attach($user->id, ['status' => 'inscrito', 'inscrito_em' => now()]);

        $this->actingAs($user)->delete(route('eventos.inscricao.destroy', $evento))->assertRedirect();

        $this->assertDatabaseHas('evento_participantes', ['evento_id' => $evento->id, 'user_id' => $user->id, 'status' => 'cancelado']);
    }

    public function test_usuario_comum_nao_consegue_acessar_criacao_edicao_e_cancelamento(): void
    {
        $user   = $this->criarUsuario();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $user->id]);

        $this->actingAs($user)->get(route('eventos.create'))->assertForbidden();
        $this->actingAs($user)->get(route('eventos.edit', $evento))->assertForbidden();
        $this->actingAs($user)->post(route('eventos.cancelar', $evento), ['motivo_cancelamento' => 'x'])->assertForbidden();
    }

    // ─── Validações de criação ────────────────────────────────────────────────

    public function test_tipo_evento_e_rejeitado(): void
    {
        $admin = $this->usuarioAdmin();

        $this->actingAs($admin)
            ->post(route('eventos.store'), $this->dadosEvento(['tipo' => 'evento']))
            ->assertSessionHasErrors('tipo');
    }

    public function test_data_fim_e_obrigatoria_ao_criar_evento(): void
    {
        $admin = $this->usuarioAdmin();

        $this->actingAs($admin)
            ->post(route('eventos.store'), $this->dadosEvento(['data_fim' => null]))
            ->assertSessionHasErrors('data_fim');
    }

    public function test_data_fim_deve_ser_posterior_a_data_inicio(): void
    {
        $admin = $this->usuarioAdmin();

        $this->actingAs($admin)->post(route('eventos.store'), $this->dadosEvento([
            'data_inicio' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'data_fim'    => now()->addDay()->format('Y-m-d H:i:s'),
        ]))->assertSessionHasErrors('data_fim');
    }

    public function test_limite_inscricao_deve_ser_anterior_a_data_inicio(): void
    {
        $admin = $this->usuarioAdmin();

        $this->actingAs($admin)->post(route('eventos.store'), $this->dadosEvento([
            'limite_inscricao' => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]))->assertSessionHasErrors('limite_inscricao');
    }

    public function test_cidade_e_obrigatoria_ao_criar_evento(): void
    {
        $admin = $this->usuarioAdmin();

        $this->actingAs($admin)
            ->post(route('eventos.store'), $this->dadosEvento(['cidade_id' => null]))
            ->assertSessionHasErrors('cidade_id');
    }

    public function test_admin_cria_evento_com_cidade(): void
    {
        $admin = $this->usuarioAdmin();

        $this->actingAs($admin)->post(route('eventos.store'), $this->dadosEvento())
            ->assertRedirect(route('eventos.index'));

        $this->assertDatabaseHas('eventos', [
            'titulo'    => 'Oficina de alegria',
            'cidade_id' => $this->cidadeId,
        ]);
    }

    // ─── Inscrição básica ────────────────────────────────────────────────────

    public function test_usuario_consegue_se_inscrever(): void
    {
        $user   = $this->criarUsuario();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $user->id]);

        $this->actingAs($user)->post(route('eventos.inscricao.store', $evento))->assertRedirect();

        $this->assertDatabaseHas('evento_participantes', [
            'evento_id' => $evento->id,
            'user_id'   => $user->id,
            'status'    => 'inscrito',
        ]);
    }

    public function test_usuario_nao_consegue_se_inscrever_duas_vezes(): void
    {
        $user   = $this->criarUsuario();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $user->id]);

        $this->actingAs($user)->post(route('eventos.inscricao.store', $evento));
        $this->actingAs($user)->post(route('eventos.inscricao.store', $evento));

        $this->assertSame(
            1,
            DB::table('evento_participantes')
                ->where('evento_id', $evento->id)
                ->where('user_id', $user->id)
                ->count()
        );
    }

    public function test_usuario_nao_consegue_se_inscrever_em_evento_finalizado(): void
    {
        $user   = $this->criarUsuario();
        $evento = Evento::create([...$this->dadosEvento(['status' => 'finalizado']), 'criado_por_id' => $user->id]);

        $this->actingAs($user)->post(route('eventos.inscricao.store', $evento))
            ->assertSessionHas('mensagem_erro', 'Este evento já foi finalizado.');
    }

    public function test_usuario_nao_consegue_se_inscrever_em_evento_que_ja_comecou(): void
    {
        $user   = $this->criarUsuario();
        $evento = Evento::create([
            ...$this->dadosEvento(),
            'criado_por_id' => $user->id,
            'data_inicio'   => now()->subHour(),
        ]);

        $this->actingAs($user)->post(route('eventos.inscricao.store', $evento))
            ->assertSessionHas('mensagem_erro', 'Este evento já começou.');
    }

    public function test_usuario_nao_consegue_se_inscrever_em_evento_lotado(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([...$this->dadosEvento(['limite_participantes' => 1]), 'criado_por_id' => $admin->id]);
        $outro  = $this->criarUsuario();
        $evento->participantes()->attach($outro->id, ['status' => 'inscrito', 'inscrito_em' => now()]);

        $user = $this->criarUsuario();
        $this->actingAs($user)->post(route('eventos.inscricao.store', $evento))
            ->assertSessionHas('mensagem_erro', 'Não há vagas disponíveis.');
    }

    // ─── Limite de inscrição (prazo) ──────────────────────────────────────────

    public function test_usuario_nao_consegue_se_inscrever_apos_prazo(): void
    {
        $user   = $this->criarUsuario();
        $evento = Evento::create([
            ...$this->dadosEvento(),
            'criado_por_id'    => $user->id,
            'limite_inscricao' => now()->subHour(),
        ]);

        $this->actingAs($user)->post(route('eventos.inscricao.store', $evento))
            ->assertSessionHas('mensagem_erro', 'O prazo de inscrição deste evento foi encerrado.');
    }

    public function test_usuario_consegue_se_inscrever_antes_do_prazo(): void
    {
        $user   = $this->criarUsuario();
        $evento = Evento::create([
            ...$this->dadosEvento(),
            'criado_por_id'    => $user->id,
            'limite_inscricao' => now()->addHours(2),
        ]);

        $this->actingAs($user)->post(route('eventos.inscricao.store', $evento))->assertRedirect();

        $this->assertDatabaseHas('evento_participantes', [
            'evento_id' => $evento->id,
            'user_id'   => $user->id,
            'status'    => 'inscrito',
        ]);
    }

    // ─── Cancelamento de inscrição ────────────────────────────────────────────

    public function test_usuario_consegue_cancelar_inscricao(): void
    {
        $user   = $this->criarUsuario();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $user->id]);
        $evento->participantes()->attach($user->id, ['status' => 'inscrito', 'inscrito_em' => now()]);

        $this->actingAs($user)->delete(route('eventos.inscricao.destroy', $evento))->assertRedirect();

        $this->assertDatabaseHas('evento_participantes', [
            'evento_id' => $evento->id,
            'user_id'   => $user->id,
            'status'    => 'cancelado',
        ]);
    }

    public function test_usuario_nao_consegue_cancelar_inscricao_apos_inicio_do_evento(): void
    {
        $user   = $this->criarUsuario();
        $evento = Evento::create([
            ...$this->dadosEvento(),
            'criado_por_id' => $user->id,
            'data_inicio'   => now()->subHour(),
        ]);
        $evento->participantes()->attach($user->id, ['status' => 'inscrito', 'inscrito_em' => now()]);

        $this->actingAs($user)->delete(route('eventos.inscricao.destroy', $evento))
            ->assertSessionHas('mensagem_erro', 'Não é possível cancelar a inscrição após o início do evento.');
    }

    public function test_usuario_nao_consegue_cancelar_inscricao_com_presenca_registrada(): void
    {
        $user   = $this->criarUsuario();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $user->id]);
        $evento->participantes()->attach($user->id, [
            'status'      => 'inscrito',
            'inscrito_em' => now(),
            'presenca'    => 'presente',
        ]);

        $this->actingAs($user)->delete(route('eventos.inscricao.destroy', $evento))
            ->assertSessionHas('mensagem_erro', 'Não é possível cancelar a inscrição com presença já registrada.');
    }

    // ─── inscrever_me ─────────────────────────────────────────────────────────

    public function test_admin_se_auto_inscreve_com_inscrever_me(): void
    {
        $admin = $this->usuarioAdmin();

        $this->actingAs($admin)
            ->post(route('eventos.store'), [...$this->dadosEvento(), 'inscrever_me' => true])
            ->assertRedirect(route('eventos.index'));

        $evento = Evento::where('titulo', 'Oficina de alegria')->first();
        $this->assertDatabaseHas('evento_participantes', [
            'evento_id' => $evento->id,
            'user_id'   => $admin->id,
            'status'    => 'inscrito',
        ]);
    }

    public function test_admin_nao_e_inscrito_automaticamente_sem_inscrever_me(): void
    {
        $admin = $this->usuarioAdmin();

        $this->actingAs($admin)->post(route('eventos.store'), $this->dadosEvento())
            ->assertRedirect(route('eventos.index'));

        $evento = Evento::where('titulo', 'Oficina de alegria')->first();
        $this->assertDatabaseMissing('evento_participantes', [
            'evento_id' => $evento->id,
            'user_id'   => $admin->id,
        ]);
    }

    // ─── Finalização ─────────────────────────────────────────────────────────

    public function test_admin_consegue_finalizar_evento_que_ja_comecou(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([
            ...$this->dadosEvento(),
            'criado_por_id' => $admin->id,
            'data_inicio'   => now()->subHour(),
        ]);

        $this->actingAs($admin)
            ->post(route('eventos.finalizar', $evento), ['observacoes_finalizacao' => 'Tudo certo'])
            ->assertRedirect(route('eventos.show', $evento));

        $this->assertDatabaseHas('eventos', [
            'id'                => $evento->id,
            'status'            => 'finalizado',
            'finalizado_por_id' => $admin->id,
        ]);
    }

    public function test_responsavel_consegue_finalizar_evento(): void
    {
        $responsavel = $this->criarUsuario();
        $evento      = Evento::create([
            ...$this->dadosEvento(),
            'criado_por_id'  => $responsavel->id,
            'responsavel_id' => $responsavel->id,
            'data_inicio'    => now()->subHour(),
        ]);

        $this->actingAs($responsavel)
            ->post(route('eventos.finalizar', $evento), [])
            ->assertRedirect(route('eventos.show', $evento));

        $this->assertDatabaseHas('eventos', ['id' => $evento->id, 'status' => 'finalizado']);
    }

    public function test_nao_e_possivel_finalizar_evento_que_ainda_nao_comecou(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('eventos.finalizar', $evento), [])
            ->assertSessionHas('mensagem_erro', 'O evento ainda não começou.');

        $this->assertDatabaseHas('eventos', ['id' => $evento->id, 'status' => 'agendado']);
    }

    public function test_evento_cancelado_nao_pode_ser_finalizado(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([
            ...$this->dadosEvento(['status' => 'cancelado']),
            'criado_por_id' => $admin->id,
            'data_inicio'   => now()->subHour(),
        ]);

        $this->actingAs($admin)
            ->post(route('eventos.finalizar', $evento), [])
            ->assertSessionHas('mensagem_erro', 'Este evento foi cancelado e não pode ser finalizado.');
    }

    public function test_evento_ja_finalizado_nao_pode_ser_finalizado_novamente(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([
            ...$this->dadosEvento(['status' => 'finalizado']),
            'criado_por_id' => $admin->id,
            'data_inicio'   => now()->subHour(),
        ]);

        $this->actingAs($admin)
            ->post(route('eventos.finalizar', $evento), [])
            ->assertSessionHas('mensagem_erro', 'Este evento já foi finalizado.');
    }

    public function test_usuario_comum_nao_consegue_finalizar_evento(): void
    {
        $user   = $this->criarUsuario();
        $evento = Evento::create([
            ...$this->dadosEvento(),
            'criado_por_id' => $user->id,
            'data_inicio'   => now()->subHour(),
        ]);

        $this->actingAs($user)->post(route('eventos.finalizar', $evento), [])->assertForbidden();
    }

    public function test_evento_finalizado_nao_pode_ser_editado(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([...$this->dadosEvento(['status' => 'finalizado']), 'criado_por_id' => $admin->id]);

        $this->actingAs($admin)
            ->put(route('eventos.update', $evento), $this->dadosEvento(['titulo' => 'Novo título']))
            ->assertRedirect(route('eventos.show', $evento));

        $this->assertDatabaseHas('eventos', ['id' => $evento->id, 'titulo' => 'Oficina de alegria']);
    }

    public function test_evento_finalizado_nao_pode_ser_cancelado(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([...$this->dadosEvento(['status' => 'finalizado']), 'criado_por_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('eventos.cancelar', $evento), ['motivo_cancelamento' => 'teste'])
            ->assertSessionHas('mensagem_erro', 'Evento finalizado não pode ser cancelado.');

        $this->assertDatabaseHas('eventos', ['id' => $evento->id, 'status' => 'finalizado']);
    }

    // ─── Presença ─────────────────────────────────────────────────────────────

    public function test_admin_consegue_registrar_presenca(): void
    {
        $admin  = $this->usuarioAdmin();
        $user   = $this->criarUsuario();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $admin->id, 'data_inicio' => now()->subHour()]);
        $evento->participantes()->attach($user->id, ['status' => 'inscrito', 'inscrito_em' => now()]);

        $this->actingAs($admin)->put(route('eventos.presencas.update', $evento), [
            'participantes' => [
                ['user_id' => $user->id, 'presenca' => 'presente', 'observacao_presenca' => null],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('evento_participantes', [
            'evento_id' => $evento->id,
            'user_id'   => $user->id,
            'presenca'  => 'presente',
        ]);
    }

    public function test_responsavel_consegue_registrar_presenca(): void
    {
        $responsavel = $this->criarUsuario();
        $user        = $this->criarUsuario();
        $evento      = Evento::create([
            ...$this->dadosEvento(),
            'criado_por_id'  => $responsavel->id,
            'responsavel_id' => $responsavel->id,
            'data_inicio'    => now()->subHour(),
        ]);
        $evento->participantes()->attach($user->id, ['status' => 'inscrito', 'inscrito_em' => now()]);

        $this->actingAs($responsavel)->put(route('eventos.presencas.update', $evento), [
            'participantes' => [
                ['user_id' => $user->id, 'presenca' => 'ausente', 'observacao_presenca' => null],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('evento_participantes', [
            'evento_id' => $evento->id,
            'user_id'   => $user->id,
            'presenca'  => 'ausente',
        ]);
    }

    public function test_responsavel_consegue_registrar_presenca_apos_data_fim(): void
    {
        $responsavel = $this->criarUsuario();
        $user        = $this->criarUsuario();
        $evento      = Evento::create([
            ...$this->dadosEvento(),
            'criado_por_id'  => $responsavel->id,
            'responsavel_id' => $responsavel->id,
            'data_inicio'    => now()->subDays(2),
            'data_fim'       => now()->subHour(),
            'status'         => 'finalizado',
        ]);
        $evento->participantes()->attach($user->id, ['status' => 'inscrito', 'inscrito_em' => now()->subDays(2)]);

        $this->actingAs($responsavel)->put(route('eventos.presencas.update', $evento), [
            'participantes' => [
                ['user_id' => $user->id, 'presenca' => 'presente', 'observacao_presenca' => null],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('evento_participantes', [
            'evento_id' => $evento->id,
            'user_id'   => $user->id,
            'presenca'  => 'presente',
        ]);
    }

    public function test_admin_consegue_registrar_presenca_apos_finalizacao(): void
    {
        $admin  = $this->usuarioAdmin();
        $user   = $this->criarUsuario();
        $evento = Evento::create([
            ...$this->dadosEvento(['status' => 'finalizado']),
            'criado_por_id' => $admin->id,
            'data_inicio'   => now()->subDays(2),
        ]);
        $evento->participantes()->attach($user->id, ['status' => 'inscrito', 'inscrito_em' => now()->subDays(2)]);

        $this->actingAs($admin)->put(route('eventos.presencas.update', $evento), [
            'participantes' => [
                ['user_id' => $user->id, 'presenca' => 'presente', 'observacao_presenca' => null],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('evento_participantes', [
            'evento_id' => $evento->id,
            'user_id'   => $user->id,
            'presenca'  => 'presente',
        ]);
    }

    public function test_usuario_comum_nao_consegue_registrar_presenca(): void
    {
        $user   = $this->criarUsuario();
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $admin->id]);

        $this->actingAs($user)->put(route('eventos.presencas.update', $evento), [
            'participantes' => [],
        ])->assertForbidden();
    }

    public function test_evento_cancelado_nao_permite_registrar_presenca(): void
    {
        $admin  = $this->usuarioAdmin();
        $user   = $this->criarUsuario();
        $evento = Evento::create([...$this->dadosEvento(['status' => 'cancelado']), 'criado_por_id' => $admin->id]);
        $evento->participantes()->attach($user->id, ['status' => 'inscrito', 'inscrito_em' => now()]);

        $this->actingAs($admin)->put(route('eventos.presencas.update', $evento), [
            'participantes' => [
                ['user_id' => $user->id, 'presenca' => 'presente', 'observacao_presenca' => null],
            ],
        ])->assertSessionHas('mensagem_erro', 'Evento cancelado não permite registrar presença.');
    }

    // ─── Exclusão ─────────────────────────────────────────────────────────────

    public function test_admin_consegue_excluir_evento_sem_participantes(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $admin->id]);

        $this->actingAs($admin)->delete(route('eventos.destroy', $evento))
            ->assertRedirect(route('eventos.index'));

        $this->assertDatabaseMissing('eventos', ['id' => $evento->id]);
    }

    public function test_admin_nao_consegue_excluir_evento_com_participantes(): void
    {
        $admin  = $this->usuarioAdmin();
        $user   = $this->criarUsuario();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $admin->id]);
        $evento->participantes()->attach($user->id, ['status' => 'inscrito', 'inscrito_em' => now()]);

        $this->actingAs($admin)->delete(route('eventos.destroy', $evento))
            ->assertSessionHas('mensagem_erro');

        $this->assertDatabaseHas('eventos', ['id' => $evento->id]);
    }

    public function test_admin_nao_consegue_excluir_evento_com_presenca_registrada(): void
    {
        $admin  = $this->usuarioAdmin();
        $user   = $this->criarUsuario();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $admin->id]);
        $evento->participantes()->attach($user->id, [
            'status'      => 'inscrito',
            'inscrito_em' => now(),
            'presenca'    => 'presente',
        ]);

        $this->actingAs($admin)->delete(route('eventos.destroy', $evento))
            ->assertSessionHas('mensagem_erro');

        $this->assertDatabaseHas('eventos', ['id' => $evento->id]);
    }

    public function test_usuario_comum_nao_consegue_excluir_evento(): void
    {
        $user   = $this->criarUsuario();
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $admin->id]);

        $this->actingAs($user)->delete(route('eventos.destroy', $evento))->assertForbidden();

        $this->assertDatabaseHas('eventos', ['id' => $evento->id]);
    }

    // ─── Comando de finalização automática ───────────────────────────────────

    public function test_comando_finaliza_eventos_cuja_data_fim_passou(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([
            ...$this->dadosEvento(),
            'criado_por_id' => $admin->id,
            'data_inicio'   => now()->subDays(2),
            'data_fim'      => now()->subHour(),
        ]);

        $this->artisan('eventos:finalizar-passados');

        $this->assertDatabaseHas('eventos', ['id' => $evento->id, 'status' => 'finalizado']);
        $this->assertNotNull($evento->fresh()->finalizado_em);
    }

    public function test_comando_finaliza_evento_sem_data_fim_quando_data_inicio_passou(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([
            ...$this->dadosEvento(),
            'criado_por_id' => $admin->id,
            'data_inicio'   => now()->subHour(),
            'data_fim'      => null,
        ]);

        $this->artisan('eventos:finalizar-passados');

        $this->assertDatabaseHas('eventos', ['id' => $evento->id, 'status' => 'finalizado']);
    }

    public function test_comando_nao_finaliza_eventos_que_ainda_nao_terminaram(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $admin->id]);

        $this->artisan('eventos:finalizar-passados');

        $this->assertDatabaseHas('eventos', ['id' => $evento->id, 'status' => 'agendado']);
    }

    public function test_comando_nao_finaliza_eventos_cancelados(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([
            ...$this->dadosEvento(['status' => 'cancelado']),
            'criado_por_id' => $admin->id,
            'data_inicio'   => now()->subDays(2),
            'data_fim'      => now()->subHour(),
        ]);

        $this->artisan('eventos:finalizar-passados');

        $this->assertDatabaseHas('eventos', ['id' => $evento->id, 'status' => 'cancelado']);
    }

    public function test_filtra_eventos_por_cidade_do_voluntario_ou_parametro(): void
    {
        $estado = Estado::create(['nome' => 'RS', 'sigla' => 'RS']);
        $cidadePOA = Cidade::forceCreate(['nome' => 'Porto Alegre', 'estado_id' => $estado->id]);
        $cidadeSM  = Cidade::forceCreate(['nome' => 'Santa Maria', 'estado_id' => $estado->id]);

        $voluntario = \App\Models\Voluntario::create([
            'nome_completo'  => 'Voluntario Eventos',
            'email'          => 'vol_evt@teste.com',
            'cidade_base_id' => $cidadePOA->id,
            'status'         => 'ativo',
        ]);
        $user = User::factory()->createOne(['voluntario_id' => $voluntario->id]);

        $eventoPOA = Evento::create([
            ...$this->dadosEvento(['cidade_id' => $cidadePOA->id, 'titulo' => 'Evento POA']),
            'criado_por_id' => $user->id,
        ]);

        $eventoSM = Evento::create([
            ...$this->dadosEvento(['cidade_id' => $cidadeSM->id, 'titulo' => 'Evento SM']),
            'criado_por_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('eventos.index', ['mes' => $eventoPOA->data_inicio->format('Y-m')]))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Evento/Index')
                ->where('cidadeId', $cidadePOA->id)
                ->has('eventos', 1)
                ->where('eventos.0.id', $eventoPOA->id)
            );

        $this->actingAs($user)
            ->get(route('eventos.index', [
                'mes' => $eventoSM->data_inicio->format('Y-m'),
                'cidade_id' => $cidadeSM->id,
            ]))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Evento/Index')
                ->where('cidadeId', $cidadeSM->id)
                ->has('eventos', 1)
                ->where('eventos.0.id', $eventoSM->id)
            );

        $this->actingAs($user)
            ->get(route('eventos.index', [
                'mes' => $eventoPOA->data_inicio->format('Y-m'),
                'cidade_id' => 'todas',
            ]))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Evento/Index')
                ->where('cidadeId', 'todas')
                ->has('eventos', 2)
            );
    }

    public function test_admin_corrige_inscricao_com_auditoria_apos_inicio(): void
    {
        $admin = $this->usuarioAdmin();
        $voluntario = $this->usuarioVoluntario();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $admin->id, 'data_inicio' => now()->subHour(), 'data_fim' => now()->addHour()]);

        $this->actingAs($admin)->post(route('eventos.ajustes-participacao.store', $evento), [
            'tipo' => 'correcao_inscricao', 'voluntario_id' => $voluntario->id,
            'justificativa' => 'Inscrição não foi registrada por falha operacional.',
        ])->assertRedirect();

        $this->assertDatabaseHas('evento_participantes', ['evento_id' => $evento->id, 'user_id' => $voluntario->id, 'status' => 'inscrito']);
        $this->assertDatabaseHas('eventos_ajustes_participacao', ['evento_id' => $evento->id, 'voluntario_id' => $voluntario->id, 'administrador_id' => $admin->id]);
    }

    public function test_admin_corrige_presenca_e_usuario_comum_recebe_bloqueio(): void
    {
        $admin = $this->usuarioAdmin();
        $voluntario = $this->usuarioVoluntario();
        $comum = $this->criarUsuario();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $admin->id, 'data_inicio' => now()->subHour(), 'data_fim' => now()->addHour()]);
        $evento->participantes()->attach($voluntario->id, ['status' => 'inscrito', 'inscrito_em' => now()->subDay()]);
        $dados = ['tipo' => 'correcao_presenca', 'voluntario_id' => $voluntario->id, 'presenca' => 'presente', 'justificativa' => 'Presença confirmada na lista física do evento.'];

        $this->actingAs($comum)->post(route('eventos.ajustes-participacao.store', $evento), $dados)->assertForbidden();
        $this->actingAs($admin)->post(route('eventos.ajustes-participacao.store', $evento), $dados)->assertRedirect();

        $this->assertDatabaseHas('evento_participantes', ['evento_id' => $evento->id, 'user_id' => $voluntario->id, 'presenca' => 'presente', 'presenca_registrada_por_id' => $admin->id]);
    }

    private function usuarioVoluntario(): User
    {
        $voluntario = Voluntario::query()->create(['nome_completo' => uniqid('Voluntário '), 'email' => uniqid().'@teste.com', 'status' => 'ativo']);

        return User::factory()->createOne(['voluntario_id' => $voluntario->id]);
    }
}
