<?php

namespace App\Http\Controllers\Web\Hospital\Meta;

// CONTROLLERS
use App\Http\Controllers\Controller as BaseController;

// HELPERS
use App\Helpers\MetaHospital as MetaHospitalHelper;

// FORM REQUESTS
use App\Http\Requests\Web\Hospital\Meta\IndexRequest;
use App\Http\Requests\Web\Hospital\Meta\UpdateRequest;

// SERVICES
use App\Services\Hospital\Meta\Service;

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
            $ano = (int) $request->input('ano', now()->year);
            $mes = (int) $request->input('mes', now()->month);

            return Inertia::render('Hospital/Meta/Index', [
                'ano'       => $ano,
                'mes'       => $mes,
                'semanas'   => MetaHospitalHelper::semanasDoMes($ano, $mes),
                'hospitais' => [],
            ])->with('mensagem_erro', $retorno['erros'][0] ?? 'Erro ao carregar metas.');
        }

        return Inertia::render('Hospital/Meta/Index', $retorno['dados']);
    }

    public function update(UpdateRequest $request): RedirectResponse
    {
        $retorno = $this->service->update($request->user(), $request->validated());

        if (! $retorno['sucesso']) {
            $erros = $retorno['erros'] !== []
                ? $retorno['erros']
                : ['Erro ao salvar metas.'];

            return back()->withErrors(['geral' => $erros]);
        }

        return redirect()
            ->route('hospitais.metas.index', [
                'ano' => $request->integer('ano'),
                'mes' => $request->integer('mes'),
            ]);
    }
}
