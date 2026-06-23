<?php

use App\Models\Cargo;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function usuarioAdmin(): User
{
    $user = User::factory()->create();
    $cargo = Cargo::create(['nome' => 'Administrador', 'slug' => 'administrador']);
    $user->cargos()->attach($cargo);
    return $user;
}

function dadosEvento(array $overrides = []): array
{
    return array_merge([
        'titulo' => 'Oficina de alegria',
        'tipo' => 'oficina',
        'descricao' => 'Descrição',
        'local' => 'Sede',
        'data_inicio' => now()->addDay()->format('Y-m-d H:i:s'),
        'data_fim' => now()->addDays(2)->format('Y-m-d H:i:s'),
        'limite_participantes' => 10,
    ], $overrides);
}

test('usuário autenticado consegue listar eventos', function () {
    $user = User::factory()->create();
    Evento::create([...dadosEvento(), 'criado_por_id' => $user->id]);

    $this->actingAs($user)->get(route('eventos.index'))->assertOk();
});

test('admin consegue criar evento', function () {
    $admin = usuarioAdmin();

    $this->actingAs($admin)->post(route('eventos.store'), dadosEvento())
        ->assertRedirect(route('eventos.index'));

    $this->assertDatabaseHas('eventos', ['titulo' => 'Oficina de alegria', 'status' => 'agendado', 'criado_por_id' => $admin->id]);
});

test('admin consegue editar evento', function () {
    $admin = usuarioAdmin();
    $evento = Evento::create([...dadosEvento(), 'criado_por_id' => $admin->id]);

    $this->actingAs($admin)->put(route('eventos.update', $evento), dadosEvento(['titulo' => 'Reunião atualizada']))
        ->assertRedirect(route('eventos.show', $evento));

    $this->assertDatabaseHas('eventos', ['id' => $evento->id, 'titulo' => 'Reunião atualizada']);
});

test('admin consegue cancelar evento', function () {
    $admin = usuarioAdmin();
    $evento = Evento::create([...dadosEvento(), 'criado_por_id' => $admin->id]);

    $this->actingAs($admin)->post(route('eventos.cancelar', $evento), ['motivo_cancelamento' => 'Sem quórum'])
        ->assertRedirect(route('eventos.show', $evento));

    $this->assertDatabaseHas('eventos', ['id' => $evento->id, 'status' => 'cancelado', 'motivo_cancelamento' => 'Sem quórum']);
});

test('usuário autenticado consegue se inscrever', function () {
    $user = User::factory()->create();
    $evento = Evento::create([...dadosEvento(), 'criado_por_id' => $user->id]);

    $this->actingAs($user)->post(route('eventos.inscricao.store', $evento))->assertRedirect();

    $this->assertDatabaseHas('evento_participantes', ['evento_id' => $evento->id, 'user_id' => $user->id, 'status' => 'inscrito']);
});

test('usuário não consegue se inscrever duas vezes ativamente', function () {
    $user = User::factory()->create();
    $evento = Evento::create([...dadosEvento(), 'criado_por_id' => $user->id]);

    $this->actingAs($user)->post(route('eventos.inscricao.store', $evento));
    $this->actingAs($user)->post(route('eventos.inscricao.store', $evento));

    expect(DB::table('evento_participantes')->where('evento_id', $evento->id)->where('user_id', $user->id)->count())->toBe(1);
});

test('usuário não consegue se inscrever em evento cancelado', function () {
    $user = User::factory()->create();
    $evento = Evento::create([...dadosEvento(['status' => 'cancelado']), 'criado_por_id' => $user->id]);

    $this->actingAs($user)->post(route('eventos.inscricao.store', $evento))->assertSessionHas('mensagem_erro', 'Este evento foi cancelado.');
});

test('usuário consegue cancelar sua inscrição', function () {
    $user = User::factory()->create();
    $evento = Evento::create([...dadosEvento(), 'criado_por_id' => $user->id]);
    $evento->participantes()->attach($user->id, ['status' => 'inscrito', 'inscrito_em' => now()]);

    $this->actingAs($user)->delete(route('eventos.inscricao.destroy', $evento))->assertRedirect();

    $this->assertDatabaseHas('evento_participantes', ['evento_id' => $evento->id, 'user_id' => $user->id, 'status' => 'cancelado']);
});

test('usuário comum não consegue acessar criação edição e cancelamento', function () {
    $user = User::factory()->create();
    $evento = Evento::create([...dadosEvento(), 'criado_por_id' => $user->id]);

    $this->actingAs($user)->get(route('eventos.create'))->assertForbidden();
    $this->actingAs($user)->get(route('eventos.edit', $evento))->assertForbidden();
    $this->actingAs($user)->post(route('eventos.cancelar', $evento), ['motivo_cancelamento' => 'x'])->assertForbidden();
});
