<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;
use App\Services\Patrocinador\Service;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Patrocinador\StoreRequest;
use App\Http\Requests\Web\Patrocinador\UpdateRequest;
use App\Models\Patrocinador;
use App\Services\Patrocinador\Form\Service as FormService;
use Illuminate\Http\Request;

class PatrocinadorController extends Controller
{

    public function __construct(
        private Service $service,
        private FormService $formService,
    ) {}

    /**
     * Retornar listagem do recurso
     */
    public function index(Request $request)
    {
        $filtrosBusca = [
            ...$request->all(),
            'retornar_lista' => true,
        ];

        $retorno = $this->service->index($filtrosBusca);

        $dadosView = ['patrocinadores' => $retorno['dados']];

        return Inertia::render('Patrocinador/Index', $dadosView);
    }

    /**
     * Retornar formulário de criação
     */
    public function create()
    {
        $dadosView = $this->formService->buscarDados(null);

        return Inertia::render('Patrocinador/Create', $dadosView);
    }

    /**
     * Salvar novo recurso
     */
    public function store(StoreRequest $request)
    {
        $this->service->store($request->all());

        return redirect()->route('patrocinadores.index');
    }

    /**
     * Retornar formulário de edição
     */
    public function edit(Request $request, Patrocinador $patrocinador)
    {
        $dadosView = $this->formService->buscarDados($patrocinador);

        return Inertia::render('Patrocinador/Edit', $dadosView);
    }

    /**
     * Atualizar recurso
     */
    public function update(UpdateRequest $request, string $id)
    {
        $this->service->update($id, $request->all());

        return redirect()->route('patrocinadores.index');
    }

    /**
     * Excluir recurso
     */
    public function destroy(string $id)
    {
        $this->service->destroy($id);

        return redirect()->route('patrocinadores.index');
    }
}
