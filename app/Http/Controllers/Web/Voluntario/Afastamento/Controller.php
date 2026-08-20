<?php

namespace App\Http\Controllers\Web\Voluntario\Afastamento;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Requests\Web\Voluntario\Afastamento\EncerrarRequest;
use App\Http\Requests\Web\Voluntario\Afastamento\ProrrogarRequest;
use App\Http\Requests\Web\Voluntario\Afastamento\StoreRequest;
use App\Http\Requests\Web\Voluntario\Afastamento\UpdateRequest;
use App\Models\Voluntario;
use App\Models\VoluntarioAfastamento;
use App\Services\Voluntario\Afastamento\Service;
use Illuminate\Http\RedirectResponse;

class Controller extends BaseController
{
    public function store(StoreRequest $request, Voluntario $voluntario, Service $service): RedirectResponse
    {
        $service->store($voluntario, $request->user(), $request->validated());

        return back();
    }

    public function update(
        UpdateRequest $request,
        Voluntario $voluntario,
        VoluntarioAfastamento $afastamento,
        Service $service
    ): RedirectResponse {
        abort_if((int) $afastamento->voluntario_id !== (int) $voluntario->id, 404);

        $service->update($afastamento, $request->validated(), $request->user());

        return back();
    }

    public function destroy(
        Voluntario $voluntario,
        VoluntarioAfastamento $afastamento,
        Service $service
    ): RedirectResponse {
        abort_if((int) $afastamento->voluntario_id !== (int) $voluntario->id, 404);

        $service->destroy($afastamento);

        return back();
    }

    public function prorrogar(
        ProrrogarRequest $request,
        Voluntario $voluntario,
        VoluntarioAfastamento $afastamento,
        Service $service
    ): RedirectResponse {
        abort_if((int) $afastamento->voluntario_id !== (int) $voluntario->id, 404);

        $service->prorrogar($afastamento, $request->validated(), $request->user());

        return back();
    }

    public function encerrar(
        EncerrarRequest $request,
        Voluntario $voluntario,
        VoluntarioAfastamento $afastamento,
        Service $service
    ): RedirectResponse {
        abort_if((int) $afastamento->voluntario_id !== (int) $voluntario->id, 404);

        $service->encerrar($afastamento, $request->validated());

        return back();
    }
}
