<?php

namespace Tests\Feature\Evento;

use App\Models\Cargo;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PresencaQrCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_administrador_responsavel_e_criador_podem_abrir_sessao(): void
    {
        $administrador = $this->usuarioAdministrador();
        $responsavel = User::factory()->createOne();
        $criador = User::factory()->createOne();

        foreach ([$administrador, $responsavel, $criador] as $usuario) {
            $evento = $this->evento($criador, $responsavel);

            $this->actingAs($usuario)
                ->post(route('eventos.presencas-qr.sessoes.store', $evento))
                ->assertRedirect();

            $this->assertDatabaseHas('evento_sessoes_presenca', [
                'evento_id' => $evento->id,
                'aberta_por_id' => $usuario->id,
                'encerrada_em' => null,
            ]);
        }
    }

    public function test_usuario_sem_autorizacao_nao_pode_abrir_sessao(): void
    {
        $criador = User::factory()->createOne();
        $evento = $this->evento($criador);

        $this->actingAs(User::factory()->createOne())
            ->post(route('eventos.presencas-qr.sessoes.store', $evento))
            ->assertForbidden();
    }

    public function test_sessao_so_abre_no_dia_a_partir_de_uma_hora_antes(): void
    {
        $criador = User::factory()->createOne();
        $evento = $this->evento($criador, null, now()->addHour()->addMinute());

        $this->actingAs($criador)
            ->post(route('eventos.presencas-qr.sessoes.store', $evento))
            ->assertSessionHas('mensagem_erro');

        $this->assertDatabaseMissing('evento_sessoes_presenca', ['evento_id' => $evento->id]);
    }

    public function test_sessao_nao_abre_para_acao_especial(): void
    {
        $criador = User::factory()->createOne();
        $evento = $this->evento($criador);
        $evento->update(['tipo' => 'evento']);

        $this->actingAs($criador)
            ->post(route('eventos.presencas-qr.sessoes.store', $evento))
            ->assertSessionHas('mensagem_erro');

        $this->assertDatabaseMissing('evento_sessoes_presenca', ['evento_id' => $evento->id]);
    }

    public function test_evento_possui_apenas_uma_sessao_ativa(): void
    {
        $criador = User::factory()->createOne();
        $evento = $this->evento($criador);

        $this->actingAs($criador)->post(route('eventos.presencas-qr.sessoes.store', $evento));
        $this->actingAs($criador)->post(route('eventos.presencas-qr.sessoes.store', $evento));

        $this->assertSame(1, DB::table('evento_sessoes_presenca')->where('evento_id', $evento->id)->whereNull('encerrada_em')->count());
    }

    public function test_participante_autenticado_confirma_presenca_com_auditoria(): void
    {
        $criador = User::factory()->createOne();
        $participante = User::factory()->createOne();
        $evento = $this->evento($criador);
        $sessao = $this->abrirSessao($criador, $evento);
        $url = $this->urlQr($evento, $sessao->id);

        $this->actingAs($participante)->get($url)->assertRedirect(route('eventos.presencas-qr.confirmacao.show'));
        $this->post(route('eventos.presencas-qr.confirmacao.store'))->assertRedirect();

        $this->assertDatabaseHas('evento_participantes', [
            'evento_id' => $evento->id,
            'user_id' => $participante->id,
            'status' => 'inscrito',
            'presenca' => 'presente',
        ]);
        $this->assertDatabaseHas('evento_confirmacoes_presenca', [
            'evento_id' => $evento->id,
            'user_id' => $participante->id,
            'sessao_id' => $sessao->id,
            'metodo' => 'QR_CODE',
        ]);
    }

    public function test_qr_valido_preserva_fluxo_para_usuario_nao_autenticado(): void
    {
        $criador = User::factory()->createOne();
        $evento = $this->evento($criador);
        $sessao = $this->abrirSessao($criador, $evento);
        auth()->logout();

        $this->get($this->urlQr($evento, $sessao->id))
            ->assertRedirect(route('eventos.presencas-qr.confirmacao.show'));

        $this->assertSame($sessao->id, session('evento_presenca_qr.sessao_id'));
        $this->get(route('eventos.presencas-qr.confirmacao.show'))->assertRedirect(route('login'));
    }

    public function test_qr_expirado_alterado_encerrado_ou_de_outro_evento_e_rejeitado(): void
    {
        $criador = User::factory()->createOne();
        $evento = $this->evento($criador);
        $outroEvento = $this->evento($criador);
        $sessao = $this->abrirSessao($criador, $evento);

        $this->get($this->urlQr($evento, $sessao->id).'alterado')->assertForbidden();

        $expirado = URL::temporarySignedRoute('eventos.presencas-qr.acesso', now()->subSecond(), ['evento' => $evento, 'sessao' => $sessao->id]);
        $this->get($expirado)->assertForbidden();

        $urlOutroEvento = URL::temporarySignedRoute('eventos.presencas-qr.acesso', now()->addMinutes(2), ['evento' => $outroEvento, 'sessao' => $sessao->id]);
        $this->get($urlOutroEvento)->assertNotFound();

        DB::table('evento_sessoes_presenca')->where('id', $sessao->id)->update(['encerrada_em' => now()]);
        $this->get($this->urlQr($evento, $sessao->id))->assertGone();
    }

    public function test_confirmacao_duplicada_nao_cria_nova_presenca(): void
    {
        $criador = User::factory()->createOne();
        $participante = User::factory()->createOne();
        $evento = $this->evento($criador);
        $sessao = $this->abrirSessao($criador, $evento);

        $this->actingAs($participante)->get($this->urlQr($evento, $sessao->id));
        $this->post(route('eventos.presencas-qr.confirmacao.store'));
        $this->post(route('eventos.presencas-qr.confirmacao.store'));

        $this->assertSame(1, DB::table('evento_participantes')->where('evento_id', $evento->id)->where('user_id', $participante->id)->count());
        $this->assertSame(1, DB::table('evento_confirmacoes_presenca')->where('evento_id', $evento->id)->where('user_id', $participante->id)->count());
    }

    public function test_qr_preserva_inscricao_existente_e_reativa_inscricao_cancelada_mesmo_com_evento_lotado(): void
    {
        $criador = User::factory()->createOne();
        $inscrito = User::factory()->createOne();
        $cancelado = User::factory()->createOne();
        $evento = $this->evento($criador);
        $inscritoEm = now()->subDays(3)->startOfSecond();
        $evento->participantes()->attach($inscrito->id, ['status' => 'inscrito', 'inscrito_em' => $inscritoEm]);
        $evento->participantes()->attach($cancelado->id, ['status' => 'cancelado', 'inscrito_em' => now()->subDays(2), 'cancelado_em' => now()->subDay()]);
        $sessao = $this->abrirSessao($criador, $evento);

        foreach ([$inscrito, $cancelado] as $participante) {
            $this->actingAs($participante)->get($this->urlQr($evento, $sessao->id));
            $this->post(route('eventos.presencas-qr.confirmacao.store'))->assertRedirect();
        }

        $this->assertDatabaseHas('evento_participantes', ['evento_id' => $evento->id, 'user_id' => $inscrito->id, 'inscrito_em' => $inscritoEm]);
        $this->assertDatabaseHas('evento_participantes', ['evento_id' => $evento->id, 'user_id' => $cancelado->id, 'status' => 'inscrito', 'cancelado_em' => null, 'presenca' => 'presente']);
    }

    public function test_responsavel_pode_encerrar_e_reabrir_com_nova_sessao(): void
    {
        $responsavel = User::factory()->createOne();
        $evento = $this->evento(User::factory()->createOne(), $responsavel);
        $sessao = $this->abrirSessao($responsavel, $evento);

        $this->actingAs($responsavel)
            ->delete(route('eventos.presencas-qr.sessoes.destroy', [$evento, $sessao->id]))
            ->assertRedirect();

        $this->assertDatabaseMissing('evento_sessoes_presenca', ['id' => $sessao->id, 'encerrada_em' => null]);
        $this->post(route('eventos.presencas-qr.sessoes.store', $evento))->assertRedirect();
        $this->assertSame(2, DB::table('evento_sessoes_presenca')->where('evento_id', $evento->id)->count());
    }

    public function test_finalizar_evento_encerra_sessao_ativa(): void
    {
        $administrador = $this->usuarioAdministrador();
        $evento = $this->evento($administrador, null, now()->subMinutes(30));
        $sessao = $this->abrirSessao($administrador, $evento);

        $this->actingAs($administrador)->post(route('eventos.finalizar', $evento), [
            'observacoes_finalizacao' => null,
        ])->assertRedirect();

        $this->assertDatabaseMissing('evento_sessoes_presenca', ['id' => $sessao->id, 'encerrada_em' => null]);
    }

    private function usuarioAdministrador(): User
    {
        $usuario = User::factory()->createOne();
        $cargo = Cargo::query()->create(['nome' => 'Administrador', 'slug' => 'administrador']);
        $usuario->cargos()->attach($cargo);

        return $usuario;
    }

    private function evento(User $criador, ?User $responsavel = null, $inicio = null): Evento
    {
        $inicio ??= now()->addMinutes(30);

        return Evento::query()->create([
            'titulo' => 'Oficina de alegria',
            'tipo' => 'oficina',
            'local' => 'Sede',
            'data_inicio' => $inicio,
            'data_fim' => $inicio->copy()->addHours(2),
            'limite_participantes' => 1,
            'status' => 'agendado',
            'responsavel_id' => $responsavel?->id,
            'criado_por_id' => $criador->id,
        ]);
    }

    private function abrirSessao(User $criador, Evento $evento): object
    {
        $this->actingAs($criador)->post(route('eventos.presencas-qr.sessoes.store', $evento));

        return DB::table('evento_sessoes_presenca')->where('evento_id', $evento->id)->first();
    }

    private function urlQr(Evento $evento, int $sessaoId): string
    {
        return URL::temporarySignedRoute('eventos.presencas-qr.acesso', now()->addMinutes(2), [
            'evento' => $evento,
            'sessao' => $sessaoId,
        ]);
    }
}
