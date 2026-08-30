<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;
use App\Services\Hospital\Service;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Hospital\StoreRequest;
use App\Http\Requests\Web\Hospital\UpdateRequest;
use App\Models\Hospital;
use App\Services\Hospital\Form\Service as FormService;
use App\Helpers\User as UserHelper;
use Illuminate\Http\Request;

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
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user || ! UserHelper::ehGestor($user)) {
            abort(403);
        }

        $filtrosBusca = [
            ...$request->all(),
            'retornar_lista' => true,
        ];

        if (! UserHelper::possuiEscopoGlobal($user)) {
            $filtrosBusca['cidade_id'] = (int) $user->voluntario->cidade_base_id;
        }

        $retorno = $this->service->index($filtrosBusca);

        $dadosView = [
            'hospitais' => $retorno['dados'],
            'pode_editar_dados' => $user->temCargo('administrador'),
        ];

        return Inertia::render('Hospital/Index', $dadosView);
    }

    /**
     * Retornar formulário de criação
     */
    public function create()
    {
        $dadosView = $this->formService->buscarDados(null);

        return Inertia::render('Hospital/Create', $dadosView);
    }

    /**
     * Salvar novo recurso
     */
    public function store(StoreRequest $request)
    {
        $this->service->store($request->all());

        return redirect()->route('hospitais.index');
    }

    /**
     * Retornar formulário de edição
     */
    public function edit(Request $request, Hospital $hospital)
    {
        $dadosView = $this->formService->buscarDados($hospital);

        return Inertia::render('Hospital/Edit', $dadosView);
    }

    /**
     * Atualizar recurso
     */
    public function update(UpdateRequest $request, string $id)
    {
        $this->service->update($id, $request->all());

        return redirect()->route('hospitais.index');
    }

    /**
     * Excluir recurso
     */
    public function destroy(string $id)
    {
        $this->service->destroy($id);

        return redirect()->route('hospitais.index');
    }
}
