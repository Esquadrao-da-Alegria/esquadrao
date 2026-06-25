<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\Request;

class EventoInscricaoController extends Controller
{
    public function store(Request $request, Evento $evento)
    {
        if ($erro = $this->validarEvento($evento)) return $erro;
        if ($evento->limite_participantes && $evento->participantesAtivos()->count() >= $evento->limite_participantes) {
            return back()->with('mensagem_erro', 'Não há vagas disponíveis.');
        }
        $userId = $request->user()->id;
        $atual = $evento->participantes()->where('users.id', $userId)->first();
        if ($atual?->pivot->status === 'inscrito') {
            return back()->with('mensagem_erro', 'Você já está inscrito neste evento.');
        }
        $evento->participantes()->syncWithoutDetaching([$userId => ['status' => 'inscrito', 'inscrito_em' => now(), 'cancelado_em' => null]]);
        return back()->with('mensagem_sucesso', 'Inscrição realizada com sucesso.');
    }

    public function destroy(Request $request, Evento $evento)
    {
        if ($evento->estaCancelado()) return back()->with('mensagem_erro', 'Este evento foi cancelado.');
        if ($evento->estaFinalizado()) return back()->with('mensagem_erro', 'Este evento já foi finalizado.');
        if ($evento->data_inicio->isPast()) return back()->with('mensagem_erro', 'Não é possível cancelar a inscrição após o início do evento.');

        $userId = $request->user()->id;
        $participante = $evento->participantes()->where('users.id', $userId)->first();

        if (! $participante || $participante->pivot->status !== 'inscrito') {
            return back()->with('mensagem_erro', 'Você não está inscrito neste evento.');
        }
        if ($participante->pivot->presenca !== null) {
            return back()->with('mensagem_erro', 'Não é possível cancelar a inscrição com presença já registrada.');
        }

        $evento->participantes()->updateExistingPivot($userId, ['status' => 'cancelado', 'cancelado_em' => now()]);
        return back()->with('mensagem_sucesso', 'Inscrição cancelada com sucesso.');
    }

    private function validarEvento(Evento $evento)
    {
        if ($evento->estaCancelado()) return back()->with('mensagem_erro', 'Este evento foi cancelado.');
        if ($evento->estaFinalizado()) return back()->with('mensagem_erro', 'Este evento já foi finalizado.');
        if ($evento->data_inicio->isPast()) return back()->with('mensagem_erro', 'Este evento já começou.');
        if ($evento->limite_inscricao?->isPast()) return back()->with('mensagem_erro', 'O prazo de inscrição deste evento foi encerrado.');
        return null;
    }
}
