<?php

namespace Tests\Feature\Voluntario;

use App\Enums\MotivoAfastamento;
use App\Enums\PapelNaVisita;
use App\Enums\StatusAfastamento;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaParticipante;
use App\Models\Voluntario;
use App\Models\VoluntarioAfastamento;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AfastamentoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function criarAdmin(): User
    {
        $voluntario = Voluntario::query()->create([
            'nome_completo' => 'Administrador Geral',
            'email' => 'admin_' . uniqid() . '@teste.com',
            'status' => 'ativo',
        ]);

        $user = User::factory()->createOne([
            'voluntario_id' => $voluntario->id,
            'email' => $voluntario->email,
            'status' => 'ativo',
        ]);

        $cargo = Cargo::firstOrCreate(['slug' => 'administrador'], ['nome' => 'Administrador']);
        $user->cargos()->attach($cargo);

        return $user;
    }

    private function criarVoluntario(string $nome = 'Voluntario Teste'): array
    {
        $voluntario = Voluntario::query()->create([
            'nome_completo' => $nome,
            'email' => 'voluntario_' . uniqid() . '@teste.com',
            'status' => 'ativo',
        ]);

        $user = User::factory()->createOne([
            'voluntario_id' => $voluntario->id,
            'email' => $voluntario->email,
            'status' => 'ativo',
        ]);

        $cargo = Cargo::firstOrCreate(['slug' => 'voluntario'], ['nome' => 'Voluntário']);
        $user->cargos()->attach($cargo);

        return [$voluntario, $user];
    }

    private function criarVisita(Carbon $dataHoraInicio): Visita
    {
        $estado = Estado::firstOrCreate(['sigla' => 'RS'], ['nome' => 'Rio Grande do Sul']);
        $cidade = Cidade::firstOrCreate(['nome' => 'Santa Maria', 'estado_id' => $estado->id]);
        $hospital = Hospital::firstOrCreate(
            ['nome' => 'Hospital Geral', 'cidade_id' => $cidade->id],
            [
                'cnpj' => '12.345.678/0001-90',
                'endereco' => 'Rua Teste',
                'telefone' => '5599999999',
                'email' => 'hosp@teste.com',
                'ativo' => true,
            ]
        );

        return Visita::create([
            'hospital_id' => $hospital->id,
            'criado_por_id' => User::factory()->createOne()->id,
            'inicio_em' => $dataHoraInicio,
            'fim_em' => $dataHoraInicio->copy()->addHours(2),
            'tipo' => VisitaTipo::Hospital,
            'status' => VisitaStatus::Agendada,
            'origem' => \App\Enums\VisitaOrigem::Sistema,
        ]);
    }

    public function test_administrador_pode_registrar_afastamento(): void
    {
        $admin = $this->criarAdmin();
        [$voluntario] = $this->criarVoluntario();

        $response = $this->actingAs($admin)->post(route('voluntarios.afastamentos.store', $voluntario), [
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addDays(30)->toDateString(),
            'motivo' => MotivoAfastamento::AtestadoMedico->value,
            'observacoes' => 'Cirurgia no joelho',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('mensagem_sucesso');

        $this->assertDatabaseHas('voluntario_afastamentos', [
            'voluntario_id' => $voluntario->id,
            'registrado_por_id' => $admin->id,
            'motivo' => MotivoAfastamento::AtestadoMedico->value,
            'status' => StatusAfastamento::Ativo->value,
            'observacoes' => 'Cirurgia no joelho',
        ]);

        $this->assertTrue($voluntario->fresh()->estaAfastado());
    }

    public function test_ao_cadastrar_afastamento_cancela_visitas_agendadas_no_periodo(): void
    {
        $admin = $this->criarAdmin();
        [$voluntario, $user] = $this->criarVoluntario();

        // Visita 1: dentro do período
        $visitaNoPeriodo = $this->criarVisita(now()->addDays(5));
        $participacao1 = VisitaParticipante::create([
            'visita_id' => $visitaNoPeriodo->id,
            'voluntario_id' => $user->id,
            'tipo_participacao' => TipoParticipacao::Palhaco->value,
            'papel_na_visita' => PapelNaVisita::Participante->value,
            'status_participacao' => StatusParticipacao::Confirmado->value,
        ]);

        // Visita 2: fora do período
        $visitaFora = $this->criarVisita(now()->addDays(40));
        $participacao2 = VisitaParticipante::create([
            'visita_id' => $visitaFora->id,
            'voluntario_id' => $user->id,
            'tipo_participacao' => TipoParticipacao::Palhaco->value,
            'papel_na_visita' => PapelNaVisita::Participante->value,
            'status_participacao' => StatusParticipacao::Confirmado->value,
        ]);

        // Registra afastamento de 30 dias
        $this->actingAs($admin)->post(route('voluntarios.afastamentos.store', $voluntario), [
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addDays(30)->toDateString(),
            'motivo' => MotivoAfastamento::AtestadoMedico->value,
        ]);

        $this->assertEquals(StatusParticipacao::Cancelado, $participacao1->fresh()->status_participacao);
        $this->assertEquals(StatusParticipacao::Confirmado, $participacao2->fresh()->status_participacao);
    }

    public function test_voluntario_afastado_e_bloqueado_de_se_inscrever_em_visita_no_periodo(): void
    {
        $admin = $this->criarAdmin();
        [$voluntario, $user] = $this->criarVoluntario();

        // Registra afastamento
        $this->actingAs($admin)->post(route('voluntarios.afastamentos.store', $voluntario), [
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addDays(30)->toDateString(),
            'motivo' => MotivoAfastamento::LicencaPessoal->value,
        ]);

        $visitaNoPeriodo = $this->criarVisita(now()->addDays(10));

        // Tenta se auto-inscrever
        $response = $this->actingAs($user)->postJson(route('visitas.participantes.store', $visitaNoPeriodo), [
            'tipo_participacao' => TipoParticipacao::Palhaco->value,
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'sucesso' => false,
            'erros' => ['Voluntário está afastado temporariamente no período desta visita.'],
        ]);
    }

    public function test_gestor_pode_prorrogar_afastamento_e_preserva_historico(): void
    {
        $admin = $this->criarAdmin();
        [$voluntario, $user] = $this->criarVoluntario();

        $afastamento = VoluntarioAfastamento::create([
            'voluntario_id' => $voluntario->id,
            'registrado_por_id' => $admin->id,
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addDays(15)->toDateString(),
            'motivo' => MotivoAfastamento::AtestadoMedico,
            'observacoes' => 'Primeiro atestado de 15 dias',
            'status' => StatusAfastamento::Ativo,
        ]);

        // Visita no período prorrogado (+20 dias)
        $visitaProrrogada = $this->criarVisita(now()->addDays(20));
        $participacao = VisitaParticipante::create([
            'visita_id' => $visitaProrrogada->id,
            'voluntario_id' => $user->id,
            'tipo_participacao' => TipoParticipacao::Palhaco->value,
            'papel_na_visita' => PapelNaVisita::Participante->value,
            'status_participacao' => StatusParticipacao::Confirmado->value,
        ]);

        // Prorroga por +15 dias
        $novaDataFim = now()->addDays(30)->toDateString();
        $response = $this->actingAs($admin)->post(
            route('voluntarios.afastamentos.prorrogar', [$voluntario, $afastamento]),
            [
                'nova_data_fim' => $novaDataFim,
                'observacoes' => 'Prorrogação solicitada pelo médico',
            ]
        );

        $response->assertRedirect();
        $afastamentoAtualizado = $afastamento->fresh();

        $this->assertEquals($novaDataFim, $afastamentoAtualizado->data_fim->toDateString());
        $this->assertStringContainsString('Primeiro atestado de 15 dias', $afastamentoAtualizado->observacoes);
        $this->assertStringContainsString('Prorrogação solicitada pelo médico', $afastamentoAtualizado->observacoes);

        // Participação na visita deve ter sido cancelada com a prorrogação
        $this->assertEquals(StatusParticipacao::Cancelado, $participacao->fresh()->status_participacao);
    }

    public function test_gestor_pode_encerrar_afastamento_antecipadamente(): void
    {
        $admin = $this->criarAdmin();
        [$voluntario] = $this->criarVoluntario();

        $afastamento = VoluntarioAfastamento::create([
            'voluntario_id' => $voluntario->id,
            'registrado_por_id' => $admin->id,
            'data_inicio' => now()->subDays(5)->toDateString(),
            'data_fim' => now()->addDays(25)->toDateString(),
            'motivo' => MotivoAfastamento::AtestadoMedico,
            'status' => StatusAfastamento::Ativo,
        ]);

        $this->assertTrue($voluntario->fresh()->estaAfastado());

        $response = $this->actingAs($admin)->post(
            route('voluntarios.afastamentos.encerrar', [$voluntario, $afastamento]),
            ['observacoes' => 'Retorno antecipado liberado']
        );

        $response->assertRedirect();
        $afastamentoAtualizado = $afastamento->fresh();

        $this->assertEquals(StatusAfastamento::Encerrado, $afastamentoAtualizado->status);
        $this->assertStringContainsString('Retorno antecipado liberado', $afastamentoAtualizado->observacoes);
    }

    public function test_usuario_sem_permissao_nao_pode_gerenciar_afastamento(): void
    {
        [$voluntario, $user] = $this->criarVoluntario();
        [$outroVoluntario] = $this->criarVoluntario('Outro');

        // Usuário comum não pode registrar afastamento
        $response = $this->actingAs($user)->post(route('voluntarios.afastamentos.store', $outroVoluntario), [
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addDays(30)->toDateString(),
            'motivo' => MotivoAfastamento::AtestadoMedico->value,
        ]);

        $response->assertForbidden();
    }

    public function test_nao_permite_cadastrar_novo_afastamento_com_periodo_sobreposto_a_um_ativo(): void
    {
        $admin = $this->criarAdmin();
        [$voluntario] = $this->criarVoluntario();

        VoluntarioAfastamento::create([
            'voluntario_id' => $voluntario->id,
            'registrado_por_id' => $admin->id,
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addDays(30)->toDateString(),
            'motivo' => MotivoAfastamento::AtestadoMedico,
            'status' => StatusAfastamento::Ativo,
        ]);

        // Tentativa de cadastrar outro no meio do período
        $response = $this->actingAs($admin)->post(route('voluntarios.afastamentos.store', $voluntario), [
            'data_inicio' => now()->addDays(10)->toDateString(),
            'data_fim' => now()->addDays(40)->toDateString(),
            'motivo' => MotivoAfastamento::LicencaPessoal->value,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('mensagem_erro');

        $this->assertDatabaseCount('voluntario_afastamentos', 1);
    }

    public function test_nao_permite_prorrogar_afastamento_ja_encerrado_ou_cancelado(): void
    {
        $admin = $this->criarAdmin();
        [$voluntario] = $this->criarVoluntario();

        $afastamentoEncerrado = VoluntarioAfastamento::create([
            'voluntario_id' => $voluntario->id,
            'registrado_por_id' => $admin->id,
            'data_inicio' => now()->subDays(20)->toDateString(),
            'data_fim' => now()->subDays(5)->toDateString(),
            'motivo' => MotivoAfastamento::AtestadoMedico,
            'status' => StatusAfastamento::Encerrado,
        ]);

        $response = $this->actingAs($admin)->post(
            route('voluntarios.afastamentos.prorrogar', [$voluntario, $afastamentoEncerrado]),
            ['nova_data_fim' => now()->addDays(10)->toDateString()]
        );

        $response->assertRedirect();
        $response->assertSessionHas('mensagem_erro');

        $this->assertEquals(StatusAfastamento::Encerrado, $afastamentoEncerrado->fresh()->status);
    }

    public function test_nao_permite_encerrar_afastamento_ja_encerrado(): void
    {
        $admin = $this->criarAdmin();
        [$voluntario] = $this->criarVoluntario();

        $afastamentoEncerrado = VoluntarioAfastamento::create([
            'voluntario_id' => $voluntario->id,
            'registrado_por_id' => $admin->id,
            'data_inicio' => now()->subDays(20)->toDateString(),
            'data_fim' => now()->subDays(5)->toDateString(),
            'motivo' => MotivoAfastamento::AtestadoMedico,
            'status' => StatusAfastamento::Encerrado,
        ]);

        $response = $this->actingAs($admin)->post(
            route('voluntarios.afastamentos.encerrar', [$voluntario, $afastamentoEncerrado]),
            ['observacoes' => 'Tentativa duplicada']
        );

        $response->assertRedirect();
        $response->assertSessionHas('mensagem_erro');
    }

    public function test_retorna_404_ao_tentar_alterar_afastamento_pertencente_a_outro_voluntario(): void
    {
        $admin = $this->criarAdmin();
        [$voluntarioA] = $this->criarVoluntario('Voluntário A');
        [$voluntarioB] = $this->criarVoluntario('Voluntário B');

        $afastamentoB = VoluntarioAfastamento::create([
            'voluntario_id' => $voluntarioB->id,
            'registrado_por_id' => $admin->id,
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addDays(15)->toDateString(),
            'motivo' => MotivoAfastamento::AtestadoMedico,
            'status' => StatusAfastamento::Ativo,
        ]);

        // Tentativa cruzada de prorrogar afastamento do Voluntário B passando a rota do Voluntário A
        $responseProrrogar = $this->actingAs($admin)->post(
            route('voluntarios.afastamentos.prorrogar', [$voluntarioA, $afastamentoB]),
            ['nova_data_fim' => now()->addDays(30)->toDateString()]
        );
        $responseProrrogar->assertNotFound();

        // Tentativa cruzada de encerrar
        $responseEncerrar = $this->actingAs($admin)->post(
            route('voluntarios.afastamentos.encerrar', [$voluntarioA, $afastamentoB]),
            ['observacoes' => 'Teste']
        );
        $responseEncerrar->assertNotFound();

        // Tentativa cruzada de editar
        $responseUpdate = $this->actingAs($admin)->put(
            route('voluntarios.afastamentos.update', [$voluntarioA, $afastamentoB]),
            [
                'data_inicio' => now()->toDateString(),
                'data_fim' => now()->addDays(20)->toDateString(),
                'motivo' => MotivoAfastamento::AtestadoMedico->value,
            ]
        );
        $responseUpdate->assertNotFound();

        // Tentativa cruzada de excluir
        $responseDestroy = $this->actingAs($admin)->delete(
            route('voluntarios.afastamentos.destroy', [$voluntarioA, $afastamentoB])
        );
        $responseDestroy->assertNotFound();
    }

    public function test_administrador_pode_editar_afastamento_e_reduzir_data_fim(): void
    {
        $admin = $this->criarAdmin();
        [$voluntario] = $this->criarVoluntario();

        $afastamento = VoluntarioAfastamento::create([
            'voluntario_id' => $voluntario->id,
            'registrado_por_id' => $admin->id,
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addDays(30)->toDateString(),
            'motivo' => MotivoAfastamento::AtestadoMedico,
            'observacoes' => 'Criado com prazo longo padrão',
            'status' => StatusAfastamento::Ativo,
        ]);

        $novaDataFim = now()->addDays(12)->toDateString();

        $response = $this->actingAs($admin)->put(
            route('voluntarios.afastamentos.update', [$voluntario, $afastamento]),
            [
                'data_inicio' => now()->toDateString(),
                'data_fim' => $novaDataFim,
                'motivo' => MotivoAfastamento::LicencaPessoal->value,
                'observacoes' => 'Ajustado retorno para 12 dias',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('mensagem_sucesso');

        $afastamentoAtualizado = $afastamento->fresh();
        $this->assertEquals($novaDataFim, $afastamentoAtualizado->data_fim->toDateString());
        $this->assertEquals(MotivoAfastamento::LicencaPessoal, $afastamentoAtualizado->motivo);
        $this->assertEquals('Ajustado retorno para 12 dias', $afastamentoAtualizado->observacoes);
    }

    public function test_administrador_pode_excluir_afastamento(): void
    {
        $admin = $this->criarAdmin();
        [$voluntario] = $this->criarVoluntario();

        $afastamento = VoluntarioAfastamento::create([
            'voluntario_id' => $voluntario->id,
            'registrado_por_id' => $admin->id,
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addDays(15)->toDateString(),
            'motivo' => MotivoAfastamento::AtestadoMedico,
            'status' => StatusAfastamento::Ativo,
        ]);

        $this->assertDatabaseHas('voluntario_afastamentos', ['id' => $afastamento->id]);

        $response = $this->actingAs($admin)->delete(
            route('voluntarios.afastamentos.destroy', [$voluntario, $afastamento])
        );

        $response->assertRedirect();
        $response->assertSessionHas('mensagem_sucesso');

        $this->assertDatabaseMissing('voluntario_afastamentos', ['id' => $afastamento->id]);
    }

    public function test_nao_permite_editar_afastamento_com_data_inicio_posterior_a_data_fim(): void
    {
        $admin = $this->criarAdmin();
        [$voluntario] = $this->criarVoluntario();

        $afastamento = VoluntarioAfastamento::create([
            'voluntario_id' => $voluntario->id,
            'registrado_por_id' => $admin->id,
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addDays(15)->toDateString(),
            'motivo' => MotivoAfastamento::AtestadoMedico,
            'status' => StatusAfastamento::Ativo,
        ]);

        $response = $this->actingAs($admin)->put(
            route('voluntarios.afastamentos.update', [$voluntario, $afastamento]),
            [
                'data_inicio' => now()->addDays(10)->toDateString(),
                'data_fim' => now()->addDays(5)->toDateString(),
                'motivo' => MotivoAfastamento::AtestadoMedico->value,
            ]
        );

        $response->assertSessionHasErrors(['data_fim']);
    }

    public function test_nao_permite_editar_afastamento_sobrepondo_outro_afastamento_ativo(): void
    {
        $admin = $this->criarAdmin();
        [$voluntario] = $this->criarVoluntario();

        $afastamento1 = VoluntarioAfastamento::create([
            'voluntario_id' => $voluntario->id,
            'registrado_por_id' => $admin->id,
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addDays(10)->toDateString(),
            'motivo' => MotivoAfastamento::AtestadoMedico,
            'status' => StatusAfastamento::Ativo,
        ]);

        $afastamento2 = VoluntarioAfastamento::create([
            'voluntario_id' => $voluntario->id,
            'registrado_por_id' => $admin->id,
            'data_inicio' => now()->addDays(20)->toDateString(),
            'data_fim' => now()->addDays(30)->toDateString(),
            'motivo' => MotivoAfastamento::LicencaPessoal,
            'status' => StatusAfastamento::Ativo,
        ]);

        // Tenta editar afastamento 2 para cobrir o período do afastamento 1
        $response = $this->actingAs($admin)->put(
            route('voluntarios.afastamentos.update', [$voluntario, $afastamento2]),
            [
                'data_inicio' => now()->addDays(5)->toDateString(),
                'data_fim' => now()->addDays(25)->toDateString(),
                'motivo' => MotivoAfastamento::LicencaPessoal->value,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('mensagem_erro');

        // Garante que afastamento 2 manteve as datas originais
        $this->assertEquals(now()->addDays(20)->toDateString(), $afastamento2->fresh()->data_inicio->toDateString());
    }
}
