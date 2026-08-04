<?php

namespace Tests\Feature\Visita\Relatorio;

use App\Enums\TipoRelatorio;
use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Ala;
use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaRelatorio;
use App\Models\Voluntario;
use App\Notifications\RelatorioVisitaNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RelatorioStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_convidado_redireciona_login_no_store(): void
    {
        $visita = $this->criarVisita($this->criarVoluntario());

        $this->post(route('visitas.relatorios.store', $visita), $this->payloadRelatorio())
            ->assertRedirect(route('login'));
    }

    public function test_convidado_redireciona_login_no_pdf(): void
    {
        $visita    = $this->criarVisita($this->criarVoluntario());
        $relatorio = $this->criarRelatorio($visita, $this->criarVoluntario());

        $this->get(route('visitas.relatorios.pdf', [$visita, $relatorio]))
            ->assertRedirect(route('login'));
    }

    public function test_autenticado_cria_relatorio_com_autor_e_enviado_em(): void
    {
        $autor  = $this->criarVoluntario();
        $visita = $this->criarVisita($autor);

        $this->actingAs($autor)
            ->post(route('visitas.relatorios.store', $visita), $this->payloadRelatorio())
            ->assertRedirect();

        $this->assertDatabaseHas('visitas_relatorios', [
            'visita_id' => $visita->id,
            'autor_id'  => $autor->id,
        ]);

        $relatorio = VisitaRelatorio::query()->where('visita_id', $visita->id)->first();

        $this->assertNotNull($relatorio?->enviado_em);
    }

    public function test_rejeita_criacao_de_relatorio_por_usuario_que_nao_participou_da_visita(): void
    {
        $lider = $this->criarVoluntario();
        $visita = $this->criarVisita($lider);

        $naoParticipante = $this->criarVoluntario();

        $this->actingAs($naoParticipante)
            ->get(route('visitas.relatorios.create', $visita))
            ->assertRedirect(route('visitas.relatorios.index', $visita))
            ->assertSessionHas('mensagem_erro');

        $this->actingAs($naoParticipante)
            ->from(route('visitas.relatorios.create', $visita))
            ->post(route('visitas.relatorios.store', $visita), $this->payloadRelatorio())
            ->assertRedirect(route('visitas.relatorios.create', $visita))
            ->assertSessionHasErrors('geral');

        $this->assertDatabaseCount('visitas_relatorios', 0);
    }

    public function test_dentro_de_48h_fora_do_prazo_false(): void
    {
        $autor  = $this->criarVoluntario();
        $visita = $this->criarVisita($autor, fimEm: '2026-06-15 12:00:00');

        $this->travelTo(Carbon::parse('2026-06-16 12:00:00'));

        $this->actingAs($autor)
            ->post(route('visitas.relatorios.store', $visita), $this->payloadRelatorio())
            ->assertRedirect();

        $this->assertDatabaseHas('visitas_relatorios', [
            'visita_id'     => $visita->id,
            'fora_do_prazo' => false,
        ]);
    }

    public function test_apos_48h_fora_do_prazo_true_e_store_com_sucesso(): void
    {
        $autor  = $this->criarVoluntario();
        $visita = $this->criarVisita($autor, fimEm: '2026-06-15 12:00:00');

        $this->travelTo(Carbon::parse('2026-06-18 13:00:00'));

        $this->actingAs($autor)
            ->post(route('visitas.relatorios.store', $visita), $this->payloadRelatorio())
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('visitas_relatorios', [
            'visita_id'     => $visita->id,
            'fora_do_prazo' => true,
        ]);
    }

    public function test_duas_creates_na_mesma_visita_geram_duas_linhas(): void
    {
        $autor  = $this->criarVoluntario();
        $visita = $this->criarVisita($autor);

        $this->actingAs($autor)
            ->post(route('visitas.relatorios.store', $visita), $this->payloadRelatorio())
            ->assertRedirect();

        $this->actingAs($autor)
            ->post(route('visitas.relatorios.store', $visita), $this->payloadRelatorio(['resumo' => 'Segundo relatório']))
            ->assertRedirect();

        $this->assertSame(2, VisitaRelatorio::query()->where('visita_id', $visita->id)->count());
    }

    public function test_store_falha_em_visita_cancelada(): void
    {
        $autor  = $this->criarVoluntario();
        $visita = $this->criarVisita($autor, status: VisitaStatus::Cancelada);

        $this->actingAs($autor)
            ->from(route('visitas.relatorios.create', $visita))
            ->post(route('visitas.relatorios.store', $visita), $this->payloadRelatorio())
            ->assertRedirect(route('visitas.relatorios.create', $visita))
            ->assertSessionHasErrors('geral');

        $this->assertDatabaseCount('visitas_relatorios', 0);
    }

    public function test_store_altera_status_da_visita_agendada_para_realizada(): void
    {
        $autor  = $this->criarVoluntario();
        $visita = $this->criarVisita($autor, status: VisitaStatus::Agendada);

        $this->actingAs($autor)
            ->post(route('visitas.relatorios.store', $visita), $this->payloadRelatorio())
            ->assertRedirect();

        $this->assertDatabaseHas('visitas', [
            'id'     => $visita->id,
            'status' => VisitaStatus::Realizada->value,
        ]);
    }

    public function test_envia_notificacao_por_email_para_integrantes_da_visita(): void
    {
        Notification::fake();

        $autor  = $this->criarVoluntario();
        $visita = $this->criarVisita($autor);

        \App\Models\VisitaParticipante::query()->create([
            'visita_id'           => $visita->id,
            'voluntario_id'       => $autor->id,
            'tipo_participacao'   => 'palhaco',
            'papel_na_visita'     => 'participante',
            'status_participacao' => 'confirmado',
        ]);

        $outroParticipante = $this->criarVoluntario();
        \App\Models\VisitaParticipante::query()->create([
            'visita_id'           => $visita->id,
            'voluntario_id'       => $outroParticipante->id,
            'tipo_participacao'   => 'palhaco',
            'papel_na_visita'     => 'participante',
            'status_participacao' => 'confirmado',
        ]);

        $this->actingAs($autor)
            ->post(route('visitas.relatorios.store', $visita), $this->payloadRelatorio())
            ->assertRedirect();

        Notification::assertSentTo(
            $autor,
            RelatorioVisitaNotification::class,
        );

        Notification::assertSentTo(
            $outroParticipante,
            RelatorioVisitaNotification::class,
        );
    }

    public function test_store_persiste_ala_unidade_id(): void
    {
        $autor  = $this->criarVoluntario();
        $visita = $this->criarVisita($autor);
        $ala    = $this->criarAla($visita->hospital_id, 'Pediatria');

        $this->actingAs($autor)
            ->post(route('visitas.relatorios.store', $visita), $this->payloadRelatorio([
                'ala_unidade_id' => $ala->id,
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('visitas_relatorios', [
            'visita_id'      => $visita->id,
            'ala_unidade_id' => $ala->id,
        ]);
    }

    public function test_store_rejeita_ala_de_outro_hospital(): void
    {
        $autor        = $this->criarVoluntario();
        $visita       = $this->criarVisita($autor);
        $outroHospital = $this->criarHospital();
        $alaOutro     = $this->criarAla($outroHospital->id, 'UTI externa');

        $this->actingAs($autor)
            ->from(route('visitas.relatorios.create', $visita))
            ->post(route('visitas.relatorios.store', $visita), $this->payloadRelatorio([
                'ala_unidade_id' => $alaOutro->id,
            ]))
            ->assertRedirect(route('visitas.relatorios.create', $visita))
            ->assertSessionHasErrors('ala_unidade_id');

        $this->assertDatabaseCount('visitas_relatorios', 0);
    }

    private function criarAla(int $hospitalId, string $nome): Ala
    {
        return Ala::query()->create([
            'hospital_id' => $hospitalId,
            'nome'        => $nome,
        ]);
    }

    private function payloadRelatorio(array $override = []): array
    {
        return array_merge([
            'tipo_relatorio' => TipoRelatorio::Geral->value,
            'resumo'         => 'Resumo do relatório de teste.',
        ], $override);
    }

    private function criarRelatorio(Visita $visita, User $autor): VisitaRelatorio
    {
        return VisitaRelatorio::query()->create([
            'visita_id'      => $visita->id,
            'autor_id'       => $autor->id,
            'tipo_relatorio' => TipoRelatorio::Geral,
            'resumo'         => 'Relatório existente',
            'enviado_em'     => now(),
            'fora_do_prazo'  => false,
        ]);
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

    private function criarHospital(): Hospital
    {
        $estado = Estado::query()->firstOrCreate(
            ['sigla' => 'RS'],
            ['nome' => 'RS'],
        );
        $cidade = Cidade::query()
            ->where('nome', 'POA')
            ->where('estado_id', $estado->id)
            ->first()
            ?? Cidade::query()->forceCreate(['nome' => 'POA', 'estado_id' => $estado->id]);

        return Hospital::query()->create([
            'cidade_id' => $cidade->id,
            'nome'      => 'Hospital Teste ' . uniqid(),
            'cnpj'      => (string) random_int(10000000000000, 99999999999999),
            'endereco'  => 'Rua 1',
            'telefone'  => '51999999999',
            'email'     => 'a@b.com',
            'ativo'     => true,
        ]);
    }

    private function criarVisita(
        User $criador,
        ?string $fimEm = null,
        VisitaStatus $status = VisitaStatus::Agendada,
    ): Visita {
        $hospital = $this->criarHospital();

        return Visita::query()->create([
            'hospital_id'   => $hospital->id,
            'criado_por_id' => $criador->id,
            'lider_id'      => $criador->id,
            'inicio_em'     => '2026-06-15 10:00:00',
            'fim_em'        => $fimEm ?? '2026-06-15 12:00:00',
            'tipo'          => VisitaTipo::Hospital,
            'status'        => $status,
            'origem'        => VisitaOrigem::Sistema,
        ]);
    }
}
