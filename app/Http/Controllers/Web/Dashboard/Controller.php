<?php

namespace App\Http\Controllers\Web\Dashboard;

use App\Http\Controllers\Controller as BaseController;
use App\Services\Dashboard\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class Controller extends BaseController
{
    public function __construct(private Service $service) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Dashboard', $this->service->index($request->user()));
    }
}
