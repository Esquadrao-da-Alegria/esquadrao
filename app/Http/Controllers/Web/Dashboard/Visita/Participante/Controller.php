<?php

namespace App\Http\Controllers\Web\Dashboard\Visita\Participante;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Requests\Web\Dashboard\Visita\Participante\IndexRequest;
use App\Models\User;
use App\Services\Dashboard\Visita\Participante\Service;
use Inertia\Inertia;
use Inertia\Response;

class Controller extends BaseController
{
    public function __construct(private Service $service) {}

    public function index(IndexRequest $request): Response
    {
        return Inertia::render(
            'Dashboard/Visita/Participante/Index',
            $this->service->index($request->user(), $request->validated())
        );
    }

    public function show(IndexRequest $request, User $voluntario): Response
    {
        return Inertia::render(
            'Dashboard/Visita/Participante/Show',
            $this->service->show($request->user(), $voluntario, $request->validated())
        );
    }
}
