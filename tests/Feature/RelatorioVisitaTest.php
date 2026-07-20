<?php

namespace Tests\Feature;

use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Hospital;
use App\Models\RelatorioVisita;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioVisitaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_usuario_autenticado_acessa_lista_e_cria_relatorio(): void
    {
        $user = User::factory()->create();
        $visita = $this->criarVisita($user);

        $this->actingAs($user)->get(route('visitas.relatorios.index', $visita))->assertOk();
        $this->actingAs($user)->post(route('visitas.relatorios.store', $visita), $this->payload())->assertRedirect();

        $this->assertDatabaseHas('relatorios_visita', [
            'visita_id' => $visita->id,
            'autor_id' => $user->id,
            'tipo_relatorio' => 'artista',
            'fora_do_prazo' => false,
        ]);

        $this->assertNotNull(RelatorioVisita::first()?->enviado_em);
    }

    public function test_relatorio_apos_48h_e_salvo_fora_do_prazo_sem_bloqueio(): void
    {
        $user = User::factory()->create();
        $visita = $this->criarVisita($user, now()->subHours(49));

        $this->actingAs($user)
            ->post(route('visitas.relatorios.store', $visita), $this->payload())
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('relatorios_visita', [
            'visita_id' => $visita->id,
            'fora_do_prazo' => true,
        ]);
    }

    public function test_visita_aceita_multiplos_relatorios(): void
    {
        $user = User::factory()->create();
        $visita = $this->criarVisita($user);

        $this->actingAs($user)->post(route('visitas.relatorios.store', $visita), $this->payload());
        $this->actingAs($user)->post(route('visitas.relatorios.store', $visita), $this->payload(['tipo_relatorio' => 'paisana', 'resumo' => 'Outro relatório']));

        $this->assertSame(2, $visita->relatorios()->count());
    }

    public function test_usuario_nao_autenticado_nao_acessa_rotas(): void
    {
        $visita = $this->criarVisita(User::factory()->create());

        $this->get(route('visitas.relatorios.index', $visita))->assertRedirect(route('login'));
        $this->post(route('visitas.relatorios.store', $visita), $this->payload())->assertRedirect(route('login'));
    }

    public function test_autor_edita_o_proprio_relatorio(): void
    {
        $autor = User::factory()->create();
        $visita = $this->criarVisita($autor);
        $relatorio = $this->criarRelatorio($visita, $autor);

        $this->actingAs($autor)
            ->put(route('visitas.relatorios.update', [$visita, $relatorio]), $this->payload(['resumo' => 'Resumo atualizado']))
            ->assertRedirect();

        $this->assertDatabaseHas('relatorios_visita', ['id' => $relatorio->id, 'resumo' => 'Resumo atualizado']);
    }

    public function test_usuario_comum_nao_edita_relatorio_de_outro_autor(): void
    {
        $autor = User::factory()->create();
        $outro = User::factory()->create();
        $visita = $this->criarVisita($autor);
        $relatorio = $this->criarRelatorio($visita, $autor);

        $this->actingAs($outro)
            ->put(route('visitas.relatorios.update', [$visita, $relatorio]), $this->payload(['resumo' => 'Invasão']))
            ->assertForbidden();
    }

    public function test_administrador_edita_qualquer_relatorio(): void
    {
        $autor = User::factory()->create();
        $admin = User::factory()->create();
        $cargo = Cargo::firstOrCreate(['slug' => 'administrador'], ['nome' => 'Administrador']);
        $admin->cargos()->attach($cargo);
        $visita = $this->criarVisita($autor);
        $relatorio = $this->criarRelatorio($visita, $autor);

        $this->actingAs($admin)
            ->put(route('visitas.relatorios.update', [$visita, $relatorio]), $this->payload(['resumo' => 'Alterado pelo admin']))
            ->assertRedirect();

        $this->assertDatabaseHas('relatorios_visita', ['id' => $relatorio->id, 'resumo' => 'Alterado pelo admin']);
    }

    public function test_relatorio_de_outra_visita_na_url_retorna_404(): void
    {
        $user = User::factory()->create();
        $visita = $this->criarVisita($user);
        $outraVisita = $this->criarVisita($user);
        $relatorio = $this->criarRelatorio($visita, $user);

        $this->actingAs($user)
            ->get(route('visitas.relatorios.show', [$outraVisita, $relatorio]))
            ->assertNotFound();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'tipo_relatorio' => 'artista',
            'resumo' => 'Resumo da visita',
            'feedback' => 'Feedback',
            'ala_unidade' => 'Pediatria',
            'quartos_visitados' => 4,
            'pessoas_impactadas' => 12,
        ], $overrides);
    }

    private function criarRelatorio(Visita $visita, User $autor): RelatorioVisita
    {
        return RelatorioVisita::create([
            ...$this->payload(),
            'visita_id' => $visita->id,
            'autor_id' => $autor->id,
            'enviado_em' => now(),
            'fora_do_prazo' => false,
        ]);
    }

    private function criarVisita(User $user, $fimEm = null): Visita
    {
        $estado = Estado::firstOrCreate(['sigla' => 'RS'], ['nome' => 'Rio Grande do Sul']);
        $cidade = Cidade::firstOrCreate(['nome' => 'Passo Fundo', 'estado_id' => $estado->id]);
        $hospital = Hospital::create([
            'cidade_id' => $cidade->id,
            'nome' => 'Hospital Teste '.uniqid(),
            'cnpj' => (string) random_int(10000000000000, 99999999999999),
            'endereco' => 'Rua Teste',
            'telefone' => '54999999999',
            'email' => uniqid().'@teste.com',
            'ativo' => true,
        ]);

        return Visita::create([
            'hospital_id' => $hospital->id,
            'criado_por_id' => $user->id,
            'lider_id' => $user->id,
            'inicio_em' => now()->subHours(2),
            'fim_em' => $fimEm ?? now()->subHour(),
            'tipo' => VisitaTipo::Hospital,
            'status' => VisitaStatus::Realizada,
            'origem' => VisitaOrigem::Sistema,
        ]);
    }
}
