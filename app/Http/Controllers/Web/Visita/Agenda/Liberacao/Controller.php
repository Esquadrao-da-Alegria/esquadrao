<?php

namespace App\Http\Controllers\Web\Visita\Agenda\Liberacao;

// CONTROLLERS
use App\Http\Controllers\Controller as BaseController;

// FORM REQUESTS
use App\Http\Requests\Web\Visita\Agenda\Liberacao\IndexRequest;
use App\Http\Requests\Web\Visita\Agenda\Liberacao\UpdateRequest;

// SERVICES
use App\Services\Visita\Agenda\Liberacao\Service;

// HTTP
use Illuminate\Http\RedirectResponse;

// INERTIA
use Inertia\Inertia;
use Inertia\Response;

class Controller extends BaseController
{
    public function __construct(private Service $service) {}

    public function index(IndexRequest $request): Response
    {
        $retorno = $this->service->index($request->user(), $request->validated());

        if (! $retorno['sucesso']) {
            return Inertia::render('Visita/Agenda/Liberacao/Index', [
                'ano'   => (int) $request->input('ano', now()->year),
                'meses' => [],
            ])->with('mensagem_erro', $retorno['erros'][0] ?? 'Erro ao carregar liberações.');
        }

        return Inertia::render('Visita/Agenda/Liberacao/Index', $retorno['dados']);
    }

    public function update(UpdateRequest $request): RedirectResponse
    {
        $dados = $request->validated();
        $retorno = $this->service->update($request->user(), $dados);
        $destino = route('visitas.index', [
            'mes' => sprintf('%04d-%02d', $dados['ano'], $dados['mes']),
            'cidade_id' => $dados['cidade_id'],
        ]);

        if (! $retorno['sucesso']) {
            return redirect($destino)
                ->withErrors(['geral' => $retorno['erros'][0] ?? 'Erro ao atualizar liberação da agenda.']);
        }

        return redirect($destino);
    }
}
