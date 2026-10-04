<?php

namespace Tests\Feature\Lembrete;

use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Evento;
use App\Models\Hospital;
use App\Models\Lembrete;
use App\Models\User;
use App\Models\Visita;
use App\Models\Voluntario;
use App\Services\Lembrete\Processamento\Service as ProcessamentoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LembreteAberturaSeguraTest extends TestCase
{
    use RefreshDatabase;

    // ─── Rotas abertas pelo clique exigem sessão e autorização normais ───────

    public function test_clique_em_lembrete_de_evento_sem_sessao_exige_login(): void
    {
        $evento = Evento::create([
            'titulo' => 'Evento Protegido',
            'tipo' => 'evento',
            'data_inicio' => now()->addDay(),
            'data_fim' => now()->addDay()->addHours(2),
            'status' => 'agendado',
            'criado_por_id' => $this->criarVoluntario()->id,
        ]);

        $this->get(route('eventos.show', $evento))->assertRedirect(route('login'));
    }

    public function test_clique_em_lembrete_de_relatorio_sem_sessao_exige_login(): void
    {
        $visita = $this->criarVisita($this->criarVoluntario());

        $this->get(route('visitas.relatorios.create', $visita))->assertRedirect(route('login'));
    }

    public function test_clique_em_lembrete_de_relatorio_por_quem_nao_participou_e_bloqueado(): void
    {
        $lider = $this->criarVoluntario();
        $visita = $this->criarVisita($lider);
        $semPermissao = $this->criarVoluntario();

        $this->actingAs($semPermissao)
            ->get(route('visitas.relatorios.create', $visita))
            ->assertRedirect(route('visitas.relatorios.index', $visita))
            ->assertSessionHas('mensagem_erro');
    }

    // ─── Payload da notificação não contém dados sensíveis da visita ─────────

    public function test_payload_de_lembrete_de_relatorio_nao_contem_dados_sensiveis(): void
    {
        $lider = $this->criarVoluntario();
        $visita = $this->criarVisita($lider);

        $lembrete = Lembrete::query()->create([
            'atividade_tipo' => 'visita',
            'atividade_id' => $visita->id,
            'tipo' => Lembrete::TIPO_RELATORIO_VISITA,
            'programado_para' => $visita->fim_em,
            'status' => Lembrete::STATUS_PENDENTE,
        ]);

        $notificacao = $this->construirNotificacao($lembrete);

        $this->assertStringNotContainsString($visita->observacoes ?? '__nunca__', $notificacao['body']);
        $this->assertArrayNotHasKey('resumo', $notificacao);
        $this->assertArrayNotHasKey('feedback', $notificacao);
        $this->assertArrayNotHasKey('observacoes', $notificacao);
        $this->assertSame(route('visitas.relatorios.create', ['visita' => $visita->id]), $notificacao['url']);
    }

    public function test_payload_de_lembrete_de_evento_nao_contem_dados_sensiveis(): void
    {
        $evento = Evento::create([
            'titulo' => 'Reunião Confidencial de Diretoria',
            'descricao' => 'Pauta sigilosa sobre reestruturação',
            'tipo' => 'reuniao',
            'data_inicio' => now()->addHours(24),
            'data_fim' => now()->addHours(26),
            'status' => 'agendado',
            'criado_por_id' => $this->criarVoluntario()->id,
        ]);

        $lembrete = Lembrete::query()->create([
            'atividade_tipo' => 'evento',
            'atividade_id' => $evento->id,
            'tipo' => Lembrete::TIPO_EVENTO_24H,
            'programado_para' => $evento->data_inicio->copy()->subHours(24),
            'status' => Lembrete::STATUS_PENDENTE,
        ]);

        $notificacao = $this->construirNotificacao($lembrete);

        $this->assertStringNotContainsString($evento->titulo, $notificacao['body']);
        $this->assertStringNotContainsString($evento->descricao, $notificacao['body']);
        $this->assertArrayNotHasKey('descricao', $notificacao);
        $this->assertSame(route('eventos.show', ['evento' => $evento->id]), $notificacao['url']);
    }

    private function construirNotificacao(Lembrete $lembrete): array
    {
        $reflection = new \ReflectionMethod(ProcessamentoService::class, 'notificacao');
        $reflection->setAccessible(true);

        return $reflection->invoke(new ProcessamentoService(), $lembrete);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    private function criarVoluntario(): User
    {
        $cargo = Cargo::query()->firstOrCreate(['slug' => 'voluntario'], ['nome' => 'Voluntário']);

        $voluntario = Voluntario::query()->create([
            'nome_completo' => 'Voluntário ' . uniqid(),
            'email' => uniqid('vol_') . '@test.com',
            'status' => User::STATUS_ATIVO,
        ]);

        $user = User::factory()->create([
            'voluntario_id' => $voluntario->id,
            'status' => User::STATUS_ATIVO,
        ]);
        $user->cargos()->syncWithoutDetaching([$cargo->id]);

        return $user->fresh('cargos');
    }

    private function criarHospital(): Hospital
    {
        $estado = Estado::query()->firstOrCreate(['sigla' => 'RS'], ['nome' => 'RS']);
        $cidade = Cidade::query()->where('nome', 'POA')->where('estado_id', $estado->id)->first()
            ?? Cidade::query()->forceCreate(['nome' => 'POA', 'estado_id' => $estado->id]);

        return Hospital::query()->create([
            'cidade_id' => $cidade->id,
            'nome' => 'Hospital Teste ' . uniqid(),
            'cnpj' => (string) random_int(10000000000000, 99999999999999),
            'endereco' => 'Rua 1',
            'telefone' => '51999999999',
            'email' => 'a@b.com',
            'ativo' => true,
        ]);
    }

    private function criarVisita(User $lider): Visita
    {
        $hospital = $this->criarHospital();

        return Visita::query()->create([
            'hospital_id' => $hospital->id,
            'criado_por_id' => $lider->id,
            'lider_id' => $lider->id,
            'inicio_em' => now()->subHours(2),
            'fim_em' => now()->subMinutes(5),
            'tipo' => VisitaTipo::Hospital,
            'status' => VisitaStatus::Agendada,
            'origem' => VisitaOrigem::Sistema,
            'observacoes' => 'Paciente relatou dor e recebeu visita da equipe de palhaços.',
        ]);
    }
}
