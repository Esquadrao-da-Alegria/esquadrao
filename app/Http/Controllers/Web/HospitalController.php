<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Cidade\Service;
use App\Services\Hospital\Form\Service as FormService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HospitalController extends Controller
{
    public function __construct(
        private Service $service,
        private FormService $formService,
    ) {
        //
    }

    /**
     * Retornar listagem do recurso
     */
    public function index()
    {
        return Inertia::render('Hospitais/Index');
    }

    /**
     * Retornar formulário de criação
     */
    public function create()
    {
        $dadosParaView = $this->formService->buscarDados();

        return Inertia::render('Hospitais/Create', $dadosParaView);
    }

    /**
     * Salvar novo recurso
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Retornar formulário de edição
     */
    public function edit(string $id)
    {
        return Inertia::render('Hospitais/Edit');
    }

    /**
     * Atualizar recurso
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Excluir recurso
     */
    public function destroy(string $id)
    {
        //
    }
}
