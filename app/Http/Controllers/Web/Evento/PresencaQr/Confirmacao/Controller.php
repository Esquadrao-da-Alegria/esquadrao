<?php

namespace App\Http\Controllers\Web\Evento\PresencaQr\Confirmacao;

use App\Http\Controllers\Controller as BaseController;
use App\Models\EventoSessaoPresenca;
use App\Services\Evento\PresencaQr\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Controller extends BaseController
{
    public function show(Request $request, Service $service)
    {
        $sessao = $this->sessao($request);
        $service->validarSessaoAtiva($sessao->evento, $sessao);
        $confirmado = $sessao->confirmacoes()->where('user_id', $request->user()->id)->exists();

        return Inertia::render('Evento/PresencaQr/Confirmar', [
            'evento' => $sessao->evento->only(['id', 'titulo', 'tipo', 'local', 'data_inicio', 'data_fim']),
            'confirmado' => $confirmado,
        ]);
    }

    public function store(Request $request, Service $service)
    {
        $sessao = $this->sessao($request);
        $registrada = $service->confirmar($sessao, $request->user());

        return back()->with(
            $registrada ? 'mensagem_sucesso' : 'mensagem_alerta',
            $registrada ? 'Sua presença foi confirmada.' : 'Sua presença já estava confirmada.',
        );
    }

    private function sessao(Request $request): EventoSessaoPresenca
    {
        $id = $request->session()->get('evento_presenca_qr.sessao_id');
        abort_unless($id, 404);

        return EventoSessaoPresenca::query()->with('evento')->findOrFail($id);
    }
}
