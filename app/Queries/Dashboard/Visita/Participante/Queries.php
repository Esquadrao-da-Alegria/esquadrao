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
            ->join('hospitais as h', 'h.id', '=', 'v.hospital_id')
            ->whereIn('vp.voluntario_id', $ids)
            ->where('vp.status_participacao', StatusParticipacao::Confirmado->value)
            ->where('v.status', '!=', VisitaStatus::Cancelada->value)
            ->whereBetween('v.inicio_em', [$inicioCalculo, $fim])
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('visitas_relatorios as vr')
                    ->whereColumn('vr.visita_id', 'v.id')
                    ->whereColumn('vr.autor_id', 'vp.voluntario_id')
                    ->where('vr.fora_do_prazo', false);
            })
            ->select(['vp.voluntario_id', 'v.id as visita_id', 'v.inicio_em', 'v.fim_em', 'v.status', 'h.nome as hospital', 'h.cidade_id'])
            ->distinct()
            ->get();

        $participacoes = DB::table('visita_participante as vp')
            ->join('visitas as v', 'v.id', '=', 'vp.visita_id')
            ->join('hospitais as h', 'h.id', '=', 'v.hospital_id')
            ->whereIn('vp.voluntario_id', $ids)
            ->where('vp.status_participacao', StatusParticipacao::Confirmado->value)
            ->where('v.status', '!=', VisitaStatus::Cancelada->value)
            ->whereBetween('v.inicio_em', [$inicio, $fim])
            ->select(['vp.voluntario_id', 'v.id as visita_id', 'v.inicio_em', 'h.nome as hospital', 'h.cidade_id'])
            ->selectRaw("EXISTS(SELECT 1 FROM visitas_relatorios vr WHERE vr.visita_id = v.id AND vr.autor_id = vp.voluntario_id) as possui_relatorio")
            ->selectRaw("EXISTS(SELECT 1 FROM visitas_relatorios vr WHERE vr.visita_id = v.id AND vr.autor_id = vp.voluntario_id AND vr.fora_do_prazo = 0) as possui_relatorio_no_prazo")
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
