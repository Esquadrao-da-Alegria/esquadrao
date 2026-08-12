<?php

namespace App\Http\Controllers\Web\Dashboard\Meu;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Requests\Web\Dashboard\Meu\IndexRequest;
use App\Services\Dashboard\Meu\Service;
use Inertia\Inertia;
use Inertia\Response;

class Controller extends BaseController
{
    public function __construct(private Service $service) {}

    public function index(IndexRequest $request): Response
    {
        return Inertia::render(
            'Dashboard/Meu',
            $this->service->index($request->user(), $request->validated())
        );
    }
}
