<?php

namespace App\Http\Controllers\Web\Evento\Ajuste;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Requests\Web\Evento\Ajuste\StoreRequest;
use App\Models\Evento;
use App\Services\Evento\Ajuste\Service;
use Illuminate\Http\RedirectResponse;

class Controller extends BaseController
{
    public function store(StoreRequest $request, Evento $evento, Service $service): RedirectResponse
    {
        $service->store($evento, $request->user(), $request->validated());

        return back()->with('mensagem_sucesso', 'Ajuste de participação registrado com sucesso.');
    }
}
