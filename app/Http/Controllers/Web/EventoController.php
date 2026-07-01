<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Evento\CancelarRequest;
use App\Http\Requests\Web\Evento\StoreRequest;
use App\Http\Requests\Web\Evento\UpdateRequest;
use App\Models\Cidade;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventoController extends Controller
{
    public function index(Request $request)
    {
        $tipo = $request->filled('tipo') ? $request->string('tipo') : null;

        $base = fn () => Evento::with(['responsavel', 'cidade'])->withCount('participantesAtivos')
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo));

        $abertas = $base()
            ->where('status', 'agendado')
            ->where('data_inicio', '>', now())
            ->where(function ($q) {
                $q->whereNull('limite_inscricao')->orWhere('limite_inscricao', '>', now());
            })
            ->orderBy('data_inicio')
            ->get();

        $encerradas = $base()
            ->where('status', 'agendado')
            ->where('data_inicio', '>', now())
            ->whereNotNull('limite_inscricao')
            ->where('limite_inscricao', '<=', now())
            ->orderBy('data_inicio')
            ->get();

        $demais = $base()
            ->where(function ($q) {
                $q->where('status', '!=', 'agendado')
                    ->orWhere('data_inicio', '<=', now());
            })
            ->orderByDesc('data_inicio')
            ->get();

        return Inertia::render('Evento/Index', [
            'abertas' => $abertas,
            'encerradas' => $encerradas,
            'demais' => $demais,
            'filtros' => $request->only(['tipo']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Evento/Create', [
            'responsaveis' => User::orderBy('name')->get(['id', 'name', 'email']),
            'cidades' => Cidade::orderBy('nome')->get(['id', 'nome', 'estado_id']),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $validated = $request->validated();
        $inscreverMe = (bool) ($validated['inscrever_me'] ?? false);
        unset($validated['inscrever_me']);

        $evento = Evento::create([...$validated, 'status' => 'agendado', 'criado_por_id' => $request->user()->id]);

        if ($inscreverMe) {
            $evento->participantes()->syncWithoutDetaching([
                $request->user()->id => ['status' => 'inscrito', 'inscrito_em' => now()],
            ]);
        }

        return redirect()->route('eventos.index')->with('mensagem_sucesso', 'Evento criado com sucesso.');
    }

    public function show(Request $request, Evento $evento)
    {
        $evento->load(['responsavel:id,name,email', 'participantesAtivos:id,name,email', 'cidade'])->loadCount('participantesAtivos');
        $user = $request->user();
        $participacao = $evento->participantes()->where('users.id', $user->id)->first();
        $inscrito = $participacao?->pivot->status === 'inscrito';
        $presencaMarcada = $participacao?->pivot->presenca !== null;
        $podeGerenciar = $user->temCargo('administrador') || $evento->responsavel_id === $user->id;
        return Inertia::render('Evento/Show', [
            'evento' => $evento,
            'inscrito' => $inscrito,
            'presenca_marcada' => $presencaMarcada,
            'pode_gerenciar' => $podeGerenciar,
        ]);
    }

    public function edit(Evento $evento)
    {
        if (! $evento->estaAgendado()) {
            return redirect()->route('eventos.show', $evento)->with('mensagem_erro', 'Apenas eventos agendados podem ser editados.');
        }
        return Inertia::render('Evento/Edit', [
            'evento' => $evento,
            'responsaveis' => User::orderBy('name')->get(['id', 'name', 'email']),
            'cidades' => Cidade::orderBy('nome')->get(['id', 'nome', 'estado_id']),
        ]);
    }

    public function update(UpdateRequest $request, Evento $evento)
    {
        if (! $evento->estaAgendado()) {
            return redirect()->route('eventos.show', $evento)->with('mensagem_erro', 'Apenas eventos agendados podem ser editados.');
        }
        $ativos = $evento->participantesAtivos()->count();
        $limite = $request->validated('limite_participantes');
        if ($limite !== null && (int) $limite < $ativos) {
            return back()->withErrors(['limite_participantes' => 'O limite não pode ser menor que os participantes ativos.'])->withInput();
        }
        $evento->update($request->validated());
        return redirect()->route('eventos.show', $evento)->with('mensagem_sucesso', 'Evento atualizado com sucesso.');
    }

    public function destroy(Evento $evento)
    {
        if ($evento->participantes()->exists()) {
            return back()->with('mensagem_erro', 'Não é possível excluir um evento que possui participantes. Cancele o evento em vez disso.');
        }

        $evento->delete();
        return redirect()->route('eventos.index')->with('mensagem_sucesso', 'Evento excluído com sucesso.');
    }

    public function cancelar(CancelarRequest $request, Evento $evento)
    {
        if ($evento->estaFinalizado()) {
            return redirect()->route('eventos.show', $evento)->with('mensagem_erro', 'Evento finalizado não pode ser cancelado.');
        }
        if ($evento->estaCancelado()) {
            return redirect()->route('eventos.show', $evento)->with('mensagem_erro', 'Este evento já foi cancelado.');
        }
        $evento->update(['status' => 'cancelado', 'motivo_cancelamento' => $request->validated('motivo_cancelamento'), 'cancelado_em' => now(), 'cancelado_por_id' => $request->user()->id]);
        return redirect()->route('eventos.show', $evento)->with('mensagem_sucesso', 'Evento cancelado com sucesso.');
    }
}