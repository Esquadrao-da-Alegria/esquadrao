<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Visita\StoreRequest;
use App\Http\Requests\Web\Visita\UpdateRequest;
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

        $filtrosBusca = [
            'mes' => $mes,
        ];

        $retorno = $this->service->index($filtrosBusca);

        return Inertia::render('Visita/Index', [
            'visitas' => $retorno['dados'],
            'mes'     => $mes,
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
            return redirect()
                ->route('visitas.index')
                ->with('mensagem_erro', 'Você não tem permissão para editar esta visita.');
        }

        $dadosView = $this->formService->buscarDados($visita);

        return Inertia::render('Visita/Edit', $dadosView);
    }

    public function update(UpdateRequest $request, Visita $visita): \Illuminate\Http\RedirectResponse
    {
        if (! $this->service->podeEditarVisita(Auth::user(), $visita)) {
            return redirect()
                ->route('visitas.index')
                ->with('mensagem_erro', 'Você não tem permissão para editar esta visita.');
        }

        $retorno = $this->service->update($visita, $request->validated());

        if (! $retorno['sucesso']) {
            return back()->withErrors(['geral' => $retorno['erros'][0] ?? 'Erro ao atualizar visita.']);
        }

        return redirect()->route('visitas.index');
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
