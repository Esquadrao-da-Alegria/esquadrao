<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Evento\CancelarRequest;
use App\Http\Requests\Web\Evento\StoreRequest;
use App\Http\Requests\Web\Evento\UpdateRequest;
use App\Models\Cidade;
use App\Models\Evento;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EventoController extends Controller
{
    public function index(Request $request)
    {
        $mes = $this->normalizarMes($request->query('mes'));

        $inicio = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
        $fim = $inicio->copy()->endOfMonth();

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

        $eventos = Evento::with(['responsavel', 'cidade'])->withCount('participantesAtivos')
            ->whereBetween('data_inicio', [$inicio, $fim])
            ->when($cidadeId !== 'todas', fn ($q) => $q->where('cidade_id', $cidadeId))
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->string('tipo')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('data_inicio')->get();

        $cidades = Cidade::orderBy('nome')->get(['id', 'nome']);

        return Inertia::render('Evento/Index', [
            'eventos' => $eventos,
            'mes' => $mes,
            'cidades' => $cidades,
            'cidadeId' => $cidadeId,
            'cidadeUsuarioId' => $cidadeUsuarioId ? (int) $cidadeUsuarioId : null,
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
        $podeGerenciar = $user->temCargo('administrador') || $evento->responsavel_id === $user->id || $evento->criado_por_id === $user->id;

        return Inertia::render('Evento/Show', [
            'evento' => $evento,
            'inscrito' => $inscrito,
            'presenca_marcada' => $presencaMarcada,
            'pode_gerenciar' => $podeGerenciar,
            'ajustes_participacao' => $podeGerenciar ? $evento->ajustesParticipacao()->with(['voluntario:id,name', 'administrador:id,name'])->latest()->get() : [],
            'voluntarios_ajuste' => $podeGerenciar ? User::query()->whereNotNull('voluntario_id')->whereHas('voluntario', fn ($query) => $query->where('status', 'ativo'))->orderBy('name')->get(['id', 'name']) : [],
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

    private function normalizarMes(?string $mes): string
    {
        if (! $mes) {
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
