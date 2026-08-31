<?php

namespace App\Services\Visita\Participante;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
use App\Enums\VisitaStatus;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaParticipante;
use App\Queries\Visita\Participante\Queries;
use App\Services\Visita\Service as VisitaService;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __construct(
        private Queries $queries,
        private VisitaService $visitaService,
    ) {}

    public function store(Visita $visita, array $dados): array
    {
        try {
            $tipo = $dados['tipo_participacao'] ?? null;

            if (! in_array($tipo, [TipoParticipacao::Palhaco->value, TipoParticipacao::Paisana->value], true)) {
                return $this->erro('Tipo de participação inválido.');
            }

            $usuario = usuarioAutenticado();

            if (! $usuario) {
                return $this->erro('Usuário não autenticado.');
            }

            $solicitadoVoluntarioId = isset($dados['voluntario_id']) ? (int) $dados['voluntario_id'] : null;
            $ehGestorEditando = $solicitadoVoluntarioId !== null && $this->visitaService->podeEditarVisita($usuario, $visita);

            if (! $ehGestorEditando) {
                if ($visita->status !== VisitaStatus::Agendada || ($visita->fim_em && $visita->fim_em->isPast())) {
                    return $this->erro('Esta visita não está aberta para inscrições.');
                }

                if ($usuario->status !== User::STATUS_ATIVO || $usuario->voluntario_id === null) {
                    return $this->erro('Apenas voluntários ativos podem se inscrever.');
                }

                $voluntarioModel = $usuario->voluntario;
                if ($voluntarioModel && $voluntarioModel->estaAfastado($visita->inicio_em)) {
                    return $this->erro('Voluntário está afastado temporariamente no período desta visita.');
                }

                $voluntarioId = $usuario->id;
            } else {
                $targetUser = User::query()->find($solicitadoVoluntarioId);
                if (! $targetUser || $targetUser->status !== User::STATUS_ATIVO || $targetUser->voluntario_id === null) {
                    return $this->erro('Voluntário selecionado é inválido ou inativo.');
                }

                $voluntarioModel = $targetUser->voluntario;
                if ($voluntarioModel && $voluntarioModel->estaAfastado($visita->inicio_em)) {
                    return $this->erro('Voluntário está afastado temporariamente no período desta visita.');
                }

                $voluntarioId = $targetUser->id;
            }

            if ($this->usuarioJaInscrito($visita->id, $voluntarioId)) {
                return $this->erro('Voluntário já está inscrito nesta visita.');
            }

            $participacaoCancelada = $this->buscarParticipacaoCancelada($visita->id, $voluntarioId);

            if ($participacaoCancelada !== null) {
                $contagem = $this->totalParticipantesAtivos($visita->id);

                if (! $contagem['sucesso']) {
                    return $this->erro('Não foi possível validar as vagas desta visita. Tente novamente.');
                }

                if ($visita->limite_participantes !== null && $contagem['total'] >= $visita->limite_participantes) {
                    return $this->erro('Visita atingiu limite de participantes');
                }

                return $this->queries->update($participacaoCancelada->id, [
                    'tipo_participacao'   => $tipo,
                    'status_participacao' => StatusParticipacao::Confirmado->value,
                ]);
            }

            $contagem = $this->totalParticipantesAtivos($visita->id);

            if (! $contagem['sucesso']) {
                return $this->erro('Não foi possível validar as vagas desta visita. Tente novamente.');
            }

            if ($visita->limite_participantes !== null && $contagem['total'] >= $visita->limite_participantes) {
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

    public function destroy(Visita $visita, VisitaParticipante $participante): array
    {
        try {
            if ($participante->visita_id !== $visita->id) {
                return $this->erro('Participante não pertence a esta visita.');
            }

            $usuario = usuarioAutenticado();

            if (! $usuario) {
                return $this->erro('Usuário não autenticado.');
            }

            $ehProprioParticipante = $participante->voluntario_id === $usuario->id;
            $ehLiderDaVisita       = $visita->lider_id !== null && $visita->lider_id === $usuario->id;

            if ($ehProprioParticipante && $ehLiderDaVisita) {
                return $this->erro('Altere o líder da visita antes de cancelar sua inscrição.');
            }

            if (! $ehProprioParticipante && ! $this->visitaService->podeEditarVisita($usuario, $visita)) {
                return $this->erro('Você não tem permissão para cancelar esta inscrição.');
            }

            return $this->queries->update($participante->id, [
                'status_participacao' => StatusParticipacao::Cancelado->value,
            ]);
        } catch (\Throwable $th) {
            $this->logarErro([
                'visita_id'       => $visita->id,
                'participante_id' => $participante->id,
            ], formatarMensagemErro($th));

            return $this->erro(formatarMensagemErro($th));
        }
    }

    private function buscarParticipacaoCancelada(int $visitaId, int $voluntarioId): ?object
    {
        $retorno = $this->queries->index([
            'visita_id'           => $visitaId,
            'voluntario_id'       => $voluntarioId,
            'status_participacao' => StatusParticipacao::Cancelado->value,
        ]);

        if (! $retorno['sucesso']) {
            return null;
        }

        return $retorno['dados'];
    }

    private function usuarioJaInscrito(int $visitaId, int $voluntarioId): bool
    {
        $retorno = $this->queries->index([
            ...$this->filtrosParticipanteAtivo($visitaId),
            'voluntario_id' => $voluntarioId,
        ]);

        return $retorno['sucesso'] && $retorno['dados'] !== null;
    }

    private function totalParticipantesAtivos(int $visitaId): array
    {
        $retorno = $this->queries->index([
            ...$this->filtrosParticipanteAtivo($visitaId),
            'retornar_lista' => true,
        ]);

        if (! $retorno['sucesso']) {
            return ['sucesso' => false, 'total' => 0];
        }

        return ['sucesso' => true, 'total' => $retorno['dados']->count()];
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
