<?php

namespace App\Http\Controllers\Web;

use App\Enums\StatusEvento;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Evento\FinalizarRequest;
use App\Http\Requests\Web\Evento\StoreRequest;
use App\Http\Requests\Web\Evento\UpdateRequest;
use App\Models\Evento;
use App\Services\Evento\Form\Service as FormService;
use App\Services\Evento\Service;
use Illuminate\Http\Request;
use App\Enums\StatusInscricao;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EventoController extends Controller
{
    public function __construct(
        private Service $service,
        private FormService $formService,
    ) {}

    public function index(Request $request)
    {
        $filtrosBusca = [
            ...$request->only(['tipo', 'data', 'hora_inicio', 'hora_fim', 'minha_relacao']),
            'status'         => StatusEvento::AGENDADO->value,
            'retornar_lista' => true,
        ];

        $retorno = $this->service->index($filtrosBusca);

        return Inertia::render('Evento/Index', [
            'eventos' => $retorno['dados'],
            'filtros' => $request->only(['tipo', 'data', 'hora_inicio', 'hora_fim', 'minha_relacao']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Evento/Create', $this->formService->buscarDados(null));
    }

    public function store(StoreRequest $request)
    {
        $this->service->store($request->validated());

        return redirect()->route('eventos.index');
    }

    public function show(Evento $evento)
    {
        $evento->load(['cidade', 'criadoPor', 'responsaveis.voluntario', 'participantes.usuario']);

        $userId = Auth::id();

        $inscrito     = $evento->participantes->where('user_id', $userId)->where('status', StatusInscricao::INSCRITO)->isNotEmpty();
        $passouInicio = now()->greaterThanOrEqualTo($evento->data_inicio);

        return Inertia::render('Evento/Show', compact('evento', 'inscrito', 'passouInicio'));
    }

    public function edit(Evento $evento)
    {
        $this->authorize('update', $evento);

        return Inertia::render('Evento/Edit', $this->formService->buscarDados($evento));
    }

    public function update(UpdateRequest $request, Evento $evento)
    {
        $this->authorize('update', $evento);

        $this->service->update((string) $evento->id, $request->validated());

        return redirect()->route('eventos.index');
    }

    public function destroy(Evento $evento)
    {
        $this->authorize('delete', $evento);

        $this->service->destroy((string) $evento->id);

        return redirect()->route('eventos.index');
    }

    public function inscrever(Evento $evento)
    {
        $this->service->inscrever($evento->id, Auth::id());

        return redirect()->back();
    }

    public function cancelarInscricao(Evento $evento)
    {
        $this->service->cancelarInscricao($evento->id, Auth::id());

        return redirect()->back();
    }

    public function paginaFinalizar(Evento $evento)
    {
        $this->authorize('finalizar', $evento);

        $evento->load(['participantes.usuario']);

        return Inertia::render('Evento/Finalizar', [
            'evento'        => $evento,
            'participantes' => $evento->participantes,
        ]);
    }

    public function finalizar(FinalizarRequest $request, Evento $evento)
    {
        $this->authorize('finalizar', $evento);

        $this->service->finalizar($evento->id, $request->validated()['presencas']);

        return redirect()->route('eventos.index');
    }

    public function cancelar(Evento $evento)
    {
        $this->authorize('cancelar', $evento);

        $this->service->cancelarEvento($evento->id);

        return redirect()->route('eventos.index');
    }

    public function dashboard(Request $request)
    {
        $filtros = $request->only(['semestre', 'nome']);
        $retorno = $this->service->dashboard($filtros);

        return Inertia::render('Evento/Dashboard', [
            'participantes' => $retorno['dados']['dados'],
            'semestres'     => $retorno['dados']['semestres'],
            'filtros'       => $filtros,
        ]);
    }
}
