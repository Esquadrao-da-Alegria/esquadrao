<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Voluntario\StoreConviteRequest;
use App\Http\Requests\Web\Voluntario\StoreRequest;
use App\Http\Requests\Web\Voluntario\UpdateRequest;
use App\Models\Cidade;
use App\Models\Voluntario;
use App\Services\Voluntario\Form\Service as FormService;
use App\Services\Voluntario\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $user = Auth::user();
        $user?->loadMissing('voluntario');
        $cidadeUsuarioId = $user?->voluntario?->cidade_base_id;

        if ($request->has('cidade_id')) {
            $cidadeFiltro = $request->query('cidade_id');
            if ($cidadeFiltro === 'todas' || $cidadeFiltro === '' || $cidadeFiltro === null) {
                $cidadeId = 'todas';
            } else {
                $cidadeId = (int) $cidadeFiltro;
            }
        } else {
            $cidadeId = $cidadeUsuarioId ? (int) $cidadeUsuarioId : 'todas';
        }

        $filtrosBusca = [
            ...$request->all(),
            'retornar_lista' => true,
        ];

        if ($cidadeId !== 'todas') {
            $filtrosBusca['cidade_id'] = $cidadeId;
        }

        $retorno = $this->service->index($filtrosBusca);
        $cidades = Cidade::query()->orderBy('nome')->get(['id', 'nome']);

        $dadosView = [
            'voluntarios' => $retorno['dados'],
            'contadores' => $retorno['contadores'] ?? [],
            'cidades' => $cidades,
            'cidadeId' => $cidadeId,
            'cidadeUsuarioId' => $cidadeUsuarioId ? (int) $cidadeUsuarioId : null,
            'filtros' => [
                'aba' => $request->string('aba', 'voluntarios')->toString(),
                'busca' => $request->string('busca')->toString(),
                'status' => $request->string('status', 'todos')->toString(),
                'cidade_id' => $cidadeId,
            ],
        ];

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

    public function storeConvite(StoreConviteRequest $request)
    {
        $this->service->storeConvite($request->validated());

        return redirect()->route('voluntarios.index', ['aba' => 'convidados']);
    }

    public function edit(Request $request, Voluntario $voluntario)
    {
        $dadosView = $this->formService->buscarDados($voluntario);

        return Inertia::render('Voluntario/Edit', $dadosView);
    }

    public function update(UpdateRequest $request, Voluntario $voluntario)
    {
        $this->service->update($voluntario->id, $request->validated());

        return redirect()->route('voluntarios.index');
    }

    public function destroy(Request $request, Voluntario $voluntario)
    {
        if ($request->user()?->voluntario_id === $voluntario->id) {
            session()->flash('mensagem_erro', 'Você não pode excluir a própria conta por aqui.');

            return redirect()->route('voluntarios.index');
        }

        $this->service->destroy($voluntario->id);

        return redirect()->route('voluntarios.index', ['aba' => 'voluntarios']);
    }

    public function reenviarConvite(Voluntario $voluntario)
    {
        $this->service->reenviarConvite($voluntario);

        return redirect()->route('voluntarios.index', ['aba' => 'convidados']);
    }

    public function cancelarConvite(Voluntario $voluntario)
    {
        $this->service->cancelarConvite($voluntario);

        return redirect()->route('voluntarios.index', ['aba' => 'convidados']);
    }
}
