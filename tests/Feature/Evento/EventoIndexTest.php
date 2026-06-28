<?php

namespace Tests\Feature\Evento;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EventoIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_convidado_e_redirecionado_para_login(): void
    {
        $this->get(route('eventos.index'))->assertRedirect(route('login'));
    }

    public function test_filtra_eventos_pelo_mes_informado(): void
    {
        $user = $this->criarUsuario();

        $eventoJunho = $this->criarEvento($user, '2026-06-10 10:00:00');
        $this->criarEvento($user, '2026-07-05 10:00:00');

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('eventos.index', ['mes' => '2026-06']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Evento/Index')
                ->where('mes', '2026-06')
                ->has('eventos', 1)
                ->where('eventos.0.id', $eventoJunho->id)
            );
    }

    public function test_usa_mes_corrente_quando_mes_nao_informado(): void
    {
        $user = $this->criarUsuario();

        $eventoMesAtual = $this->criarEvento($user, now()->format('Y-m-').'15 10:00:00');

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('eventos.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Evento/Index')
                ->where('mes', now()->format('Y-m'))
                ->has('eventos', 1)
                ->where('eventos.0.id', $eventoMesAtual->id)
            );
    }

    public function test_evento_de_outro_mes_nao_aparece(): void
    {
        $user = $this->criarUsuario();

        $this->criarEvento($user, '2026-07-01 10:00:00');

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('eventos.index', ['mes' => '2026-06']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Evento/Index')
                ->has('eventos', 0)
            );
    }

    private function criarUsuario(): User
    {
        return User::factory()->createOne();
    }

    private function criarEvento(User $user, string $dataInicio, string $status = 'agendado'): Evento
    {
        return Evento::create([
            'titulo' => 'Evento teste',
            'tipo' => 'oficina',
            'descricao' => 'Descrição',
            'local' => 'Sede',
            'data_inicio' => $dataInicio,
            'data_fim' => date('Y-m-d H:i:s', strtotime($dataInicio) + 7200),
            'limite_participantes' => 10,
            'status' => $status,
            'criado_por_id' => $user->id,
        ]);
    }
}
