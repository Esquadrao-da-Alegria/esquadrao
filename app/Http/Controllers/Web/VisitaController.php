<?php

namespace App\Http\Controllers\Web;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Services\Visita\Service;

class VisitaController extends Controller
{
    public function __construct(private Service $service) {}

    public function index(Request $request): \Inertia\Response
    {
        $mes = $this->normalizarMes($request->query('mes'));

        $filtrosBusca = [
            'mes' => $mes,
        ];

        $retorno = $this->service->index($filtrosBusca);

        return Inertia::render('Visita/Index', [
            'visitas' => $retorno['dados'],
            'mes'     => $mes,
        ]);
    }

    private function normalizarMes(?string $mes): string
    {
        if (!$mes) {
            return now()->format('Y-m');
        }

        try {
            Carbon::createFromFormat('Y-m', $mes);
            return $mes;
        } catch (\Throwable) {
            return now()->format('Y-m');
        }
    }
}
