<?php

namespace Tests\Feature\Visita\Relatorio;

use App\Enums\TipoRelatorio;
use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaRelatorio;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RelatorioIndexShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_convidado_redireciona_login_no_index(): void
    {
        $visita = $this->criarVisita($this->criarVoluntario());

        $this->get(route('visitas.relatorios.index', $visita))
            ->assertRedirect(route('login'));
    }

    public function test_index_funciona_em_visita_cancelada(): void
    {
        $autor  = $this->criarVoluntario();
        $visita = $this->criarVisita($autor, status: VisitaStatus::Cancelada);
        $this->criarRelatorio($visita, $autor);

        $this->actingAs($autor)
            ->withoutVite()
            ->get(route('visitas.relatorios.index', $visita))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('relatorios', 1)
                ->has('visita')
            );
    }

    public function test_show_funciona_em_visita_cancelada(): void
    {
        $autor     = $this->criarVoluntario();
        $visita    = $this->criarVisita($autor, status: VisitaStatus::Cancelada);
        $relatorio = $this->criarRelatorio($visita, $autor);

        $this->actingAs($autor)
            ->withoutVite()
            ->get(route('visitas.relatorios.show', [$visita, $relatorio]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('relatorio')
                ->where('podeEditar', false)
            );
    }

    public function test_create_redireciona_com_erro_em_visita_cancelada(): void
    {
        $autor  = $this->criarVoluntario();
        $visita = $this->criarVisita($autor, status: VisitaStatus::Cancelada);

        $this->actingAs($autor)
            ->get(route('visitas.relatorios.create', $visita))
            ->assertRedirect(route('visitas.relatorios.index', $visita))
            ->assertSessionHas('mensagem_erro', 'Não é possível criar relatório para visita cancelada.');
    }

    public function test_edit_redireciona_com_erro_em_visita_cancelada(): void
    {
        $autor     = $this->criarVoluntario();
        $visita    = $this->criarVisita($autor, status: VisitaStatus::Cancelada);
        $relatorio = $this->criarRelatorio($visita, $autor);

        $this->actingAs($autor)
            ->get(route('visitas.relatorios.edit', [$visita, $relatorio]))
            ->assertRedirect(route('visitas.relatorios.index', $visita))
            ->assertSessionHas('mensagem_erro', 'Não é possível editar relatório de visita cancelada.');
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

    private function criarVisita(User $criador, VisitaStatus $status = VisitaStatus::Agendada): Visita
    {
        $hospital = $this->criarHospital();

        return Visita::query()->create([
            'hospital_id'   => $hospital->id,
            'criado_por_id' => $criador->id,
            'lider_id'      => $criador->id,
            'inicio_em'     => '2026-06-15 10:00:00',
            'fim_em'        => '2026-06-15 12:00:00',
            'tipo'          => VisitaTipo::Hospital,
            'status'        => $status,
            'origem'        => VisitaOrigem::Sistema,
        ]);
    }
}
