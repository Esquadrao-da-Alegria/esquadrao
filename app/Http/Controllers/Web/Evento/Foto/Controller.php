<?php

namespace App\Http\Controllers\Web\Evento\Foto;

// CONTROLLERS
use App\Http\Controllers\Controller as BaseController;
// FORM REQUESTS
use App\Http\Requests\Web\Evento\Foto\StoreRequest;
// MODELS
use App\Models\Evento;
// SERVICES
use App\Services\Evento\Foto\Service;
// HTTP
use Illuminate\Http\RedirectResponse;

class Controller extends BaseController
{
    public function store(StoreRequest $request, Evento $evento, Service $service): RedirectResponse
    {
        try {
            $service->salvar($evento, $request->file('foto'));
        } catch (\Throwable) {
            return back()->withErrors(['foto' => 'Não foi possível salvar a foto do evento.']);
        }

        return back()->with('mensagem_sucesso', 'Foto do evento atualizada com sucesso.');
    }

    public function destroy(StoreRequest $request, Evento $evento, Service $service): RedirectResponse
    {
        try {
            $service->remover($evento);
        } catch (\Throwable) {
            return back()->withErrors(['foto' => 'Não foi possível remover a foto do evento.']);
        }

        return back()->with('mensagem_sucesso', 'Foto do evento removida com sucesso.');
    }
}
