<?php

namespace App\Http\Controllers\Web\Json;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\Cidade\Service;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CidadeController extends Controller
{
    public function __construct(
        private Service $service
    ) {
        //
    }

    /**
     * Retornar listagem do recurso
     */
    public function index(Request $request): JsonResponse
    {
        $retorno = $this->service->index($request->all());

        $status = $retorno['sucesso'] ? 200 : 500;

        return response()->json($retorno, $status);
    }
}
