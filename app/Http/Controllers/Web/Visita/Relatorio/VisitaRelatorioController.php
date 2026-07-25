<?php

namespace App\Http\Controllers\Web\Visita\Relatorio;

use App\Enums\VisitaStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Visita\Relatorio\StoreRequest;
use App\Http\Requests\Web\Visita\Relatorio\UpdateRequest;
use App\Models\Visita;
use App\Models\VisitaRelatorio;
use App\Services\Visita\Relatorio\Service;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class VisitaRelatorioController extends Controller
{
    public function __construct(private Service $service) {}

    public function index(Visita $visita): \Inertia\Response
    {
        $visita = $this->carregarContextoVisita($visita);
        $retorno = $this->service->index($visita);

        return Inertia::render('Visita/Relatorio/Index', [
            'visita'     => $visita,
            'relatorios' => $retorno['dados'],
        ]);
    }

    public function create(Visita $visita): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        if ($visita->status === VisitaStatus::Cancelada) {
            return redirect()
                ->route('visitas.relatorios.index', $visita)
                ->with('mensagem_erro', 'Não é possível criar relatório para visita cancelada.');
        }

        $visita = $this->carregarContextoVisita($visita);

        return Inertia::render('Visita/Relatorio/Create', [
            'visita'            => $visita,
            'foraDoPrazoAviso'  => $this->service->calcularForaDoPrazo($visita, now()),
        ]);
    }

    public function store(StoreRequest $request, Visita $visita): \Illuminate\Http\RedirectResponse
    {
        $retorno = $this->service->store($visita, $request->validated());

        if (! $retorno['sucesso']) {
            return back()->withErrors(['geral' => $retorno['erros'][0] ?? 'Erro ao salvar relatório.']);
        }

        $relatorio = $retorno['dados']['model'];

        return redirect()->route('visitas.relatorios.show', [$visita, $relatorio]);
    }

    public function show(Visita $visita, VisitaRelatorio $relatorio): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        $retorno = $this->service->show($visita, $relatorio);

        if (! $retorno['sucesso']) {
            return redirect()
                ->route('visitas.relatorios.index', $visita)
                ->with('mensagem_erro', $retorno['erros'][0] ?? 'Relatório não encontrado.');
        }

        $visita = $this->carregarContextoVisita($visita);
        $relatorio = $retorno['dados']['model'];

        return Inertia::render('Visita/Relatorio/Show', [
            'visita'     => $visita,
            'relatorio'  => $relatorio,
            'podeEditar' => $this->service->podeEditarRelatorio(Auth::user(), $visita, $relatorio),
        ]);
    }

    public function edit(Visita $visita, VisitaRelatorio $relatorio): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        if ($visita->status === VisitaStatus::Cancelada) {
            return redirect()
                ->route('visitas.relatorios.index', $visita)
                ->with('mensagem_erro', 'Não é possível editar relatório de visita cancelada.');
        }

        if (! $this->service->podeEditarRelatorio(Auth::user(), $visita, $relatorio)) {
            return redirect()
                ->route('visitas.relatorios.show', [$visita, $relatorio])
                ->with('mensagem_erro', 'Você não tem permissão para editar este relatório.');
        }

        $visita = $this->carregarContextoVisita($visita);
        $relatorio->loadMissing('autor:id,name');

        return Inertia::render('Visita/Relatorio/Edit', [
            'visita'           => $visita,
            'relatorio'        => $relatorio,
            'foraDoPrazoAviso' => $relatorio->fora_do_prazo,
        ]);
    }

    public function update(UpdateRequest $request, Visita $visita, VisitaRelatorio $relatorio): \Illuminate\Http\RedirectResponse
    {
        $retorno = $this->service->update($visita, $relatorio, $request->validated(), Auth::user());

        if (! $retorno['sucesso']) {
            return back()->withErrors(['geral' => $retorno['erros'][0] ?? 'Erro ao atualizar relatório.']);
        }

        $relatorio = $retorno['dados']['model'];

        return redirect()->route('visitas.relatorios.show', [$visita, $relatorio]);
    }

    public function pdf(Visita $visita, VisitaRelatorio $relatorio): \Symfony\Component\HttpFoundation\Response
    {
        return $this->service->pdf($visita, $relatorio);
    }

    private function carregarContextoVisita(Visita $visita): Visita
    {
        $visita->loadMissing([
            'hospital:id,nome,cidade_id',
            'hospital.alas:id,hospital_id,nome',
            'alaUnidade:id,nome,hospital_id',
            'lider:id,name',
            'participantes.voluntario:id,name',
        ]);

        return $visita;
    }
}
