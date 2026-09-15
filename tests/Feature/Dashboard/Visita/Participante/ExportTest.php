<?php

namespace Tests\Feature\Dashboard\Visita\Participante;

use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\Estado;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaParticipante;
use App\Models\Voluntario;
use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_convidado_e_redirecionado_para_login(): void
    {
        $this->get('/dashboards/visitas-por-participante/exportar/csv')
            ->assertRedirect('/login');
    }

    public function test_voluntario_sem_permissao_recebe_403(): void
    {
        $cidade = $this->criarCidade();
        $voluntario = $this->criarUsuario('voluntario', $cidade);

        $this->actingAs($voluntario)
            ->get('/dashboards/visitas-por-participante/exportar/csv')
            ->assertForbidden();
    }

    public function test_formato_invalido_retorna_422(): void
    {
        $cidade = $this->criarCidade();
        $gestor = $this->criarUsuario('administrador', $cidade);

        $this->actingAs($gestor)
            ->get('/dashboards/visitas-por-participante/exportar/docx?periodo_tipo=ano&ano=2026')
            ->assertStatus(422);
    }

    public function test_exporta_csv_com_cabecalho_correto(): void
    {
        $cidade = $this->criarCidade();
        $gestor = $this->criarUsuario('administrador', $cidade);

        $response = $this->actingAs($gestor)
            ->get('/dashboards/visitas-por-participante/exportar/csv?periodo_tipo=ano&ano=2026');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Voluntário', $response->streamedContent());
    }

    public function test_exporta_xlsx_com_status_200(): void
    {
        $cidade = $this->criarCidade();
        $gestor = $this->criarUsuario('administrador', $cidade);

        $response = $this->actingAs($gestor)
            ->get('/dashboards/visitas-por-participante/exportar/xlsx?periodo_tipo=ano&ano=2026');

        $response->assertOk();
    }

    public function test_csv_inclui_dados_do_voluntario(): void
    {
        $cidade = $this->criarCidade();
        $gestor = $this->criarUsuario('administrador', $cidade);
        $voluntario = $this->criarUsuario('voluntario', $cidade, 'Voluntário Teste CSV');

        $hospital = $this->criarHospital($cidade);
        $visita = $this->criarVisita($hospital, $gestor, '2026-06-10 10:00:00', VisitaStatus::Contabilizada);
        $this->participar($visita, $voluntario);

        $response = $this->actingAs($gestor)
            ->get('/dashboards/visitas-por-participante/exportar/csv?periodo_tipo=ano&ano=2026');

        $response->assertOk();
        $conteudo = $response->streamedContent();
        $this->assertStringContainsString('Voluntário Teste CSV', $conteudo);
    }

    public function test_filtro_cidade_restringe_dados_no_csv(): void
    {
        $cidade1 = $this->criarCidade('Cidade A');
        $cidade2 = $this->criarCidade('Cidade B');
        $gestor = $this->criarUsuario('administrador', $cidade1);
        $this->criarUsuario('voluntario', $cidade1, 'Voluntário Cidade A');
        $this->criarUsuario('voluntario', $cidade2, 'Voluntário Cidade B');

        $response = $this->actingAs($gestor)
            ->get("/dashboards/visitas-por-participante/exportar/csv?periodo_tipo=ano&ano=2026&cidade_id={$cidade1->id}");

        $response->assertOk();
        $conteudo = $response->streamedContent();
        $this->assertStringContainsString('Voluntário Cidade A', $conteudo);
        $this->assertStringNotContainsString('Voluntário Cidade B', $conteudo);
    }

    public function test_csv_preserva_caracteres_especiais(): void
    {
        $cidade = $this->criarCidade('São Paulo');
        $gestor = $this->criarUsuario('administrador', $cidade);
        $this->criarUsuario('voluntario', $cidade, 'João Müller Ação');

        $response = $this->actingAs($gestor)
            ->get('/dashboards/visitas-por-participante/exportar/csv?periodo_tipo=ano&ano=2026');

        $response->assertOk();
        $conteudo = $response->streamedContent();
        $this->assertStringContainsString('João Müller Ação', $conteudo);
        $this->assertStringContainsString('São Paulo', $conteudo);
    }

    private function criarUsuario(string $slug, Cidade $cidade, string $nome = null): User
    {
        $voluntario = Voluntario::query()->create([
            'nome_completo' => $nome ?? uniqid('Voluntário '),
            'email' => uniqid() . '@teste.com',
            'cidade_base_id' => $cidade->id,
            'status' => 'ativo',
            'data_entrada_ong' => '2026-01-01',
        ]);
        $user = User::factory()->create([
            'voluntario_id' => $voluntario->id,
            'created_at' => '2026-01-01 00:00:00',
        ]);
        $cargo = Cargo::query()->firstOrCreate(['slug' => $slug], ['nome' => $slug]);
        $user->cargos()->attach($cargo);
        $user->unsetRelation('cargos');
        return $user;
    }

    private function criarCidade(string $nome = 'Porto Alegre'): Cidade
    {
        $estado = Estado::query()->firstOrCreate(['sigla' => 'RS'], ['nome' => 'Rio Grande do Sul']);
        return Cidade::query()->forceCreate(['nome' => $nome, 'estado_id' => $estado->id]);
    }

    private function criarHospital(Cidade $cidade): Hospital
    {
        return Hospital::query()->create([
            'cidade_id' => $cidade->id,
            'nome' => 'Hospital Teste',
            'cnpj' => '12345678000199',
            'endereco' => 'Rua 1',
            'telefone' => '51999999999',
            'email' => uniqid() . '@hospital.com',
            'ativo' => true,
        ]);
    }

    private function criarVisita(Hospital $hospital, User $gestor, string $inicio, VisitaStatus $status = VisitaStatus::Realizada): Visita
    {
        return Visita::query()->create([
            'hospital_id' => $hospital->id,
            'criado_por_id' => $gestor->id,
            'inicio_em' => $inicio,
            'fim_em' => date('Y-m-d H:i:s', strtotime($inicio . ' +2 hours')),
            'tipo' => VisitaTipo::Hospital,
            'status' => $status,
            'origem' => VisitaOrigem::Sistema,
        ]);
    }

    private function participar(Visita $visita, User $user, TipoParticipacao $tipo = TipoParticipacao::Palhaco): void
    {
        VisitaParticipante::query()->create([
            'visita_id' => $visita->id,
            'voluntario_id' => $user->id,
            'tipo_participacao' => $tipo,
            'papel_na_visita' => PapelNaVisita::Participante,
            'status_participacao' => StatusParticipacao::Confirmado,
        ]);
    }
}
