<?php

namespace Tests\Feature;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Evento;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaParticipante;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarioExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_convidado_e_redirecionado_para_login_em_visitas(): void
    {
        $this->get(route('calendario.exportar.visitas'))->assertRedirect(route('login'));
    }

    public function test_convidado_e_redirecionado_para_login_em_eventos(): void
    {
        $this->get(route('calendario.exportar.eventos'))->assertRedirect(route('login'));
    }

    public function test_exporta_minhas_visitas(): void
    {
        $user    = User::factory()->createOne();
        $hospital = $this->criarHospital();
        $visita   = $this->criarVisita($hospital, $user, '2026-09-10 10:00:00');

        VisitaParticipante::create([
            'visita_id'           => $visita->id,
            'voluntario_id'       => $user->id,
            'tipo_participacao'   => TipoParticipacao::Palhaco,
            'papel_na_visita'     => PapelNaVisita::Participante,
            'status_participacao' => StatusParticipacao::Confirmado,
        ]);

        $response = $this->actingAs($user)
            ->get(route('calendario.exportar.visitas', ['tipo' => 'minhas']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $response->assertSee('BEGIN:VCALENDAR', false);
        $response->assertSee('UID:visita-' . $visita->id . '@esquadrao-da-alegria', false);
    }

    public function test_minhas_visitas_nao_inclui_visitas_de_outro_usuario(): void
    {
        $user    = User::factory()->createOne();
        $outro   = User::factory()->createOne();
        $hospital = $this->criarHospital();
        $visita   = $this->criarVisita($hospital, $outro, '2026-09-10 10:00:00');

        VisitaParticipante::create([
            'visita_id'           => $visita->id,
            'voluntario_id'       => $outro->id,
            'tipo_participacao'   => TipoParticipacao::Palhaco,
            'papel_na_visita'     => PapelNaVisita::Participante,
            'status_participacao' => StatusParticipacao::Confirmado,
        ]);

        $response = $this->actingAs($user)
            ->get(route('calendario.exportar.visitas', ['tipo' => 'minhas']));

        $response->assertOk();
        $response->assertDontSee('UID:visita-' . $visita->id . '@esquadrao-da-alegria', false);
    }

    public function test_visita_cancelada_nao_aparece_na_exportacao(): void
    {
        $user    = User::factory()->createOne();
        $hospital = $this->criarHospital();
        $visita   = $this->criarVisita($hospital, $user, '2026-09-10 10:00:00', VisitaStatus::Cancelada);

        VisitaParticipante::create([
            'visita_id'           => $visita->id,
            'voluntario_id'       => $user->id,
            'tipo_participacao'   => TipoParticipacao::Palhaco,
            'papel_na_visita'     => PapelNaVisita::Participante,
            'status_participacao' => StatusParticipacao::Confirmado,
        ]);

        $response = $this->actingAs($user)
            ->get(route('calendario.exportar.visitas', ['tipo' => 'minhas']));

        $response->assertOk();
        $response->assertDontSee('UID:visita-' . $visita->id . '@esquadrao-da-alegria', false);
    }

    public function test_exporta_visitas_da_cidade(): void
    {
        $estado     = Estado::query()->create(['nome' => 'RS', 'sigla' => 'RS']);
        $cidade     = Cidade::query()->forceCreate(['nome' => 'Porto Alegre', 'estado_id' => $estado->id]);
        $voluntario = Voluntario::query()->create([
            'nome_completo'  => 'Voluntario Teste',
            'email'          => 'vol@teste.com',
            'cidade_base_id' => $cidade->id,
            'status'         => 'ativo',
        ]);
        $user = User::factory()->createOne(['voluntario_id' => $voluntario->id]);

        $hospital = Hospital::query()->create([
            'cidade_id' => $cidade->id,
            'nome'      => 'Hospital Teste',
            'cnpj'      => '12345678000199',
            'endereco'  => 'Rua Teste, 1',
            'telefone'  => '51999999999',
            'email'     => 'teste@hospital.com',
            'ativo'     => true,
        ]);
        $visita = $this->criarVisita($hospital, $user, '2026-09-10 10:00:00');

        $response = $this->actingAs($user)
            ->get(route('calendario.exportar.visitas', ['tipo' => 'cidade']));

        $response->assertOk();
        $response->assertSee('UID:visita-' . $visita->id . '@esquadrao-da-alegria', false);
    }

    public function test_exporta_oficina_e_reuniao(): void
    {
        $user = User::factory()->createOne();

        $oficina = Evento::create([
            'titulo'        => 'Oficina de Palhaco',
            'tipo'          => 'oficina',
            'descricao'     => 'Uma oficina',
            'local'         => 'Sede',
            'data_inicio'   => '2026-09-15 14:00:00',
            'status'        => 'agendado',
            'criado_por_id' => $user->id,
        ]);

        $reuniao = Evento::create([
            'titulo'        => 'Reuniao Mensal',
            'tipo'          => 'reuniao',
            'descricao'     => 'Reuniao de alinhamento',
            'local'         => 'Sede',
            'data_inicio'   => '2026-09-20 19:00:00',
            'status'        => 'agendado',
            'criado_por_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('calendario.exportar.eventos'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $response->assertSee('UID:evento-' . $oficina->id . '@esquadrao-da-alegria', false);
        $response->assertSee('UID:evento-' . $reuniao->id . '@esquadrao-da-alegria', false);
    }

    public function test_evento_cancelado_nao_aparece_na_exportacao(): void
    {
        $user = User::factory()->createOne();

        $cancelado = Evento::create([
            'titulo'        => 'Evento Cancelado',
            'tipo'          => 'oficina',
            'descricao'     => 'Cancelado',
            'local'         => 'Sede',
            'data_inicio'   => '2026-09-15 14:00:00',
            'status'        => 'cancelado',
            'criado_por_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('calendario.exportar.eventos'));

        $response->assertOk();
        $response->assertDontSee('UID:evento-' . $cancelado->id . '@esquadrao-da-alegria', false);
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
