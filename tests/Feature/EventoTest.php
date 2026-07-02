<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EventoTest extends TestCase
{
    use RefreshDatabase;

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

    private function dadosEvento(array $overrides = []): array
    {
        return array_merge([
            'titulo'               => 'Oficina de alegria',
            'tipo'                 => 'oficina',
            'descricao'            => 'Descrição',
            'local'                => 'Sede',
            'data_inicio'          => now()->addDay()->format('Y-m-d H:i:s'),
            'data_fim'             => now()->addDays(2)->format('Y-m-d H:i:s'),
            'limite_participantes' => 10,
        ], $overrides);
    }

    public function test_usuario_autenticado_consegue_listar_eventos(): void
    {
        $user = $this->criarUsuario();
        Evento::create([...$this->dadosEvento(), 'criado_por_id' => $user->id]);

        $this->withoutVite()
            ->actingAs($user)
            ->get(route('eventos.index'))
            ->assertOk();
    }

    public function test_admin_consegue_criar_evento(): void
    {
        $admin = $this->usuarioAdmin();

        $this->actingAs($admin)->post(route('eventos.store'), $this->dadosEvento())
            ->assertRedirect(route('eventos.index'));

        $this->assertDatabaseHas('eventos', ['titulo' => 'Oficina de alegria', 'status' => 'agendado', 'criado_por_id' => $admin->id]);
    }

    public function test_admin_consegue_editar_evento(): void
    {
        $admin  = $this->usuarioAdmin();
        $evento = Evento::create([...$this->dadosEvento(), 'criado_por_id' => $admin->id]);

        $this->actingAs($admin)->put(route('eventos.update', $evento), $this->dadosEvento(['titulo' => 'Reunião atualizada']))
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
}
