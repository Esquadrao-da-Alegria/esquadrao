<?php

namespace Tests\Feature;

// MODELS
use App\Models\Cargo;
use App\Models\User;
// FACADES
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Laravel\Pulse\Facades\Pulse;
// LIVEWIRE
use Laravel\Pulse\Livewire\Exceptions;
use Laravel\Pulse\Livewire\SlowRequests;
use Livewire\Livewire;
// TESTING
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
// EXCEPTIONS
use RuntimeException;

class MonitoramentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_e_publico_e_retorna_sucesso(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_health_check_falha_quando_o_banco_esta_indisponivel(): void
    {
        config(['app.debug' => false]);
        DB::shouldReceive('select')->once()->with('SELECT 1')
            ->andThrow(new RuntimeException('Banco indisponivel: detalhe privado'));

        $this->get('/up')->assertStatus(500)->assertDontSee('detalhe privado');
    }

    public function test_visitante_do_pulse_e_redirecionado_para_login(): void
    {
        $this->get('/pulse')->assertRedirect(route('login'));
    }

    public function test_usuario_sem_cargo_de_administrador_nao_acessa_pulse(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/pulse')->assertForbidden();
    }

    public function test_administrador_acessa_o_painel_do_pulse(): void
    {
        $user = User::factory()->create();
        $cargo = Cargo::query()->create(['nome' => 'Administrador', 'slug' => 'administrador']);
        $user->cargos()->attach($cargo);
        $user->unsetRelation('cargos');

        $this->actingAs($user)->get('/pulse')->assertOk();
    }

    public function test_resposta_5xx_e_registrada_sem_parametros_ou_payload(): void
    {
        Log::spy();
        Route::post('/_monitoramento/falha/{id}', function () {
            $resposta = response('Falha', 503);

            return $resposta;
        });

        $this->post('/_monitoramento/falha/123?token=segredo', ['senha' => 'segredo'])
            ->assertStatus(503);

        Log::shouldHaveReceived('error')->once()->with('Resposta HTTP 5xx', [
            'status' => 503,
            'metodo' => 'POST',
            'rota' => '_monitoramento/falha/{id}',
        ]);
    }

    public function test_resposta_4xx_nao_e_registrada_como_erro_de_servidor(): void
    {
        Log::spy();

        $this->get('/_monitoramento/inexistente')->assertNotFound();

        Log::shouldNotHaveReceived('error');
    }

    public function test_pulse_armazena_requisicao_lenta_agrupada_pela_rota(): void
    {
        $this->ativarPulse();
        Route::get('/_monitoramento/lenta/{id}', function () {
            usleep(2_100_000);
            $resposta = response('OK');

            return $resposta;
        });

        $this->get('/_monitoramento/lenta/123?token=segredo')->assertOk();
        Pulse::ingest();

        $registro = DB::table('pulse_entries')->where('type', 'slow_request')->first();

        $this->assertNotNull($registro);
        $chave = json_decode($registro->key, true);
        $this->assertSame('/_monitoramento/lenta/{id}', $chave[1]);
        $this->assertGreaterThanOrEqual(2000, $registro->value);

        Livewire::withoutLazyLoading()->test(SlowRequests::class)
            ->assertSee('/_monitoramento/lenta/{id}');
    }

    public function test_pulse_armazena_excecao_da_aplicacao(): void
    {
        $this->ativarPulse();
        Route::get('/_monitoramento/excecao', function () {
            throw new RuntimeException('Erro controlado de monitoramento');
        });

        $this->get('/_monitoramento/excecao')->assertStatus(500);
        Pulse::ingest();

        $excecao = DB::table('pulse_entries')->where('type', 'exception')->first();

        $this->assertNotNull($excecao);
        $this->assertStringContainsString('RuntimeException', $excecao->key);

        Livewire::withoutLazyLoading()->test(Exceptions::class)
            ->assertSee('RuntimeException');
    }

    private function ativarPulse(): void
    {
        if (config('pulse.enabled')) {
            return;
        }

        config(['pulse.enabled' => true]);
        Pulse::startRecording();
        $recorders = config('pulse.recorders');
        Pulse::register($recorders);
    }
}
