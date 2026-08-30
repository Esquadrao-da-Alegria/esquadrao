<?php

namespace App\Http\Controllers\Web\Dashboard\Visita\Hospital;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Requests\Web\Dashboard\Visita\Hospital\IndexRequest;
use App\Services\Dashboard\Visita\Hospital\Service;
use App\Models\Hospital;
use Inertia\Inertia;
use Inertia\Response;

class Controller extends BaseController
{
    public function __construct(private Service $service) {}

    public function index(IndexRequest $request): Response
    {
        return Inertia::render(
            'Dashboard/Visita/Hospital/Index',
            $this->service->index($request->user(), $request->validated())
        );
    }

    public function show(IndexRequest $request, Hospital $hospital): Response
    {
        return Inertia::render(
            'Dashboard/Visita/Hospital/Show',
            $this->service->show($request->user(), $hospital, $request->validated()),
        );
    }
}
