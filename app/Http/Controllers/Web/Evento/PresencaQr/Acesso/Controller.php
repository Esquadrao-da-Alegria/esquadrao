<?php

namespace App\Http\Controllers\Web\Evento\PresencaQr\Acesso;

use App\Http\Controllers\Controller as BaseController;
use App\Models\Evento;
use App\Models\EventoSessaoPresenca;
use App\Services\Evento\PresencaQr\Service;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    public function show(Request $request, Evento $evento, EventoSessaoPresenca $sessao, Service $service)
    {
        $service->validarSessaoAtiva($evento, $sessao);
        $request->session()->put('evento_presenca_qr.sessao_id', $sessao->id);

        if (! $request->user()) {
            return redirect()->guest(route('eventos.presencas-qr.confirmacao.show'));
        }

        return redirect()->route('eventos.presencas-qr.confirmacao.show');
    }
}
