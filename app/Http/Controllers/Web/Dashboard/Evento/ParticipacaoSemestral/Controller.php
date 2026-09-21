<?php

namespace App\Http\Controllers\Web\Dashboard\Evento\ParticipacaoSemestral;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Requests\Web\Dashboard\Evento\ParticipacaoSemestral\IndexRequest;
use App\Services\Dashboard\Evento\ParticipacaoSemestral\Service;
use Inertia\Inertia;
use Inertia\Response;

class Controller extends BaseController
{
    public function __construct(private Service $service) {}

    public function __invoke(IndexRequest $request): Response
    {
        return Inertia::render(
            'Dashboard/Evento/ParticipacaoSemestral/Index',
            $this->service->index($request->user(), $request->validated())
        );
    }
}
