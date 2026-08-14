<?php

namespace App\Queries\Dashboard;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class Queries
{
    public function index(int $userId, CarbonInterface $inicioMes, CarbonInterface $inicioSemestre, CarbonInterface $fimSemestre): array
    {
        return [
            'proximasVisitas' => $this->proximasVisitas($userId),
            'proximosEventos' => $this->proximosEventos($userId),
            'relatoriosPendentes' => $this->relatoriosPendentes($userId),
            'visitasMes' => $this->visitasMes($userId, $inicioMes),
            'eventosSemestre' => $this->eventosSemestre($userId, $inicioSemestre, $fimSemestre),
        ];
    }

    private function proximasVisitas(int $userId): object
    {
        return DB::table('visita_participante as vp')
            ->join('visitas as v', 'v.id', '=', 'vp.visita_id')
            ->leftJoin('hospitais as h', 'h.id', '=', 'v.hospital_id')
            ->leftJoin('cidades as c', 'c.id', '=', 'h.cidade_id')
            ->where('vp.voluntario_id', $userId)
            ->where('vp.status_participacao', 'confirmado')
            ->where('v.status', 'agendada')
            ->where('v.inicio_em', '>=', now())
            ->select(['v.id', 'v.inicio_em', 'v.fim_em', 'v.status', 'v.tipo', 'h.nome as local', 'c.nome as cidade'])
            ->orderBy('v.inicio_em')
            ->limit(6)
            ->get();
    }

    private function proximosEventos(int $userId): object
    {
        return DB::table('evento_participantes as ep')
            ->join('eventos as e', 'e.id', '=', 'ep.evento_id')
            ->leftJoin('cidades as c', 'c.id', '=', 'e.cidade_id')
            ->where('ep.user_id', $userId)
            ->where('ep.status', 'inscrito')
            ->where('e.status', 'agendado')
            ->where('e.data_inicio', '>=', now())
            ->whereIn('e.tipo', ['reuniao', 'oficina'])
            ->select(['e.id', 'e.titulo', 'e.tipo', 'e.local', 'e.data_inicio as inicio_em', 'e.data_fim as fim_em', 'e.status', 'c.nome as cidade'])
            ->orderBy('e.data_inicio')
            ->limit(6)
            ->get();
    }

    private function relatoriosPendentes(int $userId): object
    {
        return DB::table('visita_participante as vp')
            ->join('visitas as v', 'v.id', '=', 'vp.visita_id')
            ->leftJoin('hospitais as h', 'h.id', '=', 'v.hospital_id')
            ->leftJoin('cidades as c', 'c.id', '=', 'h.cidade_id')
            ->where('vp.voluntario_id', $userId)
            ->where('vp.status_participacao', 'confirmado')
            ->whereIn('v.status', ['realizada', 'pendente_relatorio'])
            ->where('v.fim_em', '<=', now())
            ->whereRaw("NOT EXISTS(SELECT 1 FROM visitas_ajustes_contabilizacao vac WHERE vac.visita_id = v.id AND (vac.voluntario_id = vp.voluntario_id OR vac.relatorio_id IS NOT NULL))")
            ->whereRaw("CASE WHEN vp.tipo_participacao = 'palhaco' THEN NOT EXISTS(SELECT 1 FROM visitas_relatorios vr JOIN visita_participante autor_vp ON autor_vp.visita_id = vr.visita_id AND autor_vp.voluntario_id = vr.autor_id WHERE vr.visita_id = v.id AND autor_vp.tipo_participacao = 'palhaco' AND autor_vp.status_participacao = 'confirmado') ELSE NOT EXISTS(SELECT 1 FROM visitas_relatorios vr WHERE vr.visita_id = v.id AND vr.autor_id = vp.voluntario_id) END")
            ->select(['v.id', 'v.inicio_em', 'v.fim_em', 'h.nome as local', 'c.nome as cidade'])
            ->orderBy('v.fim_em')
            ->limit(6)
            ->get();
    }

    private function visitasMes(int $userId, CarbonInterface $inicioMes): object
    {
        return DB::table('visita_participante as vp')
            ->join('visitas as v', 'v.id', '=', 'vp.visita_id')
            ->where('vp.voluntario_id', $userId)
            ->where('vp.status_participacao', 'confirmado')
            ->where('v.status', 'realizada')
            ->whereBetween('v.inicio_em', [$inicioMes, $inicioMes->copy()->endOfMonth()])
            ->select(['v.id', 'vp.tipo_participacao'])
            ->selectRaw("CASE WHEN vp.tipo_participacao = 'palhaco' THEN EXISTS(SELECT 1 FROM visitas_relatorios vr JOIN visita_participante autor_vp ON autor_vp.visita_id = vr.visita_id AND autor_vp.voluntario_id = vr.autor_id WHERE vr.visita_id = v.id AND autor_vp.tipo_participacao = 'palhaco' AND autor_vp.status_participacao = 'confirmado' AND (vr.fora_do_prazo = 0 OR EXISTS(SELECT 1 FROM visitas_ajustes_contabilizacao vac WHERE vac.relatorio_id = vr.id AND vac.tipo = 'aceite_relatorio_fora_prazo'))) ELSE EXISTS(SELECT 1 FROM visitas_relatorios vr WHERE vr.visita_id = v.id AND vr.autor_id = vp.voluntario_id AND (vr.fora_do_prazo = 0 OR EXISTS(SELECT 1 FROM visitas_ajustes_contabilizacao vac WHERE vac.relatorio_id = vr.id AND vac.tipo = 'aceite_relatorio_fora_prazo'))) END as valida")
            ->get();
    }

    private function eventosSemestre(int $userId, CarbonInterface $inicio, CarbonInterface $fim): object
    {
        return DB::table('evento_participantes as ep')
            ->join('eventos as e', 'e.id', '=', 'ep.evento_id')
            ->where('ep.user_id', $userId)
            ->where('ep.status', 'inscrito')
            ->where('ep.presenca', 'presente')
            ->where('e.status', 'finalizado')
            ->whereIn('e.tipo', ['reuniao', 'oficina'])
            ->whereBetween('e.data_inicio', [$inicio, $fim])
            ->select(['e.id', 'e.tipo'])
            ->get();
    }
}
