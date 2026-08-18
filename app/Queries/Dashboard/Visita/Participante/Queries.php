<?php

namespace App\Queries\Dashboard\Visita\Participante;

use App\Enums\StatusParticipacao;
use App\Enums\VisitaStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\DB;

class Queries
{
    public function index(array $filtros): array
    {
        $inicio = Carbon::parse($filtros['inicio'])->startOfDay();
        $fim = Carbon::parse($filtros['fim'])->endOfDay();
        $inicioCalculo = $inicio->copy()->subMonth()->startOfMonth();

        $usuarios = User::query()
            ->with(['cargos:id,nome,slug', 'voluntario.cidadeBase:id,nome'])
            ->whereNotNull('voluntario_id')
            ->whereHas('voluntario', function (EloquentBuilder $query) use ($filtros) {
                $query->where('status', 'ativo')
                    ->when($filtros['cidade_id'], fn (EloquentBuilder $q, int $cidadeId) => $q->where('cidade_base_id', $cidadeId));
            })
            ->when($filtros['busca'] ?? null, function (EloquentBuilder $query, string $busca) {
                $query->where(function (EloquentBuilder $q) use ($busca) {
                    $q->where('name', 'like', "%{$busca}%")
                        ->orWhere('email', 'like', "%{$busca}%");
                });
            })
            ->when($filtros['participante_id'], fn (EloquentBuilder $query, int $id) => $query->whereKey($id))
            ->when($filtros['cargo_id'], fn (EloquentBuilder $query, int $id) => $query->whereHas('cargos', fn (EloquentBuilder $q) => $q->whereKey($id)))
            ->orderBy('name')
            ->get();

        $ids = $usuarios->pluck('id');

        $visitasValidas = DB::table('visita_participante as vp')
            ->join('visitas as v', 'v.id', '=', 'vp.visita_id')
            ->leftJoin('hospitais as h', 'h.id', '=', 'v.hospital_id')
            ->whereIn('vp.voluntario_id', $ids)
            ->where('vp.status_participacao', StatusParticipacao::Confirmado->value)
            ->where(function ($query) {
                $query->where('v.status', VisitaStatus::Contabilizada->value)
                    ->orWhere(function ($q) {
                        $q->where('v.status', VisitaStatus::Realizada->value)
                            ->where(function ($query) {
                                $query->whereExists(function ($ajuste) {
                                    $ajuste->selectRaw('1')
                                        ->from('visitas_ajustes_contabilizacao as vac')
                                        ->whereColumn('vac.visita_id', 'v.id')
                                        ->where(function ($qVol) {
                                            $qVol->whereColumn('vac.voluntario_id', 'vp.voluntario_id')
                                                ->orWhereNotNull('vac.relatorio_id');
                                        });
                                })->orWhere(function ($paisana) {
                                    $paisana->where('vp.tipo_participacao', 'paisana')
                                        ->whereExists(function ($relatorio) {
                                            $relatorio->selectRaw('1')
                                                ->from('visitas_relatorios as vr')
                                                ->whereColumn('vr.visita_id', 'v.id')
                                                ->whereColumn('vr.autor_id', 'vp.voluntario_id')
                                                ->where(function ($aceite) {
                                                    $aceite->where('vr.fora_do_prazo', false)
                                                        ->orWhereExists(fn ($ajuste) => $ajuste->selectRaw('1')->from('visitas_ajustes_contabilizacao as vac')->whereColumn('vac.relatorio_id', 'vr.id'));
                                                });
                                        });
                                })->orWhere(function ($palhaco) {
                                    $palhaco->where('vp.tipo_participacao', 'palhaco')
                                        ->whereExists(function ($relatorio) {
                                            $relatorio->selectRaw('1')
                                                ->from('visitas_relatorios as vr')
                                                ->join('visita_participante as autor_vp', function ($join) {
                                                    $join->on('autor_vp.visita_id', '=', 'vr.visita_id')
                                                        ->on('autor_vp.voluntario_id', '=', 'vr.autor_id');
                                                })
                                                ->whereColumn('vr.visita_id', 'v.id')
                                                ->where('autor_vp.tipo_participacao', 'palhaco')
                                                ->where('autor_vp.status_participacao', StatusParticipacao::Confirmado->value)
                                                ->where(function ($aceite) {
                                                    $aceite->where('vr.fora_do_prazo', false)
                                                        ->orWhereExists(fn ($ajuste) => $ajuste->selectRaw('1')->from('visitas_ajustes_contabilizacao as vac')->whereColumn('vac.relatorio_id', 'vr.id'));
                                                });
                                        });
                                });
                            });
                    });
            })
            ->whereBetween('v.inicio_em', [$inicioCalculo, $fim])
            ->select(['vp.voluntario_id', 'v.id as visita_id', 'v.inicio_em', 'v.fim_em', 'v.status', 'h.cidade_id'])
            ->selectRaw("COALESCE(h.nome, 'Sem hospital') as hospital")
            ->distinct()
            ->get();

        $participacoes = DB::table('visita_participante as vp')
            ->join('visitas as v', 'v.id', '=', 'vp.visita_id')
            ->leftJoin('hospitais as h', 'h.id', '=', 'v.hospital_id')
            ->whereIn('vp.voluntario_id', $ids)
            ->where('vp.status_participacao', StatusParticipacao::Confirmado->value)
            ->whereIn('v.status', [
                VisitaStatus::Realizada->value,
                VisitaStatus::Contabilizada->value,
                VisitaStatus::NaoContabilizada->value,
            ])
            ->whereBetween('v.inicio_em', [$inicio, $fim])
            ->select(['vp.voluntario_id', 'vp.tipo_participacao', 'v.id as visita_id', 'v.inicio_em', 'v.status', 'h.cidade_id'])
            ->selectRaw("COALESCE(h.nome, 'Sem hospital') as hospital")
            ->selectRaw("CASE WHEN vp.tipo_participacao = 'palhaco' THEN EXISTS(SELECT 1 FROM visitas_relatorios vr JOIN visita_participante autor_vp ON autor_vp.visita_id = vr.visita_id AND autor_vp.voluntario_id = vr.autor_id WHERE vr.visita_id = v.id AND autor_vp.tipo_participacao = 'palhaco' AND autor_vp.status_participacao = 'confirmado') ELSE EXISTS(SELECT 1 FROM visitas_relatorios vr WHERE vr.visita_id = v.id AND vr.autor_id = vp.voluntario_id) END as possui_relatorio")
            ->selectRaw("CASE WHEN vp.tipo_participacao = 'palhaco' THEN EXISTS(SELECT 1 FROM visitas_relatorios vr JOIN visita_participante autor_vp ON autor_vp.visita_id = vr.visita_id AND autor_vp.voluntario_id = vr.autor_id WHERE vr.visita_id = v.id AND autor_vp.tipo_participacao = 'palhaco' AND autor_vp.status_participacao = 'confirmado' AND vr.fora_do_prazo = 0) ELSE EXISTS(SELECT 1 FROM visitas_relatorios vr WHERE vr.visita_id = v.id AND vr.autor_id = vp.voluntario_id AND vr.fora_do_prazo = 0) END as possui_relatorio_no_prazo")
            ->selectRaw("EXISTS(SELECT 1 FROM visitas_relatorios vr WHERE vr.visita_id = v.id AND vr.autor_id = vp.voluntario_id AND vr.fora_do_prazo = 0) as relatorio_proprio_no_prazo")
            ->selectRaw("EXISTS(SELECT 1 FROM visitas_ajustes_contabilizacao vac WHERE vac.visita_id = v.id AND (vac.voluntario_id = vp.voluntario_id OR vac.relatorio_id IS NOT NULL)) as possui_relatorio_por_ajuste")
            ->distinct()
            ->get();

        $eventos = DB::table('eventos')
            ->whereIn('tipo', ['reuniao', 'oficina'])
            ->where('status', 'finalizado')
            ->whereBetween('data_inicio', [$inicio, $fim])
            ->whereNotNull('cidade_id')
            ->select(['id', 'tipo', 'titulo', 'cidade_id', 'data_inicio'])
            ->selectRaw("EXISTS(SELECT 1 FROM evento_participantes ep WHERE ep.evento_id = eventos.id AND ep.status = 'inscrito' AND ep.presenca IS NULL) as presencas_incompletas")
            ->get();

        $presencas = DB::table('evento_participantes as ep')
            ->join('eventos as e', 'e.id', '=', 'ep.evento_id')
            ->whereIn('ep.user_id', $ids)
            ->where('ep.presenca', 'presente')
            ->where('e.status', 'finalizado')
            ->whereBetween('e.data_inicio', [$inicio, $fim])
            ->get(['ep.user_id', 'e.id as evento_id', 'e.tipo', 'e.titulo', 'e.cidade_id', 'e.data_inicio']);

        return compact('usuarios', 'visitasValidas', 'participacoes', 'eventos', 'presencas');
    }

    public function show(array $filtros): array
    {
        return $this->index($filtros);
    }
}
