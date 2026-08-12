<?php

namespace App\Http\Controllers\Web\Visita\Ajuste;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Requests\Web\Visita\Ajuste\StoreRequest;
use App\Models\Visita;
use App\Services\Visita\Ajuste\Service;
use Illuminate\Http\RedirectResponse;

class Controller extends BaseController
{
    public function store(StoreRequest $request, Visita $visita, Service $service): RedirectResponse
    {
        $service->store($visita, $request->user(), $request->validated());

        return back()->with('success', 'Ajuste de contabilização registrado com sucesso.');
    }
}
