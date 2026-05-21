<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Voluntario\StoreRequest;
use App\Http\Requests\Web\Voluntario\UpdateRequest;
use App\Models\User;
use App\Services\Voluntario\Form\Service as FormService;
use App\Services\Voluntario\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VoluntarioController extends Controller
{
    public function __construct(
        private Service $service,
        private FormService $formService,
    ) {
        //
    }

    public function index(Request $request)
    {
        $filtrosBusca = [
            ...$request->all(),
            'retornar_lista' => true,
        ];

        $retorno = $this->service->index($filtrosBusca);

        $dadosView = ['voluntarios' => $retorno['dados']];

        return Inertia::render('Voluntario/Index', $dadosView);
    }

    public function create()
    {
        $dadosView = $this->formService->buscarDados(null);

        return Inertia::render('Voluntario/Create', $dadosView);
    }

    public function store(StoreRequest $request)
    {
        $this->service->store($request->validated());

        return redirect()->route('voluntarios.index');
    }

    public function edit(Request $request, User $voluntario)
    {
        $dadosView = $this->formService->buscarDados($voluntario);

        return Inertia::render('Voluntario/Edit', $dadosView);
    }

    public function update(UpdateRequest $request, User $voluntario)
    {
        $this->service->update($voluntario->id, $request->validated());

        return redirect()->route('voluntarios.index');
    }

    public function destroy(Request $request, User $voluntario)
    {
        if ($voluntario->id === $request->user()->id) {
            session()->flash('mensagem_erro', 'Você não pode excluir a própria conta por aqui.');

            return redirect()->route('voluntarios.index');
        }

        $this->service->destroy($voluntario->id);

        return redirect()->route('voluntarios.index');
    }
}
