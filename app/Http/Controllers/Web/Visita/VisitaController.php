<?php

namespace App\Http\Controllers\Web\Visita;

use App\Enums\VisitaStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Visita\StoreRequest;
use App\Http\Requests\Web\Visita\UpdateRequest;
use App\Models\Cidade;
use App\Models\Evento;
use App\Models\Visita;
use App\Services\Visita\Ajuste\Service as AjusteService;
use App\Services\Visita\Form\Service as FormService;
use App\Services\Visita\Meta\Service as MetaService;
use App\Services\Visita\Service;
use App\Services\Visita\Agenda\Liberacao\Service as LiberacaoAgendaService;
use App\Helpers\User as UserHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VisitaController extends Controller
{
    public function __construct(
        private Service $service,
        private FormService $formService,
        private MetaService $metaService,
    ) {}

    public function index(Request $request): \Inertia\Response
    {
        $mes = $this->normalizarMes($request->query('mes'));

        $user = usuarioAutenticado();
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

        $inicio = Carbon::createFromFormat('!Y-m', $mes)->startOfMonth();
        $fim = $inicio->copy()->endOfMonth();

        $eventos = Evento::with(['responsavel', 'cidade'])
            ->withCount('participantesAtivos')
            ->whereBetween('data_inicio', [$inicio, $fim])
            ->where('status', '!=', 'cancelado')
            ->when($cidadeId !== 'todas', fn ($q) => $q->where('cidade_id', $cidadeId))
            ->orderBy('data_inicio')
            ->get();

        $agendaLiberacao = null;
        $podeGerenciarAgenda = false;
        $acompanhamentoMetas = [];

        if ($cidadeId !== 'todas') {
            $referencia = Carbon::createFromFormat('!Y-m', $mes);
            $liberacaoAgendaService = app(LiberacaoAgendaService::class);
            $agendaLiberacao = $liberacaoAgendaService->situacaoConsulta(
                (int) $cidadeId,
                (int) $referencia->year,
                (int) $referencia->month,
            );
            $podeGerenciarAgenda = $user
                ? $liberacaoAgendaService->podeGerenciarCidade($user, (int) $cidadeId)
                : false;
            $acompanhamentoMetas = $this->metaService->index((int) $cidadeId, $mes);
        }

        return Inertia::render('Visita/Index', [
            'visitas'         => $retorno['dados'],
            'eventos'         => $eventos,
            'mes'             => $mes,
            'cidades'         => $cidades,
            'cidadeId'        => $cidadeId,
            'cidadeUsuarioId' => $cidadeUsuarioId ? (int) $cidadeUsuarioId : null,
            'visitaId'        => $request->integer('visita_id') ?: null,
            'ehGestor'         => $user ? UserHelper::ehGestor($user) : false,
            'agendaLiberacao'  => $agendaLiberacao,
            'podeGerenciarAgenda' => $podeGerenciarAgenda,
            'acompanhamentoMetas' => $acompanhamentoMetas,
        ]);
    }

    public function create(Request $request): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        $mes = $this->normalizarMes($request->query('mes'));
        $cidadeId = $request->integer('cidade_id') ?: usuarioAutenticado()?->voluntario?->cidade_base_id;

        if (! $cidadeId) {
            return redirect()->route('visitas.index', ['mes' => $mes])
                ->with('mensagem_alerta', 'Selecione uma cidade antes de cadastrar uma nova visita.');
        }

        $referencia = Carbon::createFromFormat('!Y-m', $mes);
        $liberado = app(LiberacaoAgendaService::class)->mesEstaLiberado(
            (int) $cidadeId,
            (int) $referencia->year,
            (int) $referencia->month,
        );

        if (! $liberado) {
            return redirect()->route('visitas.index', ['mes' => $mes, 'cidade_id' => $cidadeId])
                ->with('mensagem_alerta', 'O agendamento de novas visitas está bloqueado para o mês selecionado.');
        }

        $dadosView = [
            ...$this->formService->buscarDados(null, (int) $cidadeId),
            'mes_selecionado' => $mes,
            'cidade_selecionada_id' => (int) $cidadeId,
        ];

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

    public function edit(Visita $visita, AjusteService $ajusteService): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        $user = usuarioAutenticado();

        if (! $user || ! $this->service->podeEditarVisita($user, $visita)) {
            return redirect()->route('visitas.index')
                ->with('mensagem_erro', 'Você não tem permissão para editar esta visita.');
        }

        $dados = $this->formService->buscarDados($visita);

        if ($user->temCargo('administrador') && $visita->status === VisitaStatus::Realizada) {
            $dados['ajustes_contabilizacao'] = $ajusteService->index($visita);
        }

        return Inertia::render('Visita/Edit', $dados);
    }

    public function update(UpdateRequest $request, Visita $visita): \Illuminate\Http\RedirectResponse
    {
        $user = usuarioAutenticado();

        if (! $user || ! $this->service->podeEditarVisita($user, $visita)) {
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
        $user = usuarioAutenticado();

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
            Carbon::createFromFormat('!Y-m', $mes);
            return $mes;
        } catch (\Throwable) {
            return now()->format('Y-m');
        }
    }
}
