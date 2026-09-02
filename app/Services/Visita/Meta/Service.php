<?php

namespace App\Services\Visita\Meta;

// ENUMS
use App\Enums\VisitaStatus;

// HELPERS
use App\Helpers\MetaHospital as MetaHospitalHelper;
use App\Helpers\Visita as VisitaHelper;

// MODELS
use App\Models\MetaMensalHospital;
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

        if ($metasMensais->isEmpty()) {
            return [];
        }

        $hospitalIds = $metasMensais->pluck('hospital_id')->all();
        $metasSemanais = MetaSemanalHospital::query()
            ->whereIn('hospital_id', $hospitalIds)
            ->where('ano', $ano)
            ->where('mes', $numeroMes)
            ->get();
        $planejadas = $this->buscarPlanejadas($hospitalIds, $ano, $numeroMes);
        $semana = $this->semanaReferencia($referencia, $metasSemanais, $planejadas);

        return $metasMensais
            ->flatMap(fn ($metaMensal) => $this->montarLinhas(
                $metaMensal,
                $metasSemanais->where('hospital_id', $metaMensal->hospital_id),
                $planejadas,
                $semana,
            ))
            ->filter(fn (array $linha) => $linha['faltam_semana'] > 0 || $linha['faltam_mes'] > 0)
            ->sortBy(fn (array $linha) => "{$linha['hospital']}|{$linha['ala']}")
            ->values()
            ->all();
    }

    /**
     * @param  array<int, int|string>  $hospitalIds
     */
    private function buscarPlanejadas(array $hospitalIds, int $ano, int $mes): Collection
    {
        $expressaoDia = DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%d', inicio_em) AS INTEGER)"
            : 'DAY(inicio_em)';
        $sqlSemana = MetaHospitalHelper::sqlSemanaVisita($ano, $mes, $expressaoDia);
        $status = [
            VisitaStatus::Agendada->value,
            ...VisitaHelper::statusRealizadasValores(),
        ];

        return Visita::query()
            ->select([
                'hospital_id',
                'ala_unidade_id',
                DB::raw("{$sqlSemana} as semana"),
                DB::raw('COUNT(*) as total'),
            ])
            ->whereIn('hospital_id', $hospitalIds)
            ->whereYear('inicio_em', $ano)
            ->whereMonth('inicio_em', $mes)
            ->whereIn('status', $status)
            ->groupBy('hospital_id', 'ala_unidade_id')
            ->groupByRaw($sqlSemana)
            ->get();
    }

    private function semanaReferencia(
        Carbon $referencia,
        Collection $metasSemanais,
        Collection $planejadas,
    ): int {
        $mesSelecionado = $referencia->copy()->startOfMonth();
        $mesAtual = now()->copy()->startOfMonth();
        $semanas = MetaHospitalHelper::semanasDoMes(
            (int) $referencia->year,
            (int) $referencia->month,
        );

        if ($mesSelecionado->equalTo($mesAtual)) {
            foreach ($semanas as $semana) {
                if (now()->day >= $semana['dia_inicio'] && now()->day <= $semana['dia_fim']) {
                    return $semana['semana'];
                }
            }
        }

        if ($mesSelecionado->lessThan($mesAtual)) {
            return (int) end($semanas)['semana'];
        }

        foreach ($semanas as $semana) {
            $possuiDeficit = $metasSemanais
                ->where('semana', $semana['semana'])
                ->contains(fn ($meta) => $this->totalPlanejado(
                    $planejadas,
                    (int) $meta->hospital_id,
                    (int) $meta->semana,
                    $meta->ala_unidade_id !== null ? (int) $meta->ala_unidade_id : null,
                ) < (int) $meta->quantidade);

            if ($possuiDeficit) {
                return (int) $semana['semana'];
            }
        }

        return (int) $semanas[0]['semana'];
    }

    private function montarLinhas(
        MetaMensalHospital $metaMensal,
        Collection $metasSemanais,
        Collection $planejadas,
        int $semana,
    ): Collection {
        $hospital = $metaMensal->hospital;
        $metasDaSemana = $metasSemanais->where('semana', $semana)->values();
        $planejadasMes = (int) $planejadas
            ->where('hospital_id', $metaMensal->hospital_id)
            ->sum('total');

        if ($metasDaSemana->isEmpty()) {
            $metasDaSemana = collect([(object) [
                'ala_unidade_id' => null,
                'quantidade'     => null,
            ]]);
        }

        return $metasDaSemana->map(function ($metaSemanal) use (
            $hospital,
            $metaMensal,
            $planejadas,
            $planejadasMes,
            $semana,
        ) {
            $alaId = $metaSemanal->ala_unidade_id !== null
                ? (int) $metaSemanal->ala_unidade_id
                : null;
            $metaSemana = $metaSemanal->quantidade !== null
                ? (int) $metaSemanal->quantidade
                : null;
            $planejadasSemana = $this->totalPlanejado(
                $planejadas,
                (int) $metaMensal->hospital_id,
                $semana,
                $alaId,
            );

            return [
                'hospital_id'        => (int) $hospital->id,
                'hospital'           => $hospital->nome,
                'ala_id'             => $alaId,
                'ala'                => $alaId
                    ? ($hospital->alas->firstWhere('id', $alaId)?->nome ?? 'Ala não encontrada')
                    : 'Todas as alas',
                'semana'             => $semana,
                'meta_semanal'       => $metaSemana,
                'planejadas_semana'  => $metaSemana !== null ? $planejadasSemana : null,
                'faltam_semana'      => $metaSemana !== null ? max(0, $metaSemana - $planejadasSemana) : 0,
                'meta_mensal'        => (int) $metaMensal->quantidade,
                'planejadas_mes'     => $planejadasMes,
                'faltam_mes'         => max(0, (int) $metaMensal->quantidade - $planejadasMes),
            ];
        });
    }

    private function totalPlanejado(
        Collection $planejadas,
        int $hospitalId,
        int $semana,
        ?int $alaId,
    ): int {
        $registros = $planejadas
            ->where('hospital_id', $hospitalId)
            ->where('semana', $semana);

        if ($alaId !== null) {
            $registros = $registros->where('ala_unidade_id', $alaId);
        }

        return (int) $registros->sum('total');
    }
}
