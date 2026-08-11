<?php

namespace App\Services\Dashboard\Visita\Participante;

use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\User;
use App\Queries\Dashboard\Visita\Participante\Queries;
use App\Services\Dashboard\Visita\Participante\Compensacao\Service as CompensacaoService;
use App\Services\Dashboard\Visita\Participante\Meta\Service as MetaService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Pagination\LengthAwarePaginator;

class Service
{
    public function __construct(
        private Queries $queries,
        private MetaService $metaService,
        private CompensacaoService $compensacaoService,
    ) {}

    public function index(User $gestor, array $filtros): array
    {
        $filtros = $this->normalizarFiltros($gestor, $filtros);
        $dados = $this->queries->index($filtros);
        $mesesCalculo = $this->meses($filtros['inicio']->copy()->subMonth(), $filtros['fim']);
        $mesesExibidos = array_slice($mesesCalculo, 1);

        $linhas = $dados['usuarios']->map(function (User $user) use ($dados, $mesesCalculo, $mesesExibidos, $filtros) {
            $tipo = $this->metaService->tipo($user);
            $validas = $dados['visitasValidas']->where('voluntario_id', $user->id);
            $porMes = $validas->groupBy(fn ($visita) => substr($visita->inicio_em, 0, 7))->map->count()->all();
            $compensacoes = $tipo === 'visitas'
                ? array_values(array_filter(
                    $this->compensacaoService->calcular($porMes, $mesesCalculo),
                    fn (array $mes) => in_array($mes['mes'], $mesesExibidos, true)
                ))
                : [];
            $presencas = $this->presencas($user, $dados, $filtros);
            $ultimaAtividade = $this->ultimaAtividade($validas, $dados['presencas']->where('user_id', $user->id));
            $diasSemAtividade = $ultimaAtividade ? (int) $ultimaAtividade->diffInDays(now()) : null;
            $situacao = $this->situacao($tipo, $compensacoes, $presencas, $diasSemAtividade);
            $participacoes = $dados['participacoes']->where('voluntario_id', $user->id);

            return [
                'id' => $user->id,
                'nome' => $user->voluntario->nome_completo,
                'cidade' => $user->voluntario->cidadeBase?->nome ?? 'Sem cidade base',
                'cidade_id' => $user->voluntario->cidade_base_id,
                'cargos' => $user->cargos->pluck('nome')->values(),
                'tipo_atuacao' => $tipo,
                'visitas_validas' => $validas->whereBetween('inicio_em', [$filtros['inicio'], $filtros['fim']])->count(),
                'meta_mensal' => $tipo === 'visitas' ? MetaService::META_VISITAS : null,
                'saldo_atual' => $compensacoes ? $compensacoes[array_key_last($compensacoes)]['saldo'] : null,
                'compensacao_atual' => $compensacoes ? $compensacoes[array_key_last($compensacoes)]['situacao'] : null,
                'compensacoes' => $compensacoes,
                'reunioes' => $presencas['reuniao'],
                'oficinas' => $presencas['oficina'],
                'ultima_atividade' => $ultimaAtividade?->toIso8601String(),
                'dias_sem_atividade' => $diasSemAtividade,
                'relatorios_pendentes' => $participacoes->where('possui_relatorio', 0)->count(),
                'relatorios_fora_prazo' => $participacoes->where('possui_relatorio', 1)->where('possui_relatorio_no_prazo', 0)->count(),
                'situacao' => $situacao,
            ];
        })->when($filtros['tipo_atuacao'], fn ($itens, $tipo) => $itens->where('tipo_atuacao', $tipo))
            ->when($filtros['situacao'], fn ($itens, $situacao) => $itens->where('situacao', $situacao))
            ->when($filtros['atividade'] === 'visitas', fn ($itens) => $itens->where('visitas_validas', '>', 0))
            ->when($filtros['atividade'] === 'reunioes', fn ($itens) => $itens->filter(fn ($item) => $item['reunioes']['presencas'] > 0))
            ->when($filtros['atividade'] === 'oficinas', fn ($itens) => $itens->filter(fn ($item) => $item['oficinas']['presencas'] > 0))
            ->values();

        $pagina = LengthAwarePaginator::resolveCurrentPage();
        $paginacao = new LengthAwarePaginator(
            $linhas->forPage($pagina, 15)->values(),
            $linhas->count(),
            15,
            $pagina,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return [
            'participantes' => $paginacao,
            'indicadores' => $this->indicadores($linhas),
            'filtros' => $this->filtrosView($filtros),
            'opcoes' => $this->opcoes($gestor, $filtros),
            'escopo_global' => $this->possuiEscopoGlobal($gestor),
        ];
    }

    public function show(User $gestor, User $voluntario, array $filtros): array
    {
        $gestor->loadMissing(['cargos', 'voluntario']);
        $voluntario->loadMissing('voluntario');

        if (! $this->possuiEscopoGlobal($gestor)
            && (int) $voluntario->voluntario?->cidade_base_id !== (int) $gestor->voluntario->cidade_base_id) {
            abort(403);
        }

        $filtros['participante_id'] = $voluntario->id;
        $resultado = $this->index($gestor, $filtros);
        $participante = collect($resultado['participantes']->items())->first();

        abort_if(! $participante, 404);

        $normalizados = $this->normalizarFiltros($gestor, $filtros);
        $dados = $this->queries->show($normalizados);
        $participacoes = $dados['participacoes']->where('voluntario_id', $voluntario->id)->map(fn ($item) => [
            'visita_id' => (int) $item->visita_id,
            'data' => $item->inicio_em,
            'hospital' => $item->hospital,
            'outra_cidade' => (int) $item->cidade_id !== (int) $participante['cidade_id'],
            'motivo' => ! $item->possui_relatorio
                ? ($item->tipo_participacao === 'palhaco' ? 'Relatório do grupo de palhaços pendente' : 'Relatório pessoal pendente')
                : (! $item->possui_relatorio_no_prazo
                    ? 'Relatório aplicável enviado fora do prazo'
                    : ($item->tipo_participacao === 'palhaco' && ! $item->relatorio_proprio_no_prazo
                        ? 'Visita válida por relatório do grupo de palhaços'
                        : 'Visita válida')),
            'valida' => (bool) $item->possui_relatorio_no_prazo,
        ])->values();

        return [
            'participante' => $participante,
            'participacoes' => $participacoes,
            'presencas' => $dados['presencas']->where('user_id', $voluntario->id)->values(),
            'filtros' => $resultado['filtros'],
        ];
    }

    private function normalizarFiltros(User $gestor, array $filtros): array
    {
        $ano = (int) $filtros['ano'];
        $tipo = $filtros['periodo_tipo'];

        if ($tipo === 'mes') {
            $inicio = Carbon::create($ano, (int) $filtros['mes'])->startOfMonth();
            $fim = $inicio->copy()->endOfMonth();
        } elseif ($tipo === 'semestre') {
            $inicio = Carbon::create($ano, (int) $filtros['semestre'] === 1 ? 1 : 7)->startOfMonth();
            $fim = $inicio->copy()->addMonths(5)->endOfMonth();
        } else {
            $inicio = Carbon::create($ano)->startOfYear();
            $fim = $inicio->copy()->endOfYear();
        }

        $gestor->loadMissing(['cargos', 'voluntario']);
        $cidadeId = isset($filtros['cidade_id']) ? (int) $filtros['cidade_id'] : null;

        if ($this->possuiEscopoGlobal($gestor)
            && ! ($filtros['visao_global'] ?? false)
            && ! $cidadeId
            && $gestor->voluntario?->cidade_base_id) {
            $cidadeId = (int) $gestor->voluntario->cidade_base_id;
        }

        if (! $this->possuiEscopoGlobal($gestor)) {
            $cidadeGestor = (int) $gestor->voluntario->cidade_base_id;
            abort_if($cidadeId && $cidadeId !== $cidadeGestor, 403);
            $cidadeId = $cidadeGestor;
        }

        return [
            ...$filtros,
            'cidade_id' => $cidadeId,
            'visao_global' => $this->possuiEscopoGlobal($gestor) ? (bool) ($filtros['visao_global'] ?? false) : false,
            'participante_id' => isset($filtros['participante_id']) ? (int) $filtros['participante_id'] : null,
            'cargo_id' => isset($filtros['cargo_id']) ? (int) $filtros['cargo_id'] : null,
            'tipo_atuacao' => $filtros['tipo_atuacao'] ?? null,
            'situacao' => $filtros['situacao'] ?? null,
            'atividade' => $filtros['atividade'] ?? null,
            'inicio' => $inicio,
            'fim' => $fim,
        ];
    }

    private function presencas(User $user, array $dados, array $filtros): array
    {
        $eventosCidade = $dados['eventos']->where('cidade_id', $user->voluntario->cidade_base_id);
        $presentes = $dados['presencas']->where('user_id', $user->id);
        $resultado = [];

        foreach (['reuniao', 'oficina'] as $tipo) {
            $eventos = $eventosCidade->where('tipo', $tipo);
            $total = $eventos->count();
            $incompleto = $eventos->contains(fn ($evento) => (bool) $evento->presencas_incompletas);
            $quantidade = $presentes->where('tipo', $tipo)->whereIn('evento_id', $eventos->pluck('id'))->count();
            $resultado[$tipo] = [
                'oferecidos' => $total,
                'presencas' => $quantidade,
                'percentual' => $total > 0 && ! $incompleto ? round($quantidade / $total * 100, 1) : null,
                'dados_incompletos' => $total === 0 || $incompleto,
            ];
        }

        return $resultado;
    }

    private function situacao(string $tipo, array $compensacoes, array $presencas, ?int $dias): string
    {
        if (in_array($tipo, ['administrativo', 'dados_insuficientes'], true)) return 'dados_insuficientes';
        if ($tipo === 'isento') return 'isento';
        if ($dias === null || $dias >= MetaService::INATIVIDADE_DIAS) return 'requer_analise';
        if (collect($compensacoes)->contains('situacao', 'requer_analise')) return 'requer_analise';

        if (collect($compensacoes)->contains('situacao', 'compensacao_pendente')) return 'compensacao_pendente';
        if (collect($compensacoes)->contains('situacao', 'atencao')) return 'atencao';
        if (collect($presencas)->contains(fn ($item) => $item['percentual'] !== null && $item['percentual'] < MetaService::META_PRESENCA)) return 'atencao';

        return 'dentro_meta';
    }

    private function ultimaAtividade($visitas, $presencas): ?Carbon
    {
        $datas = collect($visitas->pluck('inicio_em'))->merge($presencas->pluck('data_inicio'))->filter();
        return $datas->isEmpty() ? null : Carbon::parse($datas->max());
    }

    private function indicadores($linhas): array
    {
        $sujeitosMeta = $linhas->where('tipo_atuacao', 'visitas');
        $reunioes = $linhas->pluck('reunioes.percentual')->filter(fn ($valor) => $valor !== null);
        $oficinas = $linhas->pluck('oficinas.percentual')->filter(fn ($valor) => $valor !== null);

        return [
            'total' => $linhas->count(),
            'dentro_meta' => $linhas->where('situacao', 'dentro_meta')->count(),
            'atencao' => $linhas->where('situacao', 'atencao')->count(),
            'compensacao_pendente' => $linhas->where('situacao', 'compensacao_pendente')->count(),
            'requer_analise' => $linhas->where('situacao', 'requer_analise')->count(),
            'isentos' => $linhas->where('situacao', 'isento')->count(),
            'dados_insuficientes' => $linhas->where('situacao', 'dados_insuficientes')->count(),
            'visitas_validas' => $linhas->sum('visitas_validas'),
            'sem_visita' => $linhas->where('tipo_atuacao', 'visitas')->where('visitas_validas', 0)->count(),
            'abaixo_meta' => $linhas->whereIn('situacao', ['atencao', 'compensacao_pendente', 'requer_analise'])->count(),
            'media_visitas' => $sujeitosMeta->isNotEmpty() ? round($sujeitosMeta->avg('visitas_validas'), 1) : 0,
            'presenca_media_reunioes' => $reunioes->isNotEmpty() ? round($reunioes->avg(), 1) : null,
            'presenca_media_oficinas' => $oficinas->isNotEmpty() ? round($oficinas->avg(), 1) : null,
        ];
    }

    private function meses(Carbon $inicio, Carbon $fim): array
    {
        return collect(CarbonPeriod::create($inicio->copy()->startOfMonth(), '1 month', $fim->copy()->startOfMonth()))
            ->map(fn (Carbon $mes) => $mes->format('Y-m'))->all();
    }

    private function filtrosView(array $filtros): array
    {
        return collect($filtros)->except(['inicio', 'fim'])->all();
    }

    private function opcoes(User $gestor, array $filtros): array
    {
        $cidades = $this->possuiEscopoGlobal($gestor)
            ? Cidade::query()->orderBy('nome')->get(['id', 'nome'])
            : Cidade::query()->whereKey($filtros['cidade_id'])->get(['id', 'nome']);

        return [
            'cidades' => $cidades,
            'cargos' => Cargo::query()->orderBy('nome')->get(['id', 'nome']),
            'participantes' => User::query()
                ->whereNotNull('voluntario_id')
                ->when($filtros['cidade_id'], fn ($query, int $cidadeId) => $query->whereHas('voluntario', fn ($q) => $q->where('cidade_base_id', $cidadeId)))
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    private function possuiEscopoGlobal(User $user): bool
    {
        $user->loadMissing('cargos');
        return $user->cargos->contains(fn ($cargo) => in_array($cargo->slug, ['administrador', 'coordenador_geral'], true));
    }
}
