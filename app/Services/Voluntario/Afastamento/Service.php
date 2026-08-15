<?php

namespace App\Services\Voluntario\Afastamento;

use App\Enums\MotivoAfastamento;
use App\Enums\StatusAfastamento;
use App\Enums\StatusParticipacao;
use App\Enums\VisitaStatus;
use App\Models\User;
use App\Models\VisitaParticipante;
use App\Models\Voluntario;
use App\Models\VoluntarioAfastamento;
use App\Queries\Voluntario\Afastamento\Queries;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __construct(private Queries $queries) {}

    public function store(Voluntario $voluntario, User $registradoPor, array $dados): array
    {
        try {
            DB::beginTransaction();

            $dataInicio = Carbon::parse($dados['data_inicio'])->toDateString();
            $dataFim = Carbon::parse($dados['data_fim'])->toDateString();

            if ($dataInicio > $dataFim) {
                DB::rollBack();

                return [
                    'sucesso' => false,
                    'dados' => [],
                    'erros' => ['A data de início não pode ser posterior à data de fim.'],
                ];
            }

            $motivo = $dados['motivo'] instanceof MotivoAfastamento
                ? $dados['motivo']->value
                : (string) $dados['motivo'];

            $payload = [
                'voluntario_id' => $voluntario->id,
                'registrado_por_id' => $registradoPor->id,
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'motivo' => $motivo,
                'observacoes' => $dados['observacoes'] ?? null,
                'status' => StatusAfastamento::Ativo->value,
            ];

            $retorno = $this->queries->store($payload);

            if (! $retorno['sucesso']) {
                DB::rollBack();
                session()->flash('mensagem_erro', 'Erro ao registrar afastamento.');

                return $retorno;
            }

            /** @var VoluntarioAfastamento $afastamento */
            $afastamento = $retorno['dados']['model'];

            $this->cancelarInscricoesVisitasNoPeriodo($voluntario, $dataInicio, $dataFim);

            session()->flash('mensagem_sucesso', 'Afastamento registrado com sucesso!');

            DB::commit();

            return [
                'sucesso' => true,
                'dados' => ['model' => $afastamento],
                'erros' => [],
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Erro ao registrar afastamento de voluntário.', [
                'erro' => formatarMensagemErro($th),
                'voluntario_id' => $voluntario->id,
            ]);
            session()->flash('mensagem_erro', 'Erro ao registrar afastamento.');

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function prorrogar(VoluntarioAfastamento $afastamento, array $dados, ?User $usuario = null): array
    {
        try {
            DB::beginTransaction();

            $novaDataFim = null;
            if (! empty($dados['dias'])) {
                $novaDataFim = Carbon::parse($afastamento->data_fim)->addDays((int) $dados['dias'])->toDateString();
            } elseif (! empty($dados['nova_data_fim'])) {
                $novaDataFim = Carbon::parse($dados['nova_data_fim'])->toDateString();
            } elseif (! empty($dados['data_fim'])) {
                $novaDataFim = Carbon::parse($dados['data_fim'])->toDateString();
            }

            if (! $novaDataFim || $novaDataFim <= Carbon::parse($afastamento->data_fim)->toDateString()) {
                DB::rollBack();

                return [
                    'sucesso' => false,
                    'dados' => [],
                    'erros' => ['A nova data final deve ser posterior à data final atual.'],
                ];
            }

            $obsAnterior = $afastamento->observacoes ? $afastamento->observacoes . "\n" : '';
            $registroProrrogacao = sprintf(
                '[Prorrogado em %s até %s%s]',
                now()->format('d/m/Y H:i'),
                Carbon::parse($novaDataFim)->format('d/m/Y'),
                ! empty($dados['observacoes']) ? ': ' . $dados['observacoes'] : ''
            );

            $novaObservacao = $obsAnterior . $registroProrrogacao;

            $dataAnteriorFim = Carbon::parse($afastamento->data_fim)->toDateString();

            $retorno = $this->queries->update($afastamento->id, [
                'data_fim' => $novaDataFim,
                'observacoes' => $novaObservacao,
                'status' => StatusAfastamento::Ativo->value,
            ]);

            if (! $retorno['sucesso']) {
                DB::rollBack();
                session()->flash('mensagem_erro', 'Erro ao prorrogar afastamento.');

                return $retorno;
            }

            $voluntario = $afastamento->voluntario;
            if ($voluntario) {
                $this->cancelarInscricoesVisitasNoPeriodo($voluntario, $dataAnteriorFim, $novaDataFim);
            }

            session()->flash('mensagem_sucesso', 'Afastamento prorrogado com sucesso!');

            DB::commit();

            return [
                'sucesso' => true,
                'dados' => ['model' => $afastamento->fresh()],
                'erros' => [],
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Erro ao prorrogar afastamento de voluntário.', [
                'erro' => formatarMensagemErro($th),
                'afastamento_id' => $afastamento->id,
            ]);
            session()->flash('mensagem_erro', 'Erro ao prorrogar afastamento.');

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function encerrar(VoluntarioAfastamento $afastamento, array $dados = []): array
    {
        try {
            DB::beginTransaction();

            $hoje = now()->toDateString();
            $dataFimOriginal = Carbon::parse($afastamento->data_fim)->toDateString();
            $dataFimAjustada = $dataFimOriginal > $hoje ? $hoje : $dataFimOriginal;

            $obsAnterior = $afastamento->observacoes ? $afastamento->observacoes . "\n" : '';
            $registroEncerramento = sprintf(
                '[Encerrado antecipadamente em %s%s]',
                now()->format('d/m/Y H:i'),
                ! empty($dados['observacoes']) ? ': ' . $dados['observacoes'] : ''
            );

            $novaObservacao = $obsAnterior . $registroEncerramento;

            $retorno = $this->queries->update($afastamento->id, [
                'status' => StatusAfastamento::Encerrado->value,
                'data_fim' => $dataFimAjustada,
                'observacoes' => $novaObservacao,
            ]);

            if (! $retorno['sucesso']) {
                DB::rollBack();
                session()->flash('mensagem_erro', 'Erro ao encerrar afastamento.');

                return $retorno;
            }

            session()->flash('mensagem_sucesso', 'Afastamento encerrado com sucesso!');

            DB::commit();

            return [
                'sucesso' => true,
                'dados' => ['model' => $afastamento->fresh()],
                'erros' => [],
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Erro ao encerrar afastamento de voluntário.', [
                'erro' => formatarMensagemErro($th),
                'afastamento_id' => $afastamento->id,
            ]);
            session()->flash('mensagem_erro', 'Erro ao encerrar afastamento.');

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    private function cancelarInscricoesVisitasNoPeriodo(Voluntario $voluntario, string $dataInicio, string $dataFim): void
    {
        $usuario = $voluntario->user;
        if (! $usuario) {
            return;
        }

        VisitaParticipante::query()
            ->where('voluntario_id', $usuario->id)
            ->where('status_participacao', '!=', StatusParticipacao::Cancelado->value)
            ->whereHas('visita', function ($query) use ($dataInicio, $dataFim) {
                $query->where('status', VisitaStatus::Agendada->value)
                    ->whereDate('inicio_em', '<=', $dataFim)
                    ->whereDate('inicio_em', '>=', $dataInicio);
            })
            ->update([
                'status_participacao' => StatusParticipacao::Cancelado->value,
            ]);
    }
}
