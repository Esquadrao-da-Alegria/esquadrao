<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\RelatorioVisita\StoreRequest;
use App\Http\Requests\Web\RelatorioVisita\UpdateRequest;
use App\Models\RelatorioVisita;
use App\Models\Visita;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RelatorioVisitaController extends Controller
{
    public function index(Visita $visita): Response
    {
        $this->carregarVisita($visita);

        return Inertia::render('Visita/Relatorio/Index', [
            'visita' => $visita,
            'relatorios' => $visita->relatorios()->with('autor:id,name')->latest('enviado_em')->get(),
        ]);
    }

    public function create(Visita $visita): Response
    {
        $this->carregarVisita($visita);

        return Inertia::render('Visita/Relatorio/Create', [
            'visita' => $visita,
            'foraDoPrazoAviso' => now()->gt($visita->fim_em->copy()->addHours(48)),
        ]);
    }

    public function store(StoreRequest $request, Visita $visita): RedirectResponse
    {
        $enviadoEm = now();

        $relatorio = $visita->relatorios()->create([
            ...$request->validated(),
            'autor_id' => $request->user()->id,
            'enviado_em' => $enviadoEm,
            'fora_do_prazo' => $enviadoEm->gt($visita->fim_em->copy()->addHours(48)),
        ]);

        return redirect()->route('visitas.relatorios.show', [$visita, $relatorio])
            ->with('mensagem_sucesso', 'Relatório criado com sucesso.');
    }

    public function show(Visita $visita, RelatorioVisita $relatorio): Response
    {
        $this->garantirPertencimento($visita, $relatorio);
        $this->carregarVisita($visita);
        $relatorio->load('autor:id,name');

        return Inertia::render('Visita/Relatorio/Show', [
            'visita' => $visita,
            'relatorio' => $relatorio,
            'podeEditar' => $this->podeEditar(request(), $relatorio),
        ]);
    }

    public function edit(Request $request, Visita $visita, RelatorioVisita $relatorio): Response
    {
        $this->garantirPertencimento($visita, $relatorio);
        abort_unless($this->podeEditar($request, $relatorio), 403);
        $this->carregarVisita($visita);

        return Inertia::render('Visita/Relatorio/Edit', [
            'visita' => $visita,
            'relatorio' => $relatorio,
            'foraDoPrazoAviso' => $relatorio->fora_do_prazo,
        ]);
    }

    public function update(UpdateRequest $request, Visita $visita, RelatorioVisita $relatorio): RedirectResponse
    {
        $this->garantirPertencimento($visita, $relatorio);
        abort_unless($this->podeEditar($request, $relatorio), 403);

        $relatorio->update($request->validated());

        return redirect()->route('visitas.relatorios.show', [$visita, $relatorio])
            ->with('mensagem_sucesso', 'Relatório atualizado com sucesso.');
    }

    private function garantirPertencimento(Visita $visita, RelatorioVisita $relatorio): void
    {
        abort_unless((int) $relatorio->visita_id === (int) $visita->id, 404);
    }

    private function podeEditar(Request $request, RelatorioVisita $relatorio): bool
    {
        $user = $request->user();

        return (int) $relatorio->autor_id === (int) $user->id
            || $user->temCargo('administrador');
    }

    private function carregarVisita(Visita $visita): void
    {
        $visita->loadMissing([
            'hospital:id,nome,cidade_id',
            'alaUnidade:id,nome,hospital_id',
            'lider:id,name',
            'participantes.voluntario:id,name',
        ]);
    }
}
