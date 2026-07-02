<?php

namespace Tests\Feature\Visita;

use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VisitaIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_convidado_e_redirecionado_para_login(): void
    {
        $this->get(route('visitas.index'))->assertRedirect(route('login'));
    }

    public function test_filtra_visitas_pelo_mes_informado(): void
    {
        $user     = $this->criarUsuario();
        $hospital = $this->criarHospital();

        $visitaJunho = $this->criarVisita($hospital, $user, '2026-06-10 10:00:00');
        $this->criarVisita($hospital, $user, '2026-07-05 10:00:00'); // outro mês

        $this->actingAs($user)
            ->get(route('visitas.index', ['mes' => '2026-06']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Visita/Index')
                ->where('mes', '2026-06')
                ->has('visitas', 1)
                ->where('visitas.0.id', $visitaJunho->id)
            );
    }

    public function test_usa_mes_corrente_quando_mes_nao_informado(): void
    {
        $user     = $this->criarUsuario();
        $hospital = $this->criarHospital();

        $visitaMesAtual = $this->criarVisita($hospital, $user, now()->format('Y-m-') . '15 10:00:00');

        $this->actingAs($user)
            ->get(route('visitas.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Visita/Index')
                ->where('mes', now()->format('Y-m'))
                ->has('visitas', 1)
                ->where('visitas.0.id', $visitaMesAtual->id)
            );
    }

    public function test_visita_cancelada_aparece_no_resultado(): void
    {
        $user     = $this->criarUsuario();
        $hospital = $this->criarHospital();

        $visitaCancelada = $this->criarVisita($hospital, $user, '2026-06-20 10:00:00', VisitaStatus::Cancelada);

        $this->actingAs($user)
            ->get(route('visitas.index', ['mes' => '2026-06']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Visita/Index')
                ->has('visitas', 1)
                ->where('visitas.0.id', $visitaCancelada->id)
            );
    }

    public function test_visita_de_outro_mes_nao_aparece(): void
    {
        $user     = $this->criarUsuario();
        $hospital = $this->criarHospital();

        $this->criarVisita($hospital, $user, '2026-07-01 10:00:00'); // julho

        $this->actingAs($user)
            ->get(route('visitas.index', ['mes' => '2026-06']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Visita/Index')
                ->has('visitas', 0)
            );
    }

    private function criarUsuario(): User
    {
        return User::factory()->createOne();
    }

    private function criarHospital(): Hospital
    {
        $estado = Estado::query()->create(['nome' => 'RS', 'sigla' => 'RS']);
        $cidade = Cidade::query()->forceCreate(['nome' => 'Porto Alegre', 'estado_id' => $estado->id]);

        return Hospital::query()->create([
            'cidade_id' => $cidade->id,
            'nome'      => 'Hospital Teste',
            'cnpj'      => '12345678000199',
            'endereco'  => 'Rua Teste, 1',
            'telefone'  => '51999999999',
            'email'     => 'teste@hospital.com',
            'ativo'     => true,
        ]);
    }

    private function criarVisita(Hospital $hospital, User $user, string $inicioEm, VisitaStatus $status = VisitaStatus::Agendada): Visita
    {
        return Visita::query()->create([
            'hospital_id'   => $hospital->id,
            'criado_por_id' => $user->id,
            'inicio_em'     => $inicioEm,
            'fim_em'        => date('Y-m-d H:i:s', strtotime($inicioEm) + 7200),
            'tipo'          => VisitaTipo::Hospital,
            'status'        => $status,
            'origem'        => VisitaOrigem::Sistema,
        ]);
    }
}
