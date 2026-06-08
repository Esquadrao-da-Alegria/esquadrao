<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;
use App\Enums\StatusEvento;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Evento\StoreRequest;
use App\Http\Requests\Web\Evento\UpdateRequest;
use App\Models\Evento;
use App\Services\Evento\Form\Service as FormService;
use App\Services\Evento\Service;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    public function __construct(
        private Service $service,
        private FormService $formService,
    ) {
        //
    }
    
    /**
     * Retornar listagem de eventos agendados
     */
    public function index(Request $request)
    {
        $filtrosBusca = [
            ...$request->all(),
            'status' => StatusEvento::AGENDADO->value,
            'retornar_lista' => true,
        ];

        $retorno = $this->service->index($filtrosBusca);

        $dadosView = ['eventos' => $retorno['dados']];

        return Inertia::render('Evento/Index', $dadosView);
    }

    /**
     * Retornar formulário de criação
     */
    public function create()
    {
        $dadosView = $this->formService->buscarDados(null);

        return Inertia::render('Evento/Create', $dadosView);
    }

    /**
     * Salvar novo recurso
     */
    public function store(StoreRequest $request)
    {
        $this->service->store($request->validated());

        return redirect()->route('eventos.index');
    }

    /**
     * Retornar formulário de edição
     */
    public function edit(Evento $evento)
    {
        $dadosView = $this->formService->buscarDados($evento);

        return Inertia::render('Evento/Edit', $dadosView);
    }

    /**
     * Atualizar recurso
     */
    public function update(UpdateRequest $request, string $id)
    {
        $this->service->update($id, $request->validated());

        return redirect()->route('eventos.index');
    }

    /**
     * Excluir recurso
     */
    public function destroy(string $id)
    {
        $this->service->destroy($id);

        return redirect()->route('eventos.index');
    }
}
