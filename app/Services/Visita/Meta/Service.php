<?php

namespace App\Services\Visita\Meta;

// ENUMS
use App\Enums\VisitaStatus;

// HELPERS
use App\Helpers\MetaHospital as MetaHospitalHelper;
use App\Helpers\Visita as VisitaHelper;

// MODELS
use App\Models\MetaMensalHospital;
use App\Models\MetaPeriodoHospital;
use App\Models\MetaPadraoHospital;
use App\Models\MetaSemanalHospital;
use App\Models\Visita;

// LIBS EXTERNAS
use Carbon\Carbon;
use Illuminate\Support\Collection;

// FACADES
use Illuminate\Support\Facades\DB;

class Service
{
    /**
     * @return array<int, array<string, int|string|null>>
     */
    public function index(int $cidadeId, string $mes): array
    {
        $referencia = Carbon::createFromFormat('!Y-m', $mes);
        $ano = (int) $referencia->year;
        $numeroMes = (int) $referencia->month;

        $metasMensais = MetaMensalHospital::query()
            ->with(['hospital:id,nome,cidade_id,ativo', 'hospital.alas:id,hospital_id,nome'])
            ->where('ano', $ano)
            ->where('mes', $numeroMes)
            ->whereHas('hospital', fn ($query) => $query
                ->where('cidade_id', $cidadeId)
                ->where('ativo', true))
            ->get();

        $metasPadrao = MetaPadraoHospital::query()
            ->with(['hospital:id,nome,cidade_id,ativo', 'hospital.alas:id,hospital_id,nome', 'periodos'])
            ->whereHas('hospital', fn ($query) => $query
                ->where('cidade_id', $cidadeId)
                ->where('ativo', true))
            ->whereNotIn('hospital_id', $metasMensais->pluck('hospital_id'))
            ->get()
            ->map(function (MetaPadraoHospital $metaPadrao) use ($ano, $numeroMes) {
                $metaMensal = new MetaMensalHospital([
                    'hospital_id' => $metaPadrao->hospital_id,
                    'ano' => $ano,
                    'mes' => $numeroMes,
                    'quantidade' => $metaPadrao->quantidade,
                    'periodicidade' => $metaPadrao->periodicidade,
                ]);
                $metaMensal->setRelation('hospital', $metaPadrao->hospital);
                $metaMensal->setRelation('periodos_padrao', $metaPadrao->periodos);

                return $metaMensal;
            });

        $metasMensais = $metasMensais->concat($metasPadrao);

        if ($metasMensais->isEmpty()) {
            return [];
        }

        $hospitalIds = $metasMensais->pluck('hospital_id')->all();
        $metasPeriodos = $this->buscarMetasPeriodos($hospitalIds, $ano, $numeroMes)
            ->concat($metasPadrao->flatMap(function (MetaMensalHospital $meta) {
                return $meta->periodos_padrao->map(function ($periodo) use ($meta) {
                    $periodo->hospital_id = $meta->hospital_id;

                    return $periodo;
                });
            }));
        $planejadas = $this->buscarPlanejadas($hospitalIds, $ano, $numeroMes);

        return $metasMensais
            ->flatMap(fn ($metaMensal) => $this->montarLinhas(
                $metaMensal,
                $metasPeriodos->where('hospital_id', $metaMensal->hospital_id),
                $planejadas,
                $referencia,
            ))
            ->filter(fn (array $linha) => $linha['faltam_periodo'] > 0 || $linha['faltam_mes'] > 0)
            ->sortBy(fn (array $linha) => "{$linha['hospital']}|{$linha['ala']}")
            ->values()
            ->all();
    }

    /**
     * @param  array<int, int|string>  $hospitalIds
     */
    private function buscarMetasPeriodos(array $hospitalIds, int $ano, int $mes): Collection
    {
        $metas = MetaPeriodoHospital::query()
            ->whereIn('hospital_id', $hospitalIds)
            ->where('ano', $ano)
            ->where('mes', $mes)
            ->get();

        if ($metas->isNotEmpty()) {
            return $metas;
        }

        return MetaSemanalHospital::query()
            ->whereIn('hospital_id', $hospitalIds)
            ->where('ano', $ano)
            ->where('mes', $mes)
            ->get()
            ->map(function (MetaSemanalHospital $meta) {
                $meta->periodo = $meta->semana;

                return $meta;
            });
    }

    /**
     * @param  array<int, int|string>  $hospitalIds
     */
    private function buscarPlanejadas(array $hospitalIds, int $ano, int $mes): Collection
    {
        $expressaoDia = DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%d', inicio_em) AS INTEGER)"
            : 'DAY(inicio_em)';
        $status = [
            VisitaStatus::Agendada->value,
            ...VisitaHelper::statusRealizadasValores(),
        ];

        return Visita::query()
            ->select([
                'hospital_id',
                'ala_unidade_id',
                DB::raw("{$expressaoDia} as dia"),
                DB::raw('COUNT(*) as total'),
            ])
            ->whereIn('hospital_id', $hospitalIds)
            ->whereYear('inicio_em', $ano)
            ->whereMonth('inicio_em', $mes)
            ->whereIn('status', $status)
            ->groupBy('hospital_id', 'ala_unidade_id')
            ->groupByRaw($expressaoDia)
            ->get();
    }

    private function periodoReferencia(
        Carbon $referencia,
        string $periodicidade,
        Collection $metasPeriodos,
        Collection $planejadas,
        int $hospitalId,
    ): int {
        $mesSelecionado = $referencia->copy()->startOfMonth();
        $mesAtual = now()->copy()->startOfMonth();
        $periodos = MetaHospitalHelper::periodosDoMes(
            (int) $referencia->year,
            (int) $referencia->month,
            $periodicidade,
        );

        if ($mesSelecionado->equalTo($mesAtual)) {
            foreach ($periodos as $periodo) {
                if (now()->day >= $periodo['dia_inicio'] && now()->day <= $periodo['dia_fim']) {
                    return $periodo['periodo'];
                }
            }
        }

        if ($mesSelecionado->lessThan($mesAtual)) {
            return (int) end($periodos)['periodo'];
        }

        foreach ($periodos as $periodo) {
            $possuiDeficit = $metasPeriodos
                ->where('periodo', $periodo['periodo'])
                ->contains(fn ($meta) => $this->totalPlanejado(
                    $planejadas,
                    $hospitalId,
                    $periodo,
                    $meta->ala_unidade_id !== null ? (int) $meta->ala_unidade_id : null,
                ) < (int) $meta->quantidade);

            if ($possuiDeficit) {
                return (int) $periodo['periodo'];
            }
        }

        return (int) $periodos[0]['periodo'];
    }

    private function montarLinhas(
        MetaMensalHospital $metaMensal,
        Collection $metasPeriodos,
        Collection $planejadas,
        Carbon $referencia,
    ): Collection {
        $hospital = $metaMensal->hospital;
        $periodicidade = $metaMensal->periodicidade ?? 'semanal';
        $periodos = MetaHospitalHelper::periodosDoMes(
            (int) $referencia->year,
            (int) $referencia->month,
            $periodicidade,
        );
        $periodoReferencia = $this->periodoReferencia(
            $referencia,
            $periodicidade,
            $metasPeriodos,
            $planejadas,
            (int) $metaMensal->hospital_id,
        );
        $periodo = collect($periodos)->firstWhere('periodo', $periodoReferencia);
        $metasDoPeriodo = $metasPeriodos->where('periodo', $periodoReferencia)->values();
        $planejadasMes = (int) $planejadas
            ->where('hospital_id', $metaMensal->hospital_id)
            ->sum('total');

        if ($metasDoPeriodo->isEmpty()) {
            $metasDoPeriodo = collect([(object) [
                'ala_unidade_id' => null,
                'quantidade'     => null,
            ]]);
        }

        return $metasDoPeriodo->map(function ($metaPeriodo) use (
            $hospital,
            $metaMensal,
            $planejadas,
            $planejadasMes,
            $periodicidade,
            $periodo,
            $periodoReferencia,
        ) {
            $alaId = $metaPeriodo->ala_unidade_id !== null
                ? (int) $metaPeriodo->ala_unidade_id
                : null;
            $metaDoPeriodo = $metaPeriodo->quantidade !== null
                ? (int) $metaPeriodo->quantidade
                : null;
            $planejadasDoPeriodo = $this->totalPlanejado(
                $planejadas,
                (int) $metaMensal->hospital_id,
                $periodo,
                $alaId,
            );

            return [
                'hospital_id'        => (int) $hospital->id,
                'hospital'           => $hospital->nome,
                'ala_id'             => $alaId,
                'ala'                => $alaId
                    ? ($hospital->alas->firstWhere('id', $alaId)?->nome ?? 'Ala não encontrada')
                    : 'Todas as alas',
                'periodicidade'      => $periodicidade,
                'periodo'            => $periodoReferencia,
                'titulo_periodo'     => $periodo['titulo'],
                'sigla_periodo'      => $periodo['sigla'],
                'meta_periodo'       => $metaDoPeriodo,
                'planejadas_periodo' => $metaDoPeriodo !== null ? $planejadasDoPeriodo : null,
                'faltam_periodo'     => $metaDoPeriodo !== null ? max(0, $metaDoPeriodo - $planejadasDoPeriodo) : 0,
                'meta_mensal'        => (int) $metaMensal->quantidade,
                'planejadas_mes'     => $planejadasMes,
                'faltam_mes'         => max(0, (int) $metaMensal->quantidade - $planejadasMes),
            ];
        });
    }

    private function totalPlanejado(
        Collection $planejadas,
        int $hospitalId,
        array $periodo,
        ?int $alaId,
    ): int {
        $registros = $planejadas
            ->where('hospital_id', $hospitalId)
            ->filter(fn ($registro) => (int) $registro->dia >= $periodo['dia_inicio']
                && (int) $registro->dia <= $periodo['dia_fim']);

        if ($alaId !== null) {
            $registros = $registros->where('ala_unidade_id', $alaId);
        }

        return (int) $registros->sum('total');
    }
}
