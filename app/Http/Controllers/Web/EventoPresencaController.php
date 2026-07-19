<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Evento\AtualizarPresencasRequest;
use App\Models\Evento;

class EventoPresencaController extends Controller
{
    public function update(AtualizarPresencasRequest $request, Evento $evento)
    {
        if ($evento->estaCancelado()) {
            return back()->with('mensagem_erro', 'Evento cancelado não permite registrar presença.');
        }

        $registradoPor = $request->user()->id;

        foreach ($request->validated('participantes') as $participante) {
            $existeAtivo = $evento->participantesAtivos()
                ->where('users.id', $participante['user_id'])
                ->exists();

            if (! $existeAtivo) {
                continue;
            }

            $evento->participantes()->updateExistingPivot($participante['user_id'], [
                'presenca' => $participante['presenca'] ?? null,
                'presenca_registrada_em' => now(),
                'presenca_registrada_por_id' => $registradoPor,
                'observacao_presenca' => $participante['observacao_presenca'] ?? null,
            ]);
        }

        return back()->with('mensagem_sucesso', 'Presença registrada com sucesso.');
    }
}