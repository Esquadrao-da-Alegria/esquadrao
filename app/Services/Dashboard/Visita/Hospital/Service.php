<?php

namespace App\Services\Dashboard\Visita\Hospital;

use App\Queries\Dashboard\Visita\Hospital\Queries;
use App\Helpers\MetaHospital as MetaHospitalHelper;
use App\Helpers\Visita as VisitaHelper;
use App\Models\Ala;
use App\Models\Cidade;
use App\Models\Hospital;
use App\Models\MetaMensalHospital;
use App\Models\MetaSemanalHospital;
use App\Models\User;
use App\Models\Visita;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Service
{
    public function __construct(private Queries $queries) {}

    public function index(User $user, array $filtros): array
    {
        $filtros = $this->aplicarEscopo($user, $filtros);
        $dados = $this->queries->index($filtros);
        $indicadores = $dados['indicadores'];
        $totalVisitas = (int) $indicadores->total_visitas;

        $dados['indicadores'] = [
            'total_visitas' => $totalVisitas,
            'hospitais_visitados' => (int) $indicadores->hospitais_visitados,
            'total_participacoes' => (int) $indicadores->total_participacoes,
            'media_participantes' => $totalVisitas > 0
                ? round((int) $indicadores->total_participacoes / $totalVisitas, 1)
                : 0,
            'impacto_estimado' => round((float) $indicadores->impacto_estimado),
            'visitas_com_impacto' => (int) $indicadores->visitas_com_impacto,
            'visitas_sem_impacto' => $totalVisitas - (int) $indicadores->visitas_com_impacto,
            'visitas_sem_relatorio' => $totalVisitas - (int) $indicadores->visitas_com_relatorio,
        ];

        $totaisPorMes = $dados['evolucao']->pluck('total', 'mes');
        $dados['evolucao'] = collect(CarbonPeriod::create(
            Carbon::createFromFormat('Y-m', $filtros['mes_inicio'])->startOfMonth(),
            '1 month',
            Carbon::createFromFormat('Y-m', $filtros['mes_fim'])->startOfMonth(),
        ))->map(fn (Carbon $mes) => [
            'mes' => $mes->format('Y-m'),
            'rotulo' => $mes->translatedFormat('M/Y'),
            'total' => (int) ($totaisPorMes[$mes->format('Y-m')] ?? 0),
        ])->values();

        $dados['hospitais'] = $this->incluirHospitaisComMeta($dados['hospitais'], $filtros);
        $metas = $this->metasMensais($dados['hospitais']->pluck('id'), $filtros);
        $realizadasMensais = $dados['realizadas_mensais']->groupBy('hospital_id');

        $dados['hospitais'] = $dados['hospitais']->map(function ($hospital) use ($metas, $realizadasMensais, $filtros) {
            $acompanhamento = $this->acompanhamentoMensal(
                (int) $hospital->id,
                $metas,
                $realizadasMensais,
                $filtros,
            );

            return [
                'id' => (int) $hospital->id,
                'nome' => $hospital->nome,
                'cidade' => $hospital->cidade,
                'total_visitas' => (int) $hospital->total_visitas,
                'total_participacoes' => (int) $hospital->total_participacoes,
                'media_participantes' => (int) $hospital->total_visitas > 0
                    ? round((int) $hospital->total_participacoes / (int) $hospital->total_visitas, 1)
                    : 0,
                'impacto_estimado' => round((float) $hospital->impacto_estimado),
                'possui_alas' => (bool) $hospital->possui_alas,
                ...$acompanhamento,
            ];
        })->values();

        if ($dados['detalhes']) {
            $dados['detalhes']['visitas']->through(fn ($visita) => [
                'id' => (int) $visita->id,
                'inicio_em' => $visita->inicio_em,
                'status' => $visita->status,
                'ala' => $visita->ala ?? 'Sem ala informada',
                'participantes' => (int) $visita->participantes,
                'impacto_estimado' => $visita->impacto_estimado !== null
                    ? round((float) $visita->impacto_estimado)
                    : null,
                'possui_relatorio' => (int) $visita->relatorios > 0,
            ]);
        }

        return [
            ...$dados,
            'filtros' => $filtros,
            'opcoes' => $this->opcoes($user, $filtros),
            'escopo_global' => $this->possuiEscopoGlobal($user),
        ];
    }

    public function show(User $user, Hospital $hospital, array $filtros): array
    {
        $filtros['hospital_id'] = $hospital->id;
        $filtros['ala_id'] = null;

        if (! $this->possuiEscopoGlobal($user)) {
            $cidadeId = (int) $user->voluntario?->cidade_base_id;

            if ($cidadeId === 0 || $cidadeId !== (int) $hospital->cidade_id) {
                abort(403);
            }
        }

        $resultado = $this->index($user, $filtros);
        $resumo = $resultado['hospitais']->firstWhere('id', $hospital->id);

        if (! $resumo) {
            abort(404);
        }

        $cidade = Cidade::query()->find($hospital->cidade_id, ['id', 'nome']);

        return [
            'hospital' => [
                'id' => (int) $hospital->id,
                'nome' => $hospital->nome,
                'cidade' => $cidade?->nome ?? 'Cidade não informada',
                'ativo' => (bool) $hospital->ativo,
            ],
            'resumo' => $resumo,
            'indicadores' => $resultado['indicadores'],
            'detalhes' => $resultado['detalhes'],
            'metas_semanais' => $this->acompanhamentoSemanal($hospital, $resultado['filtros']),
            'filtros' => collect($resultado['filtros'])->except(['hospital_id', 'ala_id', 'page'])->all(),
        ];
    }

    private function incluirHospitaisComMeta(Collection $hospitais, array $filtros): Collection
    {
        $inicio = Carbon::createFromFormat('Y-m', $filtros['mes_inicio']);
        $fim = Carbon::createFromFormat('Y-m', $filtros['mes_fim']);

        $hospitalIds = MetaMensalHospital::query()
            ->whereRaw('(ano * 100 + mes) between ? and ?', [
                (int) $inicio->format('Ym'),
                (int) $fim->format('Ym'),
            ])
            ->when($filtros['cidade_id'], fn ($query, int $cidadeId) => $query
                ->whereHas('hospital', fn ($hospital) => $hospital->where('cidade_id', $cidadeId)))
            ->pluck('hospital_id');

        $idsAusentes = $hospitalIds->diff($hospitais->pluck('id'));

        if ($idsAusentes->isEmpty()) {
            return $hospitais;
        }

        $ausentes = Hospital::query()
            ->leftJoin('cidades as c', 'c.id', '=', 'hospitais.cidade_id')
            ->whereIn('hospitais.id', $idsAusentes)
            ->get([
                'hospitais.id',
                'hospitais.nome',
                'c.nome as cidade',
            ])
            ->map(function ($hospital) {
                $hospital->total_visitas = 0;
                $hospital->total_participacoes = 0;
                $hospital->impacto_estimado = 0;
                $hospital->possui_alas = DB::table('alas_hospitais')->where('hospital_id', $hospital->id)->exists();

                return $hospital;
            });

        return $hospitais->concat($ausentes)
            ->sortByDesc('total_visitas')
            ->values();
    }

    private function metasMensais(Collection $hospitalIds, array $filtros): Collection
    {
        if ($hospitalIds->isEmpty()) {
            return collect();
        }

        $inicio = Carbon::createFromFormat('Y-m', $filtros['mes_inicio']);
        $fim = Carbon::createFromFormat('Y-m', $filtros['mes_fim']);

        return MetaMensalHospital::query()
            ->whereIn('hospital_id', $hospitalIds)
            ->whereRaw('(ano * 100 + mes) between ? and ?', [
                (int) $inicio->format('Ym'),
                (int) $fim->format('Ym'),
            ])
            ->get()
            ->keyBy(fn ($meta) => $meta->hospital_id.'-'.sprintf('%04d-%02d', $meta->ano, $meta->mes));
    }

    private function acompanhamentoMensal(
        int $hospitalId,
        Collection $metas,
        Collection $realizadasMensais,
        array $filtros,
    ): array {
        $realizadas = $realizadasMensais->get($hospitalId, collect())->keyBy('mes');
        $meses = collect(CarbonPeriod::create(
            Carbon::createFromFormat('Y-m', $filtros['mes_inicio'])->startOfMonth(),
            '1 month',
            Carbon::createFromFormat('Y-m', $filtros['mes_fim'])->startOfMonth(),
        ))->map(function (Carbon $mes) use ($hospitalId, $metas, $realizadas) {
            $chave = $mes->format('Y-m');
            $meta = $metas->get($hospitalId.'-'.$chave);
            $quantidadeMeta = $meta ? (int) $meta->quantidade : null;
            $quantidadeRealizada = (int) ($realizadas->get($chave)?->total ?? 0);

            return [
                'mes' => $chave,
                'meta' => $quantidadeMeta,
                'realizadas' => $quantidadeRealizada,
                'diferenca' => $quantidadeMeta !== null ? $quantidadeRealizada - $quantidadeMeta : null,
                'percentual' => $quantidadeMeta !== null
                    ? ($quantidadeMeta === 0 ? 100 : round(($quantidadeRealizada / $quantidadeMeta) * 100))
                    : null,
                'situacao' => $this->situacaoDoMes($mes, $quantidadeMeta, $quantidadeRealizada),
            ];
        })->values();

        $mesesComMeta = $meses->filter(fn ($mes) => $mes['meta'] !== null && $mes['situacao'] !== 'futuro');
        $metaTotal = (int) $mesesComMeta->sum('meta');
        $realizadasComMeta = (int) $mesesComMeta->sum('realizadas');

        return [
            'situacao_meta' => $this->situacaoConsolidada($meses),
            'meta_total' => $metaTotal,
            'realizadas_com_meta' => $realizadasComMeta,
            'percentual_meta' => $mesesComMeta->isEmpty()
                ? null
                : ($metaTotal === 0 ? 100 : round(($realizadasComMeta / $metaTotal) * 100)),
            'meses' => $meses->all(),
        ];
    }

    private function situacaoDoMes(Carbon $mes, ?int $meta, int $realizadas): string
    {
        if ($meta === null) {
            return 'sem_meta_definida';
        }

        if ($mes->isAfter(now()->startOfMonth())) {
            return 'futuro';
        }

        if ($realizadas >= $meta) {
            return 'dentro_meta';
        }

        return $mes->isSameMonth(now()) ? 'em_andamento' : 'atencao';
    }

    private function situacaoConsolidada(Collection $meses): string
    {
        if ($meses->contains('situacao', 'atencao')) {
            return 'atencao';
        }

        if ($meses->contains('situacao', 'em_andamento')) {
            return 'em_andamento';
        }

        if ($meses->contains('situacao', 'dentro_meta')) {
            return 'dentro_meta';
        }

        return 'sem_meta_definida';
    }

    private function acompanhamentoSemanal(Hospital $hospital, array $filtros): array
    {
        $inicio = Carbon::createFromFormat('Y-m', $filtros['mes_inicio'])->startOfMonth();
        $fim = Carbon::createFromFormat('Y-m', $filtros['mes_fim'])->endOfMonth();
        $metas = MetaSemanalHospital::query()
            ->where('hospital_id', $hospital->id)
            ->whereRaw('(ano * 100 + mes) between ? and ?', [
                (int) $inicio->format('Ym'),
                (int) $fim->format('Ym'),
            ])
            ->orderBy('ano')
            ->orderBy('mes')
            ->orderBy('semana')
            ->get();

        if ($metas->isEmpty()) {
            return [];
        }

        $realizadas = Visita::query()
            ->where('hospital_id', $hospital->id)
            ->whereBetween('inicio_em', [$inicio, $fim])
            ->whereIn('status', VisitaHelper::statusRealizadasValores())
            ->select(['ala_unidade_id'])
            ->selectRaw('substr(inicio_em, 1, 10) as data, COUNT(*) as total')
            ->groupByRaw('ala_unidade_id, substr(inicio_em, 1, 10)')
            ->get();
        $alas = $hospital->alas->pluck('nome', 'id');

        return $metas->map(function ($meta) use ($realizadas, $alas) {
            $faixa = collect(MetaHospitalHelper::semanasDoMes($meta->ano, $meta->mes))
                ->firstWhere('semana', $meta->semana);
            $total = $realizadas->filter(function ($item) use ($meta, $faixa) {
                $data = Carbon::parse($item->data);
                $mesmaAla = $meta->ala_unidade_id === null
                    || (int) $item->ala_unidade_id === (int) $meta->ala_unidade_id;

                return $mesmaAla
                    && (int) $data->year === (int) $meta->ano
                    && (int) $data->month === (int) $meta->mes
                    && (int) $data->day >= (int) $faixa['dia_inicio']
                    && (int) $data->day <= (int) $faixa['dia_fim'];
            })->sum('total');

            return [
                'mes' => sprintf('%04d-%02d', $meta->ano, $meta->mes),
                'semana' => (int) $meta->semana,
                'periodo' => $faixa['dia_inicio'].'–'.$faixa['dia_fim'],
                'ala' => $meta->ala_unidade_id ? ($alas[$meta->ala_unidade_id] ?? 'Ala removida') : null,
                'meta' => (int) $meta->quantidade,
                'realizadas' => (int) $total,
                'situacao' => $this->situacaoDoMes(
                    Carbon::create($meta->ano, $meta->mes, 1),
                    (int) $meta->quantidade,
                    (int) $total,
                ),
            ];
        })->all();
    }

    private function aplicarEscopo(User $user, array $filtros): array
    {
        resolverUsuario($user);

        $filtros = [
            'mes_inicio' => $filtros['mes_inicio'],
            'mes_fim' => $filtros['mes_fim'],
            'cidade_id' => isset($filtros['cidade_id']) ? (int) $filtros['cidade_id'] : null,
            'visao_global' => (bool) ($filtros['visao_global'] ?? false),
            'hospital_id' => isset($filtros['hospital_id']) ? (int) $filtros['hospital_id'] : null,
            'ala_id' => isset($filtros['ala_id']) ? (int) $filtros['ala_id'] : null,
        ];

        if ($this->possuiEscopoGlobal($user)) {
            if (! $filtros['visao_global'] && ! $filtros['cidade_id'] && $user->voluntario?->cidade_base_id) {
                $filtros['cidade_id'] = (int) $user->voluntario->cidade_base_id;
            }

            return $filtros;
        }

        $cidadeId = (int) $user->voluntario->cidade_base_id;

        if ($filtros['cidade_id'] && $filtros['cidade_id'] !== $cidadeId) {
            abort(403);
        }

        $filtros['cidade_id'] = $cidadeId;
        $filtros['visao_global'] = false;

        if ($filtros['hospital_id'] && ! Hospital::query()->whereKey($filtros['hospital_id'])->where('cidade_id', $cidadeId)->exists()) {
            abort(403);
        }

        return $filtros;
    }

    private function opcoes(User $user, array $filtros): array
    {
        $cidades = $this->possuiEscopoGlobal($user)
            ? Cidade::query()->orderBy('nome')->get(['id', 'nome'])
            : Cidade::query()->whereKey($filtros['cidade_id'])->get(['id', 'nome']);

        $hospitais = $filtros['cidade_id']
            ? Hospital::query()->where('cidade_id', $filtros['cidade_id'])->orderBy('nome')->get(['id', 'nome'])
            : collect();

        $alas = $filtros['hospital_id']
            ? Ala::query()->where('hospital_id', $filtros['hospital_id'])->orderBy('nome')->get(['id', 'nome'])
            : collect();

        return compact('cidades', 'hospitais', 'alas');
    }

    private function possuiEscopoGlobal(User $user): bool
    {
        resolverUsuario($user);

        return $user->cargos->contains(fn ($cargo) => in_array($cargo->slug, ['administrador', 'coordenador_geral'], true));
    }
}
