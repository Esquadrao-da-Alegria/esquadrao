<?php

namespace App\Http\Controllers\Web\Visita\Participante;

use App\Http\Controllers\Controller;
use App\Models\Visita;
use App\Models\VisitaParticipante;
use App\Services\Visita\Participante\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitaParticipanteController extends Controller
{
    public function __construct(private Service $service) {}

    public function store(Request $request, Visita $visita): JsonResponse
    {
        $retorno = $this->service->store($visita, $request->all());

        if ($retorno['sucesso']) {
            $retorno['dados']['participantes'] = $this->carregarParticipantes($visita);
        }

        $status = $retorno['sucesso'] ? 200 : 422;

        return response()->json($retorno, $status);
    }

    public function destroy(Visita $visita, VisitaParticipante $participante): JsonResponse
    {
        $retorno = $this->service->destroy($visita, $participante);

        if ($retorno['sucesso']) {
            $retorno['dados']['participantes'] = $this->carregarParticipantes($visita);
        }

        $status = $retorno['sucesso'] ? 200 : 422;

        return response()->json($retorno, $status);
    }

    private function carregarParticipantes(Visita $visita): array
    {
        return $visita->participantes()
            ->with('voluntario:id,name')
            ->get()
            ->toArray();
    }
}
