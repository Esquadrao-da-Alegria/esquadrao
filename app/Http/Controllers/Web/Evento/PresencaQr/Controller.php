<?php

namespace App\Http\Controllers\Web\Evento\PresencaQr;

use App\Http\Controllers\Controller as BaseController;
use App\Models\Evento;
use App\Models\EventoSessaoPresenca;
use App\Services\Evento\PresencaQr\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class Controller extends BaseController
{
    public function store(Request $request, Evento $evento, Service $service)
    {
        abort_unless($service->podeGerenciar($request->user(), $evento), 403);

        try {
            $service->abrir($evento, $request->user());
        } catch (ValidationException $exception) {
            return back()->with('mensagem_erro', collect($exception->errors())->flatten()->first());
        }

        return back()->with('mensagem_sucesso', 'Confirmação de presença aberta.');
    }

    public function destroy(Request $request, Evento $evento, EventoSessaoPresenca $sessao, Service $service)
    {
        abort_unless($service->podeGerenciar($request->user(), $evento), 403);
        $service->encerrar($evento, $sessao, $request->user());

        return back()->with('mensagem_sucesso', 'Confirmação de presença encerrada.');
    }
}
