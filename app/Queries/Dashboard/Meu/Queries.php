<?php

namespace App\Queries\Dashboard\Meu;

use App\Enums\StatusParticipacao;
use App\Enums\VisitaStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class Queries
{
    public function index(int $userId, array $filtros): array
    {
        $visitas = $this->visitas($userId, $filtros)->get();
        $visitasCalculo = $this->visitas($userId, [
            ...$filtros,
            'inicio' => $filtros['inicio']->copy()->subMonth()->startOfMonth(),
            'cidade_id' => null,
        ])->get();
        $eventos = $this->eventos($userId, $filtros)->get();
        $eventosOferecidos = DB::table('eventos')
            ->whereIn('tipo', ['reuniao', 'oficina'])
            ->where('status', 'finalizado')
            ->when(
                $filtros['cidade_base_id'],
                fn (Builder $query, int $cidadeId) => $query->where('cidade_id', $cidadeId),
                fn (Builder $query) => $query->whereRaw('1 = 0')
            )
            ->whereBetween('data_inicio', [$filtros['inicio'], $filtros['fim']])
            ->select(['id', 'tipo', 'titulo', 'local', 'cidade_id', 'data_inicio'])
            ->selectRaw("EXISTS(SELECT 1 FROM evento_participantes ep WHERE ep.evento_id = eventos.id AND ep.status = 'inscrito' AND ep.presenca IS NULL) as presencas_incompletas")
            ->get();

        $proximasVisitas = $this->visitas($userId, [
            ...$filtros,
            'inicio' => now(),
            'fim' => now()->copy()->addYear(),
            'cidade_id' => null,
        ])->where('vp.status_participacao', StatusParticipacao::Confirmado->value)
            ->where('v.status', '!=', VisitaStatus::Cancelada->value)
            ->orderBy('v.inicio_em')
            ->limit(5)
            ->get();

        $proximosEventos = DB::table('evento_participantes as ep')
            ->join('eventos as e', 'e.id', '=', 'ep.evento_id')
            ->leftJoin('cidades as c', 'c.id', '=', 'e.cidade_id')
            ->where('ep.user_id', $userId)
            ->where('ep.status', 'inscrito')
            ->where('e.status', 'agendado')
            ->where('e.data_inicio', '>=', now())
            ->whereIn('e.tipo', ['reuniao', 'oficina'])
            ->select(['e.id', 'e.tipo', 'e.titulo', 'e.local', 'e.data_inicio', 'c.nome as cidade'])
            ->orderBy('e.data_inicio')
            ->limit(5)
            ->get();

        $cidades = DB::query()->fromSub(
            DB::table('visita_participante as vp')
                ->join('visitas as v', 'v.id', '=', 'vp.visita_id')
                ->join('hospitais as h', 'h.id', '=', 'v.hospital_id')
                ->join('cidades as c', 'c.id', '=', 'h.cidade_id')
                ->where('vp.voluntario_id', $userId)
                ->select(['c.id', 'c.nome'])
                ->union(
                    DB::table('evento_participantes as ep')
                        ->join('eventos as e', 'e.id', '=', 'ep.evento_id')
                        ->join('cidades as c', 'c.id', '=', 'e.cidade_id')
                        ->where('ep.user_id', $userId)
                        ->select(['c.id', 'c.nome'])
                ),
            'cidades_atuacao'
        )->distinct()->orderBy('nome')->get();

        return compact('visitas', 'visitasCalculo', 'eventos', 'eventosOferecidos', 'proximasVisitas', 'proximosEventos', 'cidades');
    }

    public function companheiros(array $visitaIds, int $userId): object
    {
        return DB::table('visita_participante as vp')
            ->join('users as u', 'u.id', '=', 'vp.voluntario_id')
            ->whereIn('vp.visita_id', $visitaIds)
            ->where('vp.voluntario_id', '!=', $userId)
            ->where('vp.status_participacao', StatusParticipacao::Confirmado->value)
            ->select(['u.id', 'u.name'])
            ->selectRaw('COUNT(DISTINCT vp.visita_id) as visitas_compartilhadas')
            ->groupBy(['u.id', 'u.name'])
            ->orderByDesc('visitas_compartilhadas')
            ->orderBy('u.name')
            ->limit(5)
            ->get();
    }

    private function visitas(int $userId, array $filtros): Builder
    {
        $impactos = DB::table('visitas_relatorios')
            ->selectRaw('visita_id, AVG(pessoas_impactadas) as media')
            ->whereNotNull('pessoas_impactadas')
            ->groupBy('visita_id');

        return DB::table('visita_participante as vp')
            ->join('visitas as v', 'v.id', '=', 'vp.visita_id')
            ->leftJoin('hospitais as h', 'h.id', '=', 'v.hospital_id')
            ->leftJoin('cidades as c', 'c.id', '=', 'h.cidade_id')
            ->leftJoin('alas_hospitais as a', 'a.id', '=', 'v.ala_unidade_id')
            ->leftJoinSub($impactos, 'impactos', 'impactos.visita_id', '=', 'v.id')
            ->where('vp.voluntario_id', $userId)
            ->whereBetween('v.inicio_em', [$filtros['inicio'], $filtros['fim']])
            ->when($filtros['cidade_id'], fn (Builder $query, int $cidadeId) => $query->where('h.cidade_id', $cidadeId))
            ->select(['v.id', 'v.inicio_em', 'v.fim_em', 'v.status', 'c.id as cidade_id', 'c.nome as cidade', 'a.nome as ala', 'vp.tipo_participacao', 'vp.status_participacao'])
            ->selectRaw("COALESCE(h.nome, 'Sem hospital') as local")
            ->selectRaw('impactos.media as impacto_estimado')
            ->selectRaw("CASE WHEN vp.tipo_participacao = 'palhaco' THEN EXISTS(SELECT 1 FROM visitas_relatorios vr JOIN visita_participante autor_vp ON autor_vp.visita_id = vr.visita_id AND autor_vp.voluntario_id = vr.autor_id WHERE vr.visita_id = v.id AND autor_vp.tipo_participacao = 'palhaco' AND autor_vp.status_participacao = 'confirmado') ELSE EXISTS(SELECT 1 FROM visitas_relatorios vr WHERE vr.visita_id = v.id AND vr.autor_id = vp.voluntario_id) END as possui_relatorio")
            ->selectRaw("CASE WHEN vp.tipo_participacao = 'palhaco' THEN EXISTS(SELECT 1 FROM visitas_relatorios vr JOIN visita_participante autor_vp ON autor_vp.visita_id = vr.visita_id AND autor_vp.voluntario_id = vr.autor_id WHERE vr.visita_id = v.id AND autor_vp.tipo_participacao = 'palhaco' AND autor_vp.status_participacao = 'confirmado' AND vr.fora_do_prazo = 0) ELSE EXISTS(SELECT 1 FROM visitas_relatorios vr WHERE vr.visita_id = v.id AND vr.autor_id = vp.voluntario_id AND vr.fora_do_prazo = 0) END as possui_relatorio_no_prazo")
            ->selectRaw("EXISTS(SELECT 1 FROM visitas_relatorios vr WHERE vr.visita_id = v.id AND vr.autor_id = vp.voluntario_id AND vr.fora_do_prazo = 0) as relatorio_proprio_no_prazo")
            ->selectRaw("EXISTS(SELECT 1 FROM visitas_ajustes_contabilizacao vac WHERE vac.visita_id = v.id AND (vac.voluntario_id = vp.voluntario_id OR vac.relatorio_id IS NOT NULL)) as possui_relatorio_por_ajuste")
            ->distinct();
    }

    private function eventos(int $userId, array $filtros): Builder
    {
        return DB::table('evento_participantes as ep')
            ->join('eventos as e', 'e.id', '=', 'ep.evento_id')
            ->leftJoin('cidades as c', 'c.id', '=', 'e.cidade_id')
            ->where('ep.user_id', $userId)
            ->whereIn('e.tipo', ['reuniao', 'oficina'])
            ->whereBetween('e.data_inicio', [$filtros['inicio'], $filtros['fim']])
            ->when($filtros['cidade_id'], fn (Builder $query, int $cidadeId) => $query->where('e.cidade_id', $cidadeId))
            ->select(['e.id', 'e.tipo', 'e.titulo', 'e.local', 'e.status', 'e.data_inicio', 'c.id as cidade_id', 'c.nome as cidade', 'ep.status as inscricao_status', 'ep.presenca']);
    }
}
