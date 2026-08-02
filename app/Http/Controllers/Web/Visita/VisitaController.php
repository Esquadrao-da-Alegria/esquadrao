<?php

namespace App\Http\Controllers\Web\Visita;

use App\Enums\VisitaStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Visita\StoreRequest;
use App\Http\Requests\Web\Visita\UpdateRequest;
use App\Models\Cidade;
use App\Models\Visita;
use App\Services\Visita\Form\Service as FormService;
use App\Services\Visita\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class VisitaController extends Controller
{
    public function __construct(
        private Service $service,
        private FormService $formService,
    ) {}

    public function index(Request $request): \Inertia\Response
    {
        $mes = $this->normalizarMes($request->query('mes'));

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

        $filtrosBusca = ['mes' => $mes];

        if ($cidadeId !== 'todas') {
            $filtrosBusca['cidade_id'] = $cidadeId;
        }

        $retorno = $this->service->index($filtrosBusca);
        $cidades = Cidade::query()->orderBy('nome')->get(['id', 'nome']);

        return Inertia::render('Visita/Index', [
            'visitas' => $retorno['dados'],
            'mes' => $mes,
            'cidades' => $cidades,
            'cidadeId' => $cidadeId,
            'cidadeUsuarioId' => $cidadeUsuarioId ? (int) $cidadeUsuarioId : null,
        ]);
    }

    public function create(): \Inertia\Response
    {
        $dadosView = $this->formService->buscarDados(null);
        return Inertia::render('Visita/Create', $dadosView);
    }

    public function store(StoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        $retorno = $this->service->store($request->validated());

        if (! $retorno['sucesso']) {
            return back()->withErrors(['geral' => $retorno['erros'][0] ?? 'Erro ao salvar visita.']);
        }

        return redirect()->route('visitas.index');
    }

    public function edit(Visita $visita): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        if (! $this->service->podeEditarVisita(Auth::user(), $visita)) {
            return redirect()->route('visitas.index')
                ->with('mensagem_erro', 'Você não tem permissão para editar esta visita.');
        }

        return Inertia::render('Visita/Edit', $this->formService->buscarDados($visita));
    }

    public function update(UpdateRequest $request, Visita $visita): \Illuminate\Http\RedirectResponse
    {
        if (! $this->service->podeEditarVisita(Auth::user(), $visita)) {
            return redirect()->route('visitas.index')
                ->with('mensagem_erro', 'Você não tem permissão para editar esta visita.');
        }

        $retorno = $this->service->update($visita, $request->validated());

        if (! $retorno['sucesso']) {
            return back()->withErrors(['geral' => $retorno['erros'][0] ?? 'Erro ao atualizar visita.']);
        }

        return redirect()->route('visitas.index');
    }

    public function cancelar(Visita $visita): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $user?->loadMissing('cargos');

        $ehAdministrador = $user?->cargos->contains('slug', 'administrador') ?? false;
        $ehLider = $user !== null && $visita->lider_id !== null && $user->id === $visita->lider_id;

        if (! $ehAdministrador && ! $ehLider) {
            return back()->with('mensagem_erro', 'Você não tem permissão para cancelar esta visita.');
        }

        if ($visita->status === VisitaStatus::Cancelada) {
            return back()->with('mensagem_alerta', 'Esta visita já está cancelada.');
        }

        $visita->update(['status' => VisitaStatus::Cancelada->value]);

        return redirect()->route('visitas.index')
            ->with('mensagem_sucesso', 'Visita cancelada com sucesso!');
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
