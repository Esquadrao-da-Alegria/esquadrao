<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\Hospital\Service as HospitalService;
use App\Http\Controllers\Controller;
use App\Services\Hospital\Form\Service as FormService;

class OndeAtuamosController extends Controller
{
    public function __construct(
        private HospitalService $hospitalService,
        private FormService $formService,
    ) {
        //
    }

    /**
     * Retornar listagem do recurso
     */
    public function index(Request $request)
    {
        $dadosView = [
            'hospitais' => $this->hospitalService->buscarListaAgrupadaPorCidades()
        ];
        //dd($dadosView['hospitais']);
        return Inertia::render('OndeAtuamos/Index', $dadosView);
    }
}
