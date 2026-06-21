<?php

namespace App\Services\Evento;

use App\Enums\StatusEvento;
use App\Models\Evento;
use App\Models\EventoParticipante;
use App\Queries\Evento\Queries;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Service
{
    public function __construct(private Queries $queries) {}

    public function index(array $filtros): array
    {
        try {
            $filtros['user_id'] = Auth::id();

            $retorno = $this->queries->index($filtros);

            if (!$retorno['sucesso']) {
                session()->flash('mensagem_erro', 'Erro ao listar eventos!');
            }

            return $retorno;
        } catch (\Throwable $th) {
            return ['sucesso' => false, 'dados' => [], 'erros' => [formatarMensagemErro($th)]];
        }
    }

    public function store(array $dados): array
    {
        try {
            DB::beginTransaction();

            $responsaveisIds              = $dados['responsaveis'] ?? [];
            $dadosDatabase                = Arr::except($dados, ['data', 'hora_inicio', 'hora_fim', 'responsaveis']);
            $dadosDatabase['criado_por_id'] = Auth::id();

            $retorno = $this->queries->store($dadosDatabase);

            if (!$retorno['sucesso']) {
                session()->flash('mensagem_erro', 'Erro ao salvar evento!');
                DB::rollBack();
                return $retorno;
            }

            $retorno['dados']['model']->responsaveis()->createMany(
                array_map(fn ($id) => ['voluntario_id' => $id], $responsaveisIds)
            );
    
            session()->flash('mensagem_sucesso', 'Evento criado com sucesso!');
            DB::commit();

            return $retorno;
        } catch (\Throwable $th) {
            session()->flash('mensagem_erro', 'Erro ao salvar evento!');
            DB::rollBack();
            return ['sucesso' => false, 'dados' => [], 'erros' => [formatarMensagemErro($th)]];
        }
    }

    public function update(string $id, array $dados): array
    {
        try {
            DB::beginTransaction();

            $responsaveisIds = $dados['responsaveis'] ?? [];
            $dadosDatabase   = Arr::except($dados, ['data', 'hora_inicio', 'hora_fim', 'responsaveis']);
            $retorno         = $this->queries->update($id, $dadosDatabase);

            if (!$retorno['sucesso']) {
                session()->flash('mensagem_erro', 'Erro ao atualizar evento!');
                DB::rollBack();
                return $retorno;
            }

            $modelo = $retorno['dados']['model'];
            $modelo->responsaveis()->delete();
            $modelo->responsaveis()->createMany(
                array_map(fn ($id) => ['voluntario_id' => $id], $responsaveisIds)
            );

            session()->flash('mensagem_sucesso', 'Evento atualizado com sucesso!');
            DB::commit();

            return $retorno;
        } catch (\Throwable $th) {
            session()->flash('mensagem_erro', 'Erro ao atualizar evento!');
            DB::rollBack();
            return ['sucesso' => false, 'dados' => [], 'erros' => [formatarMensagemErro($th)]];
        }
    }

    public function destroy(string $id): array
    {
        try {
            $retorno = $this->queries->destroy($id);

            if (!$retorno['sucesso']) {
                session()->flash('mensagem_erro', 'Erro ao excluir evento!');
            } else {
                session()->flash('mensagem_sucesso', 'Evento excluído com sucesso!');
            }

            return $retorno;
        } catch (\Throwable $th) {
            return ['sucesso' => false, 'dados' => [], 'erros' => [formatarMensagemErro($th)]];
        }
    }

    public function inscrever(int $eventoId, int $userId): array
    {
        try {
            $evento = Evento::findOrFail($eventoId);

            if ($evento->status !== StatusEvento::AGENDADO) {
                return $this->erroFlash('Não é possível se inscrever neste evento.');
            }

            if (now()->greaterThanOrEqualTo($evento->data_inicio)) {
                return $this->erroFlash('Não é possível se inscrever após o início do evento.');
            }

            $jaInscrito = EventoParticipante::where('evento_id', $eventoId)
                ->where('user_id', $userId)
                ->whereIn('status', ['INSCRITO', 'PRESENTE'])
                ->exists();

            if ($jaInscrito) {
                return $this->erroFlash('Você já está inscrito neste evento.');
            }

            if ($evento->limite_vagas !== null) {
                $inscritos = EventoParticipante::where('evento_id', $eventoId)
                    ->where('status', 'INSCRITO')
                    ->count();

                if ($inscritos >= $evento->limite_vagas) {
                    return $this->erroFlash('Limite de vagas atingido para este evento.');
                }
            }

            $inscricaoCancelada = EventoParticipante::where('evento_id', $eventoId)
                ->where('user_id', $userId)
                ->where('status', 'CANCELADO')
                ->first();

            if ($inscricaoCancelada) {
                $inscricaoCancelada->update(['status' => 'INSCRITO']);
            } else {
                EventoParticipante::create([
                    'evento_id' => $eventoId,
                    'user_id'   => $userId,
                    'status'    => 'INSCRITO',
                ]);
            }

            session()->flash('mensagem_sucesso', 'Inscrição realizada com sucesso!');

            return ['sucesso' => true, 'dados' => [], 'erros' => []];
        } catch (\Throwable $th) {
            return ['sucesso' => false, 'dados' => [], 'erros' => [formatarMensagemErro($th)]];
        }
    }

    public function cancelarInscricao(int $eventoId, int $userId): array
    {
        try {
            $evento = Evento::findOrFail($eventoId);

            if ($evento->status === StatusEvento::FINALIZADO) {
                return $this->erroFlash('Não é possível cancelar após o evento ser finalizado.');
            }

            $inscricao = EventoParticipante::where('evento_id', $eventoId)
                ->where('user_id', $userId)
                ->where('status', 'INSCRITO')
                ->first();

            if (!$inscricao) {
                return $this->erroFlash('Você não possui inscrição ativa neste evento.');
            }

            $inscricao->update(['status' => 'CANCELADO']);

            session()->flash('mensagem_sucesso', 'Inscrição cancelada com sucesso.');

            return ['sucesso' => true, 'dados' => [], 'erros' => []];
        } catch (\Throwable $th) {
            return ['sucesso' => false, 'dados' => [], 'erros' => [formatarMensagemErro($th)]];
        }
    }

    public function finalizar(int $eventoId, array $presencas): array
    {
        try {
            DB::beginTransaction();

            $evento = Evento::findOrFail($eventoId);

            if ($evento->status !== StatusEvento::AGENDADO) {
                DB::rollBack();
                return $this->erroFlash('Apenas eventos agendados podem ser finalizados.');
            }

            $confirmadoPor = Auth::id();
            $confirmadoEm  = now();

            foreach ($presencas as $presenca) {
                EventoParticipante::where('evento_id', $eventoId)
                    ->where('user_id', $presenca['user_id'])
                    ->update([
                        'status'                    => $presenca['status'],
                        'presenca_confirmada_por_id' => $confirmadoPor,
                        'presenca_confirmada_em'     => $confirmadoEm,
                    ]);
            }

            $evento->update(['status' => StatusEvento::FINALIZADO->value]);

            session()->flash('mensagem_sucesso', 'Evento finalizado com sucesso!');

            DB::commit();

            return ['sucesso' => true, 'dados' => [], 'erros' => []];
        } catch (\Throwable $th) {
            DB::rollBack();
            return ['sucesso' => false, 'dados' => [], 'erros' => [formatarMensagemErro($th)]];
        }
    }

    public function cancelarEvento(int $eventoId): array
    {
        try {
            $evento = Evento::findOrFail($eventoId);

            if ($evento->status !== StatusEvento::AGENDADO) {
                return $this->erroFlash('Apenas eventos agendados podem ser cancelados.');
            }

            $evento->update(['status' => StatusEvento::CANCELADO->value]);

            session()->flash('mensagem_sucesso', 'Evento cancelado.');

            return ['sucesso' => true, 'dados' => [], 'erros' => []];
        } catch (\Throwable $th) {
            return ['sucesso' => false, 'dados' => [], 'erros' => [formatarMensagemErro($th)]];
        }
    }

    public function dashboard(array $filtros): array
    {
        try {
            return $this->queries->dashboard($filtros);
        } catch (\Throwable $th) {
            return ['sucesso' => false, 'dados' => ['dados' => [], 'semestres' => []], 'erros' => [formatarMensagemErro($th)]];
        }
    }

    private function erroFlash(string $mensagem): array
    {
        session()->flash('mensagem_erro', $mensagem);
        return ['sucesso' => false, 'dados' => [], 'erros' => [$mensagem]];
    }
}
