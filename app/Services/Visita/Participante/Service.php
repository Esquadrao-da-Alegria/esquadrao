<?php

namespace App\Services\Visita\Participante;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
use App\Enums\VisitaStatus;
use App\Models\Visita;
use App\Queries\Visita\Participante\Queries;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Service
{
    public const LIMITE_PARTICIPANTES = 5;

    public function __construct(private Queries $queries) {}

    public function store(Visita $visita, array $dados): array
    {
        try {
            $tipo = $dados['tipo_participacao'] ?? null;

            if (! in_array($tipo, [TipoParticipacao::Palhaco->value, TipoParticipacao::Paisana->value], true)) {
                return $this->erro('Tipo de participação inválido.');
            }

            if ($visita->status !== VisitaStatus::Agendada) {
                return $this->erro('Esta visita não está aberta para inscrições.');
            }

            $voluntarioId = Auth::id();

            if ($this->usuarioJaInscrito($visita->id, $voluntarioId)) {
                return $this->erro('Você já está inscrito nesta visita.');
            }

            if ($this->totalParticipantesAtivos($visita->id) >= self::LIMITE_PARTICIPANTES) {
                return $this->erro('Visita atingiu limite de participantes');
            }

            return $this->queries->store([
                'visita_id'           => $visita->id,
                'voluntario_id'       => $voluntarioId,
                'tipo_participacao'   => $tipo,
                'papel_na_visita'     => PapelNaVisita::Participante->value,
                'status_participacao' => StatusParticipacao::Confirmado->value,
            ]);
        } catch (\Throwable $th) {
            $this->logarErro(['visita_id' => $visita->id, ...$dados], formatarMensagemErro($th));

            return $this->erro(formatarMensagemErro($th));
        }
    }

    private function usuarioJaInscrito(int $visitaId, int $voluntarioId): bool
    {
        $retorno = $this->queries->index([
            ...$this->filtrosParticipanteAtivo($visitaId),
            'voluntario_id' => $voluntarioId,
        ]);

        return $retorno['sucesso'] && $retorno['dados'] !== null;
    }

    private function totalParticipantesAtivos(int $visitaId): int
    {
        $retorno = $this->queries->index([
            ...$this->filtrosParticipanteAtivo($visitaId),
            'retornar_lista' => true,
        ]);

        if (! $retorno['sucesso']) {
            return 0;
        }

        return $retorno['dados']->count();
    }

    private function filtrosParticipanteAtivo(int $visitaId): array
    {
        return [
            'visita_id'              => $visitaId,
            'papel_na_visita'        => PapelNaVisita::Participante->value,
            'status_participacao_in' => [
                StatusParticipacao::Confirmado->value,
                StatusParticipacao::Pendente->value,
            ],
        ];
    }

    private function erro(string $mensagem): array
    {
        return ['sucesso' => false, 'dados' => [], 'erros' => [$mensagem]];
    }

    private function logarErro(array $dados, string $mensagemErro): void
    {
        Log::error('Erro ao inscrever participante na visita!', [
            'sucesso' => false,
            'dados'   => $dados,
            'erros'   => [$mensagemErro],
        ]);
    }
}
