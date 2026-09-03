<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Evento\FinalizarEventoRequest;
use App\Models\Evento;
use App\Services\Evento\PresencaQr\Service as PresencaQrService;

class EventoFinalizacaoController extends Controller
{
    public function store(FinalizarEventoRequest $request, Evento $evento, PresencaQrService $presencaQrService)
    {
        if ($evento->estaFinalizado()) {
            return back()->with('mensagem_erro', 'Este evento já foi finalizado.');
        }

        if ($evento->estaCancelado()) {
            return back()->with('mensagem_erro', 'Este evento foi cancelado e não pode ser finalizado.');
        }

        if (! $evento->data_inicio->isPast()) {
            return back()->with('mensagem_erro', 'O evento ainda não começou.');
        }

        $evento->update([
            'status' => 'finalizado',
            'finalizado_em' => now(),
            'finalizado_por_id' => $request->user()->id,
            'observacoes_finalizacao' => $request->validated('observacoes_finalizacao'),
        ]);
        $presencaQrService->encerrarAtivas($evento, $request->user());

        return redirect()->route('eventos.show', $evento)->with('mensagem_sucesso', 'Evento finalizado com sucesso.');
    }
}
