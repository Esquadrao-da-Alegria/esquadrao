<?php

namespace App\Services\Visita\Ajuste;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoAjusteContabilizacao;
use App\Enums\TipoParticipacao;
use App\Enums\VisitaStatus;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaAjusteContabilizacao;
use App\Models\VisitaParticipante;
use App\Models\VisitaRelatorio;
use App\Queries\Visita\Ajuste\Queries;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Service
{
    public function __construct(private Queries $queries) {}

    public function index(Visita $visita): array
    {
        return [
            'visita' => $visita->loadMissing(['hospital:id,nome,cidade_id', 'alaUnidade:id,nome,hospital_id']),
            'ajustes' => $this->queries->index($visita),
            'voluntarios' => User::query()
                ->whereNotNull('voluntario_id')
                ->whereHas('voluntario', fn ($query) => $query->where('status', 'ativo'))
                ->orderBy('name')
                ->get(['id', 'name']),
            'relatorios_atrasados' => VisitaRelatorio::query()
                ->with('autor:id,name')
                ->where('visita_id', $visita->id)
                ->where('fora_do_prazo', true)
                ->whereDoesntHave('ajustesContabilizacao', fn ($query) => $query->where('tipo', TipoAjusteContabilizacao::AceiteRelatorioForaPrazo->value))
                ->orderBy('enviado_em')
                ->get(['id', 'visita_id', 'autor_id', 'enviado_em', 'fora_do_prazo']),
        ];
    }

    public function store(Visita $visita, User $administrador, array $dados): VisitaAjusteContabilizacao
    {
        $this->validarAdministrador($administrador);

        if ($visita->status !== VisitaStatus::Realizada) {
            throw ValidationException::withMessages(['visita' => 'Somente visitas realizadas podem receber ajustes de contabilização.']);
        }

        return DB::transaction(function () use ($visita, $administrador, $dados) {
            $tipo = TipoAjusteContabilizacao::from($dados['tipo']);

            return $tipo === TipoAjusteContabilizacao::CorrecaoParticipacao
                ? $this->corrigirParticipacao($visita, $administrador, $dados)
                : $this->aceitarRelatorio($visita, $administrador, $dados);
        });
    }

    private function corrigirParticipacao(Visita $visita, User $administrador, array $dados): VisitaAjusteContabilizacao
    {
        $voluntario = User::query()->whereNotNull('voluntario_id')->findOrFail($dados['voluntario_id']);
        $this->validarConflitoInteresse($administrador, $voluntario);
        $participacao = VisitaParticipante::query()
            ->where('visita_id', $visita->id)
            ->where('voluntario_id', $voluntario->id)
            ->first();
        $anteriores = $participacao?->only(['tipo_participacao', 'papel_na_visita', 'status_participacao']);
        $posteriores = [
            'tipo_participacao' => TipoParticipacao::from($dados['tipo_participacao'])->value,
            'papel_na_visita' => $participacao?->papel_na_visita?->value ?? PapelNaVisita::Participante->value,
            'status_participacao' => StatusParticipacao::Confirmado->value,
        ];

        if ($participacao) {
            $participacao->update($posteriores);
        } else {
            $participacao = VisitaParticipante::query()->create([
                'visita_id' => $visita->id,
                'voluntario_id' => $voluntario->id,
                ...$posteriores,
            ]);
        }

        return VisitaAjusteContabilizacao::query()->create([
            'visita_id' => $visita->id,
            'voluntario_id' => $voluntario->id,
            'administrador_id' => $administrador->id,
            'tipo' => TipoAjusteContabilizacao::CorrecaoParticipacao,
            'tipo_participacao' => $posteriores['tipo_participacao'],
            'justificativa' => $dados['justificativa'],
            'dados_anteriores' => $anteriores,
            'dados_posteriores' => $posteriores,
        ]);
    }

    private function aceitarRelatorio(Visita $visita, User $administrador, array $dados): VisitaAjusteContabilizacao
    {
        $relatorio = VisitaRelatorio::query()->where('visita_id', $visita->id)->findOrFail($dados['relatorio_id']);
        $autor = User::query()->findOrFail($relatorio->autor_id);
        $this->validarConflitoInteresse($administrador, $autor);

        $participacao = VisitaParticipante::query()
            ->where('visita_id', $visita->id)
            ->where('voluntario_id', $autor->id)
            ->where('status_participacao', StatusParticipacao::Confirmado->value)
            ->first();

        if (VisitaAjusteContabilizacao::query()->where('relatorio_id', $relatorio->id)->where('tipo', TipoAjusteContabilizacao::AceiteRelatorioForaPrazo->value)->exists()) {
            throw ValidationException::withMessages(['relatorio_id' => 'Este relatório já possui aceite administrativo.']);
        }

        $tipoParticipacao = $participacao?->tipo_participacao?->value ?? TipoParticipacao::Palhaco->value;

        return VisitaAjusteContabilizacao::query()->create([
            'visita_id' => $visita->id,
            'voluntario_id' => $autor->id,
            'relatorio_id' => $relatorio->id,
            'administrador_id' => $administrador->id,
            'tipo' => TipoAjusteContabilizacao::AceiteRelatorioForaPrazo,
            'tipo_participacao' => $tipoParticipacao,
            'justificativa' => $dados['justificativa'],
            'dados_anteriores' => ['fora_do_prazo' => true, 'aceito_para_contabilizacao' => false],
            'dados_posteriores' => ['fora_do_prazo' => true, 'aceito_para_contabilizacao' => true],
        ]);
    }

    private function validarAdministrador(User $user): void
    {
        $user->loadMissing('cargos');
        abort_unless($user->cargos->contains('slug', 'administrador'), 403);
    }

    private function validarConflitoInteresse(User $administrador, User $voluntario): void
    {
        // Permite que administradores registrem ajustes para qualquer voluntário/relatório, incluindo si mesmos.
    }
}
