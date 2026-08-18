<?php

namespace App\Queries\Dashboard\Visita\Hospital;

use App\Enums\StatusParticipacao;
use App\Enums\VisitaStatus;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class Queries
{
    public function index(array $filtros): array
    {
        $base = $this->base($filtros);
        $participantes = $this->participantes();
        $impactos = $this->impactos();

        $indicadores = (clone $base)
            ->leftJoinSub($participantes, 'participacoes', 'participacoes.visita_id', '=', 'v.id')
            ->leftJoinSub($impactos, 'impactos', 'impactos.visita_id', '=', 'v.id')
            ->selectRaw('COUNT(DISTINCT v.id) as total_visitas')
            ->selectRaw('COUNT(DISTINCT v.hospital_id) as hospitais_visitados')
            ->selectRaw('COALESCE(SUM(participacoes.quantidade), 0) as total_participacoes')
            ->selectRaw('COALESCE(SUM(impactos.media), 0) as impacto_estimado')
            ->selectRaw('COUNT(impactos.visita_id) as visitas_com_impacto')
            ->first();

        $evolucao = (clone $base)
            ->selectRaw("substr(v.inicio_em, 1, 7) as mes, COUNT(DISTINCT v.id) as total")
            ->groupByRaw("substr(v.inicio_em, 1, 7)")
            ->orderBy('mes')
            ->get();

        $hospitais = (clone $base)
            ->leftJoinSub($participantes, 'participacoes', 'participacoes.visita_id', '=', 'v.id')
            ->leftJoinSub($impactos, 'impactos', 'impactos.visita_id', '=', 'v.id')
            ->leftJoin('cidades as c', 'c.id', '=', 'h.cidade_id')
            ->select(['h.id', 'h.nome', 'c.nome as cidade'])
            ->selectRaw('COUNT(DISTINCT v.id) as total_visitas')
            ->selectRaw('COALESCE(SUM(participacoes.quantidade), 0) as total_participacoes')
            ->selectRaw('COALESCE(SUM(impactos.media), 0) as impacto_estimado')
            ->selectRaw('EXISTS(SELECT 1 FROM alas_hospitais ah WHERE ah.hospital_id = h.id) as possui_alas')
            ->groupBy(['h.id', 'h.nome', 'c.nome'])
            ->orderByDesc('total_visitas')
            ->orderBy('h.nome')
            ->get();

        return [
            'indicadores' => $indicadores,
            'evolucao' => $evolucao,
            'hospitais' => $hospitais,
            'detalhes' => $filtros['hospital_id']
                ? $this->detalhes($base, $participantes, $impactos, $filtros['hospital_id'])
                : null,
        ];
    }

    private function detalhes(Builder $base, Builder $participantes, Builder $impactos, int $hospitalId): array
    {
        $possuiAlas = DB::table('alas_hospitais')->where('hospital_id', $hospitalId)->exists();
        $alas = collect();

        if ($possuiAlas) {
            $alas = (clone $base)
                ->leftJoin('alas_hospitais as a', 'a.id', '=', 'v.ala_unidade_id')
                ->select(['a.id', 'a.nome'])
                ->selectRaw('COUNT(DISTINCT v.id) as total_visitas')
                ->groupBy(['a.id', 'a.nome'])
                ->orderByRaw('a.id IS NULL')
                ->orderBy('a.nome')
                ->get()
                ->map(fn ($ala) => [
                    'id' => $ala->id,
                    'nome' => $ala->nome ?? 'Sem ala informada',
                    'total_visitas' => (int) $ala->total_visitas,
                ]);
        }

        $visitas = (clone $base)
            ->leftJoin('alas_hospitais as a', 'a.id', '=', 'v.ala_unidade_id')
            ->leftJoinSub($participantes, 'participacoes', 'participacoes.visita_id', '=', 'v.id')
            ->leftJoinSub($impactos, 'impactos', 'impactos.visita_id', '=', 'v.id')
            ->select(['v.id', 'v.inicio_em', 'v.status', 'a.nome as ala'])
            ->selectRaw('COALESCE(participacoes.quantidade, 0) as participantes')
            ->selectRaw('impactos.media as impacto_estimado')
            ->orderByDesc('v.inicio_em')
            ->paginate(15)
            ->withQueryString();

        return ['possui_alas' => $possuiAlas, 'alas' => $alas, 'visitas' => $visitas];
    }

    private function base(array $filtros): Builder
    {
        $inicio = Carbon::createFromFormat('Y-m', $filtros['mes_inicio'])->startOfMonth();
        $fim = Carbon::createFromFormat('Y-m', $filtros['mes_fim'])->endOfMonth();

        return DB::table('visitas as v')
            ->join('hospitais as h', 'h.id', '=', 'v.hospital_id')
            ->whereBetween('v.inicio_em', [$inicio, $fim])
            ->whereIn('v.status', [VisitaStatus::Realizada->value, VisitaStatus::Contabilizada->value])
            ->when($filtros['cidade_id'], fn (Builder $query, int $cidadeId) => $query->where('h.cidade_id', $cidadeId))
            ->when($filtros['hospital_id'], fn (Builder $query, int $hospitalId) => $query->where('v.hospital_id', $hospitalId))
            ->when($filtros['ala_id'], fn (Builder $query, int $alaId) => $query->where('v.ala_unidade_id', $alaId));
    }

    private function participantes(): Builder
    {
        return DB::table('visita_participante')
            ->selectRaw('visita_id, COUNT(*) as quantidade')
            ->where('status_participacao', StatusParticipacao::Confirmado->value)
            ->groupBy('visita_id');
    }

    private function impactos(): Builder
    {
        return DB::table('visitas_relatorios')
            ->selectRaw('visita_id, AVG(pessoas_impactadas) as media')
            ->whereNotNull('pessoas_impactadas')
            ->groupBy('visita_id');
    }
}
