<?php

namespace Tests\Feature\Dashboard\Visita\Hospital;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
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
use App\Models\VisitaParticipante;
use App\Models\VisitaRelatorio;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IndicadoresTest extends TestCase
{
    use RefreshDatabase;

    public function test_contabiliza_visitas_sem_relatorio_sem_duplicar_multiplos_relatorios(): void
    {
        $cidade = $this->criarCidade('Porto Alegre');
        $hospital = $this->criarHospital($cidade, 'Hospital Central');
        $ala = Ala::query()->create(['hospital_id' => $hospital->id, 'nome' => 'Pediatria']);
        $administrador = $this->criarUsuario('administrador');
        $participanteA = User::factory()->create();
        $participanteB = User::factory()->create();

        $comRelatorios = $this->criarVisita($hospital, $administrador, VisitaStatus::Realizada, $ala->id, '2026-03-10 10:00:00');
        $semRelatorio = $this->criarVisita($hospital, $administrador, VisitaStatus::PendenteRelatorio, null, '2026-04-10 10:00:00');
        $cancelada = $this->criarVisita($hospital, $administrador, VisitaStatus::Cancelada, null, '2026-04-12 10:00:00');

        $this->criarParticipacao($comRelatorios, $participanteA, StatusParticipacao::Confirmado);
        $this->criarParticipacao($comRelatorios, $participanteB, StatusParticipacao::Pendente);
        $this->criarParticipacao($semRelatorio, $participanteA, StatusParticipacao::Confirmado);
        $this->criarParticipacao($cancelada, $participanteB, StatusParticipacao::Confirmado);
        $this->criarRelatorio($comRelatorios, $administrador, 10);
        $this->criarRelatorio($comRelatorios, $participanteA, 30);

        $this->actingAs($administrador)
            ->get(route('dashboards.visitas-por-hospital', [
                'mes_inicio' => '2026-03',
                'mes_fim' => '2026-04',
                'cidade_id' => $cidade->id,
                'hospital_id' => $hospital->id,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Visita/Hospital/Index')
                ->where('indicadores.total_visitas', 2)
                ->where('indicadores.hospitais_visitados', 1)
                ->where('indicadores.total_participacoes', 2)
                ->where('indicadores.media_participantes', 1)
                ->where('indicadores.impacto_estimado', 20)
                ->where('indicadores.visitas_com_impacto', 1)
                ->where('indicadores.visitas_sem_impacto', 1)
                ->where('evolucao.0.total', 1)
                ->where('evolucao.1.total', 1)
                ->has('detalhes.visitas.data', 2)
                ->where('detalhes.alas.1.nome', 'Sem ala informada'));
    }

    public function test_filtros_de_cidade_hospital_e_ala_restringem_os_dados(): void
    {
        $cidadeA = $this->criarCidade('Porto Alegre');
        $cidadeB = $this->criarCidade('Santa Maria');
        $hospitalA = $this->criarHospital($cidadeA, 'Hospital A');
        $hospitalB = $this->criarHospital($cidadeB, 'Hospital B');
        $alaA = Ala::query()->create(['hospital_id' => $hospitalA->id, 'nome' => 'Ala A']);
        $alaB = Ala::query()->create(['hospital_id' => $hospitalA->id, 'nome' => 'Ala B']);
        $administrador = $this->criarUsuario('administrador');
        $this->criarVisita($hospitalA, $administrador, VisitaStatus::Realizada, $alaA->id, '2026-03-10 10:00:00');
        $this->criarVisita($hospitalA, $administrador, VisitaStatus::Realizada, $alaB->id, '2026-03-11 10:00:00');
        $this->criarVisita($hospitalB, $administrador, VisitaStatus::Realizada, null, '2026-03-10 10:00:00');

        $this->actingAs($administrador)
            ->get(route('dashboards.visitas-por-hospital', [
                'mes_inicio' => '2026-03',
                'mes_fim' => '2026-03',
                'cidade_id' => $cidadeA->id,
                'hospital_id' => $hospitalA->id,
                'ala_id' => $alaA->id,
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('indicadores.total_visitas', 1)
                ->has('hospitais', 1)
                ->where('hospitais.0.id', $hospitalA->id)
                ->has('opcoes.hospitais', 1)
                ->has('opcoes.alas', 2));
    }

    public function test_coordenador_local_nao_consulta_outra_cidade(): void
    {
        $cidadeA = $this->criarCidade('Porto Alegre');
        $cidadeB = $this->criarCidade('Santa Maria');
        $coordenador = $this->criarUsuario('coordenador_local', $cidadeA->id);

        $this->actingAs($coordenador)
            ->get(route('dashboards.visitas-por-hospital', [
                'mes_inicio' => '2026-03',
                'mes_fim' => '2026-03',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filtros.cidade_id', $cidadeA->id)
                ->where('escopo_global', false)
                ->has('opcoes.cidades', 1));

        $this->actingAs($coordenador)
            ->get(route('dashboards.visitas-por-hospital', [
                'mes_inicio' => '2026-03',
                'mes_fim' => '2026-03',
                'cidade_id' => $cidadeB->id,
            ]))
            ->assertForbidden();
    }

    private function criarUsuario(string $cargoSlug, ?int $cidadeId = null): User
    {
        $voluntario = Voluntario::query()->create([
            'nome_completo' => 'Usuário '.uniqid(),
            'email' => uniqid().'@example.com',
            'cidade_base_id' => $cidadeId,
            'status' => 'ativo',
        ]);
        $user = User::factory()->create(['voluntario_id' => $voluntario->id]);
        $cargo = Cargo::query()->firstOrCreate(['slug' => $cargoSlug], ['nome' => $cargoSlug]);
        $user->cargos()->attach($cargo);
        $user->unsetRelation('cargos');

        return $user;
    }

    private function criarCidade(string $nome): Cidade
    {
        $estado = Estado::query()->firstOrCreate(['sigla' => 'RS'], ['nome' => 'Rio Grande do Sul']);
        return Cidade::query()->forceCreate(['nome' => $nome, 'estado_id' => $estado->id]);
    }

    private function criarHospital(Cidade $cidade, string $nome): Hospital
    {
        return Hospital::query()->create([
            'cidade_id' => $cidade->id,
            'nome' => $nome,
            'cnpj' => str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT),
            'endereco' => 'Rua Teste, 1',
            'telefone' => '51999999999',
            'email' => uniqid().'@hospital.com',
            'ativo' => true,
        ]);
    }

    private function criarVisita(Hospital $hospital, User $criador, VisitaStatus $status, ?int $alaId, string $inicio): Visita
    {
        return Visita::query()->create([
            'hospital_id' => $hospital->id,
            'ala_unidade_id' => $alaId,
            'criado_por_id' => $criador->id,
            'inicio_em' => $inicio,
            'fim_em' => date('Y-m-d H:i:s', strtotime($inicio.' +2 hours')),
            'tipo' => VisitaTipo::Hospital,
            'status' => $status,
            'origem' => VisitaOrigem::Sistema,
        ]);
    }

    private function criarParticipacao(Visita $visita, User $user, StatusParticipacao $status): void
    {
        VisitaParticipante::query()->create([
            'visita_id' => $visita->id,
            'voluntario_id' => $user->id,
            'tipo_participacao' => TipoParticipacao::Palhaco,
            'papel_na_visita' => PapelNaVisita::Participante,
            'status_participacao' => $status,
        ]);
    }

    private function criarRelatorio(Visita $visita, User $autor, int $impacto): void
    {
        VisitaRelatorio::query()->create([
            'visita_id' => $visita->id,
            'autor_id' => $autor->id,
            'tipo_relatorio' => TipoRelatorio::Geral,
            'resumo' => 'Relatório de teste',
            'pessoas_impactadas' => $impacto,
            'enviado_em' => now(),
            'fora_do_prazo' => false,
        ]);
    }
}
