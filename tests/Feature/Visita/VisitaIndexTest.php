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
use App\Models\Voluntario;
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

    public function test_visita_cancelada_nao_aparece_no_calendario(): void
    {
        $user     = $this->criarUsuario();
        $hospital = $this->criarHospital();

        $this->criarVisita($hospital, $user, '2026-06-20 10:00:00', VisitaStatus::Cancelada);

        $this->actingAs($user)
            ->get(route('visitas.index', ['mes' => '2026-06']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Visita/Index')
                ->has('visitas', 0)
            );
    }

    public function test_visita_ativa_aparece_no_calendario(): void
    {
        $user     = $this->criarUsuario();
        $hospital = $this->criarHospital();

        $visita = $this->criarVisita($hospital, $user, '2026-06-20 10:00:00', VisitaStatus::Agendada);

        $this->actingAs($user)
            ->get(route('visitas.index', ['mes' => '2026-06']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Visita/Index')
                ->has('visitas', 1)
                ->where('visitas.0.id', $visita->id)
            );
    }

    public function test_evento_cancelado_nao_aparece_no_calendario(): void
    {
        $user = $this->criarUsuario();

        \App\Models\Evento::create([
            'titulo'        => 'Oficina Cancelada',
            'tipo'          => 'oficina',
            'descricao'     => 'Cancelada',
            'local'         => 'Sede',
            'data_inicio'   => now()->format('Y-m-') . '10 10:00:00',
            'status'        => 'cancelado',
            'criado_por_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('visitas.index', ['cidade_id' => 'todas']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Visita/Index')
                ->has('eventos', 0)
            );
    }

    public function test_evento_ativo_aparece_no_calendario(): void
    {
        $user = $this->criarUsuario();

        $evento = \App\Models\Evento::create([
            'titulo'        => 'Oficina Ativa',
            'tipo'          => 'oficina',
            'descricao'     => 'Ativa',
            'local'         => 'Sede',
            'data_inicio'   => now()->format('Y-m-') . '12 10:00:00',
            'status'        => 'agendado',
            'criado_por_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('visitas.index', ['cidade_id' => 'todas']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Visita/Index')
                ->has('eventos', 1)
                ->where('eventos.0.id', $evento->id)
            );
    }

    public function test_mistura_visitas_e_eventos_exclui_cancelados(): void
    {
        $user     = $this->criarUsuario();
        $hospital = $this->criarHospital();

        $visitaAtiva      = $this->criarVisita($hospital, $user, now()->format('Y-m-') . '05 10:00:00', VisitaStatus::Agendada);
        $this->criarVisita($hospital, $user, now()->format('Y-m-') . '06 10:00:00', VisitaStatus::Cancelada);

        $eventoAtivo = \App\Models\Evento::create([
            'titulo'        => 'Evento Ativo',
            'tipo'          => 'reuniao',
            'descricao'     => 'Reunião',
            'local'         => 'Sede',
            'data_inicio'   => now()->format('Y-m-') . '10 10:00:00',
            'status'        => 'agendado',
            'criado_por_id' => $user->id,
        ]);

        \App\Models\Evento::create([
            'titulo'        => 'Evento Cancelado',
            'tipo'          => 'reuniao',
            'descricao'     => 'Reunião cancelada',
            'local'         => 'Sede',
            'data_inicio'   => now()->format('Y-m-') . '11 10:00:00',
            'status'        => 'cancelado',
            'criado_por_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('visitas.index', ['cidade_id' => 'todas']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Visita/Index')
                ->has('visitas', 1)
                ->where('visitas.0.id', $visitaAtiva->id)
                ->has('eventos', 1)
                ->where('eventos.0.id', $eventoAtivo->id)
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

    public function test_filtra_visitas_automaticamente_pela_cidade_base_do_voluntario(): void
    {
        $estado = Estado::query()->create(['nome' => 'RS', 'sigla' => 'RS']);
        $cidadePOA = Cidade::query()->forceCreate(['nome' => 'Porto Alegre', 'estado_id' => $estado->id]);
        $cidadeSM  = Cidade::query()->forceCreate(['nome' => 'Santa Maria', 'estado_id' => $estado->id]);

        $user = $this->criarUsuario($cidadePOA->id, 'voluntario@teste.com');

        $hospitalPOA = Hospital::query()->create([
            'cidade_id' => $cidadePOA->id,
            'nome'      => 'Hospital POA',
            'cnpj'      => '12345678000101',
            'endereco'  => 'Rua POA, 1',
            'telefone'  => '51999999991',
            'email'     => 'poa@hospital.com',
            'ativo'     => true,
        ]);

        $hospitalSM = Hospital::query()->create([
            'cidade_id' => $cidadeSM->id,
            'nome'      => 'Hospital SM',
            'cnpj'      => '12345678000102',
            'endereco'  => 'Rua SM, 1',
            'telefone'  => '55999999992',
            'email'     => 'sm@hospital.com',
            'ativo'     => true,
        ]);

        $dataHoje = now()->format('Y-m-d H:i:s');
        $visitaPOA = $this->criarVisita($hospitalPOA, $user, $dataHoje);
        $visitaSM  = $this->criarVisita($hospitalSM, $user, $dataHoje);

        $this->actingAs($user)
            ->get(route('visitas.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Visita/Index')
                ->where('cidadeId', $cidadePOA->id)
                ->where('cidadeUsuarioId', $cidadePOA->id)
                ->has('visitas', 1)
                ->where('visitas.0.id', $visitaPOA->id)
            );
    }

    public function test_permite_selecionar_outra_cidade_manualmente(): void
    {
        $estado = Estado::query()->create(['nome' => 'RS', 'sigla' => 'RS']);
        $cidadePOA = Cidade::query()->forceCreate(['nome' => 'Porto Alegre', 'estado_id' => $estado->id]);
        $cidadeSM  = Cidade::query()->forceCreate(['nome' => 'Santa Maria', 'estado_id' => $estado->id]);

        $user = $this->criarUsuario($cidadePOA->id, 'voluntario2@teste.com');

        $hospitalPOA = Hospital::query()->create([
            'cidade_id' => $cidadePOA->id,
            'nome'      => 'Hospital POA',
            'cnpj'      => '12345678000103',
            'endereco'  => 'Rua POA, 1',
            'telefone'  => '51999999993',
            'email'     => 'poa2@hospital.com',
            'ativo'     => true,
        ]);

        $hospitalSM = Hospital::query()->create([
            'cidade_id' => $cidadeSM->id,
            'nome'      => 'Hospital SM',
            'cnpj'      => '12345678000104',
            'endereco'  => 'Rua SM, 1',
            'telefone'  => '55999999994',
            'email'     => 'sm2@hospital.com',
            'ativo'     => true,
        ]);

        $dataHoje = now()->format('Y-m-d H:i:s');
        $visitaPOA = $this->criarVisita($hospitalPOA, $user, $dataHoje);
        $visitaSM  = $this->criarVisita($hospitalSM, $user, $dataHoje);

        $this->actingAs($user)
            ->get(route('visitas.index', ['cidade_id' => $cidadeSM->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Visita/Index')
                ->where('cidadeId', $cidadeSM->id)
                ->has('visitas', 1)
                ->where('visitas.0.id', $visitaSM->id)
            );
    }

    public function test_permite_selecionar_todas_as_cidades_manualmente(): void
    {
        $estado = Estado::query()->create(['nome' => 'RS', 'sigla' => 'RS']);
        $cidadePOA = Cidade::query()->forceCreate(['nome' => 'Porto Alegre', 'estado_id' => $estado->id]);
        $cidadeSM  = Cidade::query()->forceCreate(['nome' => 'Santa Maria', 'estado_id' => $estado->id]);

        $user = $this->criarUsuario($cidadePOA->id, 'voluntario3@teste.com');

        $hospitalPOA = Hospital::query()->create([
            'cidade_id' => $cidadePOA->id,
            'nome'      => 'Hospital POA',
            'cnpj'      => '12345678000105',
            'endereco'  => 'Rua POA, 1',
            'telefone'  => '51999999995',
            'email'     => 'poa3@hospital.com',
            'ativo'     => true,
        ]);

        $hospitalSM = Hospital::query()->create([
            'cidade_id' => $cidadeSM->id,
            'nome'      => 'Hospital SM',
            'cnpj'      => '12345678000106',
            'endereco'  => 'Rua SM, 1',
            'telefone'  => '55999999996',
            'email'     => 'sm3@hospital.com',
            'ativo'     => true,
        ]);

        $dataHoje = now()->format('Y-m-d H:i:s');
        $this->criarVisita($hospitalPOA, $user, $dataHoje);
        $this->criarVisita($hospitalSM, $user, $dataHoje);

        $this->actingAs($user)
            ->get(route('visitas.index', ['cidade_id' => 'todas']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Visita/Index')
                ->where('cidadeId', 'todas')
                ->has('visitas', 2)
            );
    }

    public function test_retorna_eventos_do_mes_e_cidade_no_calendario_de_visitas(): void
    {
        $estado = Estado::query()->create(['nome' => 'RS', 'sigla' => 'RS']);
        $cidadePOA = Cidade::query()->forceCreate(['nome' => 'Porto Alegre', 'estado_id' => $estado->id]);
        $cidadeSM  = Cidade::query()->forceCreate(['nome' => 'Santa Maria', 'estado_id' => $estado->id]);

        $user = $this->criarUsuario($cidadePOA->id, 'vol_vis_evt@teste.com');

        $eventoPOA = \App\Models\Evento::create([
            'titulo'        => 'Oficina de Palhaço POA',
            'tipo'          => 'oficina',
            'descricao'     => 'Oficina',
            'local'         => 'Sede',
            'cidade_id'     => $cidadePOA->id,
            'data_inicio'   => now()->format('Y-m-') . '15 14:00:00',
            'status'        => 'agendado',
            'criado_por_id' => $user->id,
        ]);

        $eventoSM = \App\Models\Evento::create([
            'titulo'        => 'Oficina de Palhaço SM',
            'tipo'          => 'oficina',
            'descricao'     => 'Oficina',
            'local'         => 'Sede',
            'cidade_id'     => $cidadeSM->id,
            'data_inicio'   => now()->format('Y-m-') . '16 14:00:00',
            'status'        => 'agendado',
            'criado_por_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('visitas.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Visita/Index')
                ->has('eventos', 1)
                ->where('eventos.0.id', $eventoPOA->id)
            );
    }

    private function criarUsuario(?int $cidadeBaseId = null, ?string $emailVoluntario = null): User
    {
        if ($cidadeBaseId === null) {
            return User::factory()->createOne();
        }

        $voluntario = Voluntario::query()->create([
            'nome_completo'  => 'Voluntario Teste',
            'email'          => $emailVoluntario ?? uniqid('voluntario_') . '@teste.com',
            'cidade_base_id' => $cidadeBaseId,
            'status'         => 'ativo',
        ]);

        return User::factory()->createOne(['voluntario_id' => $voluntario->id]);
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
