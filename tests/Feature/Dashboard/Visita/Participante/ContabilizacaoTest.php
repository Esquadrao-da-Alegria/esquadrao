<?php

namespace Tests\Feature\Dashboard\Visita\Participante;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
use App\Enums\TipoRelatorio;
use App\Enums\TipoAjusteContabilizacao;
use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Evento;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaParticipante;
use App\Models\VisitaRelatorio;
use App\Models\VisitaAjusteContabilizacao;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ContabilizacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_busca_filtra_participantes_por_nome_ou_email(): void
    {
        $cidade = $this->criarCidade('Porto Alegre');
        $gestor = $this->criarUsuario('administrador', $cidade);
        $encontrado = $this->criarUsuario('voluntario', $cidade);
        $this->criarUsuario('voluntario', $cidade);
        $encontrado->update(['name' => 'Maria do Sorriso']);

        $this->actingAs($gestor)
            ->get(route('dashboards.visitas-por-participante', [
                'periodo_tipo' => 'ano', 'ano' => 2026, 'busca' => 'Maria',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('participantes.data', 1)
                ->where('participantes.data.0.id', $encontrado->id));
    }

    public function test_visita_exige_relatorio_no_prazo_do_proprio_participante(): void
    {
        $cidade = $this->criarCidade('Porto Alegre');
        $gestor = $this->criarUsuario('administrador', $cidade);
        $voluntario = $this->criarUsuario('voluntario', $cidade);
        $outro = $this->criarUsuario('voluntario', $cidade);
        $hospital = $this->criarHospital($cidade);

        $semRelatorioProprio = $this->criarVisita($hospital, $gestor, '2026-03-10 10:00:00');
        $comRelatorioProprio = $this->criarVisita($hospital, $gestor, '2026-03-15 10:00:00');
        $this->participar($semRelatorioProprio, $voluntario);
        $this->participar($comRelatorioProprio, $voluntario);
        $this->relatar($semRelatorioProprio, $outro, false);
        $this->relatar($comRelatorioProprio, $voluntario, false);
        $this->relatar($comRelatorioProprio, $voluntario, false);

        $this->actingAs($gestor)
            ->get(route('dashboards.visitas-por-participante', [
                'periodo_tipo' => 'mes', 'ano' => 2026, 'mes' => 3,
                'participante_id' => $voluntario->id,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Visita/Participante/Index')
                ->has('participantes.data', 1)
                ->where('participantes.data.0.visitas_validas', 1)
                ->where('participantes.data.0.relatorios_pendentes', 1));
    }

    public function test_relatorio_de_palhaco_valida_o_grupo_mas_nao_valida_paisanas(): void
    {
        $cidade = $this->criarCidade('Porto Alegre');
        $gestor = $this->criarUsuario('administrador', $cidade);
        $palhacoAutor = $this->criarUsuario('voluntario', $cidade);
        $palhacoSemRelatorio = $this->criarUsuario('voluntario', $cidade);
        $paisanaSemRelatorio = $this->criarUsuario('voluntario', $cidade);
        $visita = $this->criarVisita($this->criarHospital($cidade), $gestor, '2026-03-15 10:00:00');
        $this->participar($visita, $palhacoAutor, TipoParticipacao::Palhaco);
        $this->participar($visita, $palhacoSemRelatorio, TipoParticipacao::Palhaco);
        $this->participar($visita, $paisanaSemRelatorio, TipoParticipacao::Paisana);
        $this->relatar($visita, $palhacoAutor, false);

        foreach ([$palhacoAutor, $palhacoSemRelatorio] as $palhaco) {
            $this->actingAs($gestor)
                ->get(route('dashboards.visitas-por-participante', [
                    'periodo_tipo' => 'mes', 'ano' => 2026, 'mes' => 3,
                    'participante_id' => $palhaco->id,
                ]))
                ->assertInertia(fn (Assert $page) => $page
                    ->where('participantes.data.0.visitas_validas', 1));
        }

        $this->actingAs($gestor)
            ->get(route('dashboards.visitas-por-participante', [
                'periodo_tipo' => 'mes', 'ano' => 2026, 'mes' => 3,
                'participante_id' => $paisanaSemRelatorio->id,
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('participantes.data.0.visitas_validas', 0)
                ->where('participantes.data.0.relatorios_pendentes', 1));
    }

    public function test_cada_paisana_precisa_do_proprio_relatorio(): void
    {
        $cidade = $this->criarCidade('Porto Alegre');
        $gestor = $this->criarUsuario('administrador', $cidade);
        $paisanaAutor = $this->criarUsuario('voluntario', $cidade);
        $paisanaSemRelatorio = $this->criarUsuario('voluntario', $cidade);
        $visita = $this->criarVisita($this->criarHospital($cidade), $gestor, '2026-03-15 10:00:00');
        $this->participar($visita, $paisanaAutor, TipoParticipacao::Paisana);
        $this->participar($visita, $paisanaSemRelatorio, TipoParticipacao::Paisana);
        $this->relatar($visita, $paisanaAutor, false);

        foreach ([[$paisanaAutor, 1], [$paisanaSemRelatorio, 0]] as [$paisana, $esperado]) {
            $this->actingAs($gestor)
                ->get(route('dashboards.visitas-por-participante', [
                    'periodo_tipo' => 'mes', 'ano' => 2026, 'mes' => 3,
                    'participante_id' => $paisana->id,
                ]))
                ->assertInertia(fn (Assert $page) => $page
                    ->where('participantes.data.0.visitas_validas', $esperado));
        }
    }

    public function test_relatorio_fora_do_prazo_e_visita_nao_realizada_nao_validam_o_grupo(): void
    {
        $cidade = $this->criarCidade('Porto Alegre');
        $gestor = $this->criarUsuario('administrador', $cidade);
        $autor = $this->criarUsuario('voluntario', $cidade);
        $palhaco = $this->criarUsuario('voluntario', $cidade);
        $hospital = $this->criarHospital($cidade);
        $foraDoPrazo = $this->criarVisita($hospital, $gestor, '2026-03-10 10:00:00');
        $pendente = $this->criarVisita($hospital, $gestor, '2026-03-15 10:00:00', VisitaStatus::PendenteRelatorio);

        foreach ([$foraDoPrazo, $pendente] as $visita) {
            $this->participar($visita, $autor);
            $this->participar($visita, $palhaco);
        }
        $this->relatar($foraDoPrazo, $autor, true);
        $this->relatar($pendente, $autor, false);

        $this->actingAs($gestor)
            ->get(route('dashboards.visitas-por-participante', [
                'periodo_tipo' => 'mes', 'ano' => 2026, 'mes' => 3,
                'participante_id' => $palhaco->id,
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('participantes.data.0.visitas_validas', 0)
                ->where('participantes.data.0.relatorios_fora_prazo', 1));
    }

    public function test_aceite_administrativo_de_relatorio_atrasado_valida_grupo_de_palhacos(): void
    {
        $cidade = $this->criarCidade('Porto Alegre');
        $gestor = $this->criarUsuario('administrador', $cidade);
        $autor = $this->criarUsuario('voluntario', $cidade);
        $palhaco = $this->criarUsuario('voluntario', $cidade);
        $visita = $this->criarVisita($this->criarHospital($cidade), $gestor, '2026-03-10 10:00:00');
        $this->participar($visita, $autor);
        $this->participar($visita, $palhaco);
        $this->relatar($visita, $autor, true);
        $relatorio = VisitaRelatorio::query()->where('visita_id', $visita->id)->firstOrFail();
        VisitaAjusteContabilizacao::query()->create([
            'visita_id' => $visita->id, 'voluntario_id' => $autor->id, 'relatorio_id' => $relatorio->id,
            'administrador_id' => $gestor->id, 'tipo' => TipoAjusteContabilizacao::AceiteRelatorioForaPrazo,
            'tipo_participacao' => TipoParticipacao::Palhaco, 'justificativa' => 'Falha operacional confirmada pelo administrador.',
        ]);

        $this->actingAs($gestor)->get(route('dashboards.visitas-por-participante', [
            'periodo_tipo' => 'mes', 'ano' => 2026, 'mes' => 3, 'participante_id' => $palhaco->id,
        ]))->assertInertia(fn (Assert $page) => $page
            ->where('participantes.data.0.visitas_validas', 1)
            ->where('participantes.data.0.relatorios_fora_prazo', 0));
    }

    public function test_coordenador_local_nao_abre_historico_de_outra_cidade(): void
    {
        $cidadeA = $this->criarCidade('Porto Alegre');
        $cidadeB = $this->criarCidade('Santa Maria');
        $coordenador = $this->criarUsuario('coordenador_local', $cidadeA);
        $voluntario = $this->criarUsuario('voluntario', $cidadeB);

        $this->actingAs($coordenador)
            ->get(route('dashboards.visitas-por-participante.show', [
                'voluntario' => $voluntario,
                'periodo_tipo' => 'ano', 'ano' => 2026,
            ]))
            ->assertForbidden();
    }

    public function test_percentual_considera_eventos_finalizados_da_cidade_e_somente_presenca(): void
    {
        $cidade = $this->criarCidade('Porto Alegre');
        $gestor = $this->criarUsuario('administrador', $cidade);
        $voluntario = $this->criarUsuario('voluntario', $cidade);
        $reuniaoPresente = $this->criarEvento($cidade, $gestor, 'reuniao', '2026-03-10 10:00:00');
        $this->criarEvento($cidade, $gestor, 'reuniao', '2026-04-10 10:00:00');
        $oficina = $this->criarEvento($cidade, $gestor, 'oficina', '2026-05-10 10:00:00');
        $reuniaoPresente->participantes()->attach($voluntario->id, ['status' => 'inscrito', 'presenca' => 'presente']);
        $oficina->participantes()->attach($voluntario->id, ['status' => 'inscrito', 'presenca' => 'presente']);

        $this->actingAs($gestor)
            ->get(route('dashboards.visitas-por-participante', [
                'periodo_tipo' => 'semestre', 'ano' => 2026, 'semestre' => 1,
                'participante_id' => $voluntario->id,
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('participantes.data.0.reunioes.oferecidos', 2)
                ->where('participantes.data.0.reunioes.presencas', 1)
                ->where('participantes.data.0.reunioes.percentual', 50)
                ->where('participantes.data.0.oficinas.percentual', 100));
    }

    private function criarUsuario(string $slug, Cidade $cidade): User
    {
        $voluntario = Voluntario::query()->create(['nome_completo' => uniqid('Voluntário '), 'email' => uniqid().'@teste.com', 'cidade_base_id' => $cidade->id, 'status' => 'ativo']);
        $user = User::factory()->create(['voluntario_id' => $voluntario->id]);
        $cargo = Cargo::query()->firstOrCreate(['slug' => $slug], ['nome' => $slug]);
        $user->cargos()->attach($cargo);
        $user->unsetRelation('cargos');
        return $user;
    }

    private function criarCidade(string $nome): Cidade
    {
        $estado = Estado::query()->firstOrCreate(['sigla' => 'RS'], ['nome' => 'Rio Grande do Sul']);
        return Cidade::query()->forceCreate(['nome' => $nome, 'estado_id' => $estado->id]);
    }

    private function criarHospital(Cidade $cidade): Hospital
    {
        return Hospital::query()->create(['cidade_id' => $cidade->id, 'nome' => 'Hospital', 'cnpj' => '12345678000199', 'endereco' => 'Rua 1', 'telefone' => '51999999999', 'email' => uniqid().'@hospital.com', 'ativo' => true]);
    }

    private function criarEvento(Cidade $cidade, User $gestor, string $tipo, string $inicio): Evento
    {
        return Evento::query()->create([
            'titulo' => ucfirst($tipo),
            'tipo' => $tipo,
            'cidade_id' => $cidade->id,
            'data_inicio' => $inicio,
            'data_fim' => date('Y-m-d H:i:s', strtotime($inicio.' +2 hours')),
            'status' => 'finalizado',
            'criado_por_id' => $gestor->id,
        ]);
    }

    private function criarVisita(Hospital $hospital, User $gestor, string $inicio, VisitaStatus $status = VisitaStatus::Realizada): Visita
    {
        return Visita::query()->create(['hospital_id' => $hospital->id, 'criado_por_id' => $gestor->id, 'inicio_em' => $inicio, 'fim_em' => date('Y-m-d H:i:s', strtotime($inicio.' +2 hours')), 'tipo' => VisitaTipo::Hospital, 'status' => $status, 'origem' => VisitaOrigem::Sistema]);
    }

    private function participar(Visita $visita, User $user, TipoParticipacao $tipo = TipoParticipacao::Palhaco): void
    {
        VisitaParticipante::query()->create(['visita_id' => $visita->id, 'voluntario_id' => $user->id, 'tipo_participacao' => $tipo, 'papel_na_visita' => PapelNaVisita::Participante, 'status_participacao' => StatusParticipacao::Confirmado]);
    }

    private function relatar(Visita $visita, User $autor, bool $foraPrazo): void
    {
        VisitaRelatorio::query()->create(['visita_id' => $visita->id, 'autor_id' => $autor->id, 'tipo_relatorio' => TipoRelatorio::Geral, 'resumo' => 'Teste', 'enviado_em' => now(), 'fora_do_prazo' => $foraPrazo]);
    }
}
