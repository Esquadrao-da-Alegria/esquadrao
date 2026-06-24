<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Evento\CancelarRequest;
use App\Http\Requests\Web\Evento\StoreRequest;
use App\Http\Requests\Web\Evento\UpdateRequest;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventoController extends Controller
{
    public function index(Request $request)
    {
        $eventos = Evento::with('responsavel')->withCount('participantesAtivos')
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->string('tipo')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('data_inicio')->get();

        return Inertia::render('Evento/Index', ['eventos' => $eventos, 'filtros' => $request->only(['tipo', 'status'])]);
    }

    public function create()
    {
        return Inertia::render('Evento/Create', ['responsaveis' => User::orderBy('name')->get(['id', 'name', 'email'])]);
    }

    public function store(StoreRequest $request)
    {
        Evento::create([...$request->validated(), 'status' => 'agendado', 'criado_por_id' => $request->user()->id]);
        return redirect()->route('eventos.index')->with('mensagem_sucesso', 'Evento criado com sucesso.');
    }

    public function show(Request $request, Evento $evento)
    {
        $evento->load(['responsavel:id,name,email', 'participantesAtivos:id,name,email'])->loadCount('participantesAtivos');
        $inscrito = $evento->participantesAtivos()->where('users.id', $request->user()->id)->exists();
        return Inertia::render('Evento/Show', ['evento' => $evento, 'inscrito' => $inscrito]);
    }

    public function edit(Evento $evento)
    {
        if ($evento->status === 'cancelado') {
            return redirect()->route('eventos.show', $evento)->with('mensagem_erro', 'Este evento foi cancelado.');
        }
        return Inertia::render('Evento/Edit', ['evento' => $evento, 'responsaveis' => User::orderBy('name')->get(['id', 'name', 'email'])]);
    }

    public function update(UpdateRequest $request, Evento $evento)
    {
        if ($evento->status === 'cancelado') {
            return redirect()->route('eventos.show', $evento)->with('mensagem_erro', 'Este evento foi cancelado.');
        }
        $ativos = $evento->participantesAtivos()->count();
        $limite = $request->validated('limite_participantes');
        if ($limite !== null && (int) $limite < $ativos) {
            return back()->withErrors(['limite_participantes' => 'O limite não pode ser menor que os participantes ativos.'])->withInput();
        }
        $evento->update($request->validated());
        return redirect()->route('eventos.show', $evento)->with('mensagem_sucesso', 'Evento atualizado com sucesso.');
    }

    public function cancelar(CancelarRequest $request, Evento $evento)
    {
        if ($evento->status === 'cancelado') {
            return redirect()->route('eventos.show', $evento)->with('mensagem_erro', 'Este evento foi cancelado.');
        }
        $evento->update(['status' => 'cancelado', 'motivo_cancelamento' => $request->validated('motivo_cancelamento'), 'cancelado_em' => now(), 'cancelado_por_id' => $request->user()->id]);
        return redirect()->route('eventos.show', $evento)->with('mensagem_sucesso', 'Evento cancelado com sucesso.');
    }
}
