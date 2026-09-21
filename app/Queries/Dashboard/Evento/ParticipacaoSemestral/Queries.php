<?php

namespace App\Queries\Dashboard\Evento\ParticipacaoSemestral;

use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class Queries
{
    public function index(array $filtros): LengthAwarePaginator
    {
        $presencas = DB::table('evento_participantes as ep')
            ->join('eventos as e', 'e.id', '=', 'ep.evento_id')
            ->join('users as u', 'u.id', '=', 'ep.user_id')
            ->join('voluntarios as v', 'v.id', '=', 'u.voluntario_id')
            ->whereIn('e.tipo', ['reuniao', 'oficina'])
            ->where('e.status', 'finalizado')
            ->where('ep.presenca', 'presente')
            ->whereColumn('e.cidade_id', 'v.cidade_base_id')
            ->whereBetween('e.data_inicio', [$filtros['inicio'], $filtros['fim']])
            ->select('ep.user_id')
            ->selectRaw("SUM(CASE WHEN e.tipo = 'reuniao' THEN 1 ELSE 0 END) as reunioes")
            ->selectRaw("SUM(CASE WHEN e.tipo = 'oficina' THEN 1 ELSE 0 END) as oficinas")
            ->groupBy('ep.user_id');

        $integrantes = DB::table('users as u')
            ->join('voluntarios as v', 'v.id', '=', 'u.voluntario_id')
            ->leftJoin('cidades as c', 'c.id', '=', 'v.cidade_base_id')
            ->leftJoinSub($presencas, 'presencas', 'presencas.user_id', '=', 'u.id')
            ->whereNotNull('u.voluntario_id')
            ->when($filtros['cidade_id'], fn (Builder $query, int $cidadeId) => $query->where('v.cidade_base_id', $cidadeId))
            ->when($filtros['situacao'] !== 'todos', fn (Builder $query) => $query->where('v.status', $filtros['situacao'] === 'ativos' ? 'ativo' : 'inativo'))
            ->when($filtros['busca'], function (Builder $query, string $busca) {
                $query->where(function (Builder $where) use ($busca) {
                    $where->where('v.nome_completo', 'like', "%{$busca}%")
                        ->orWhere('u.name', 'like', "%{$busca}%")
                        ->orWhere('u.email', 'like', "%{$busca}%");
                });
            })
            ->select([
                'u.id',
                'v.nome_completo as nome',
                'c.nome as cidade',
                'v.status as situacao_cadastro',
            ])
            ->selectRaw('COALESCE(presencas.reunioes, 0) as reunioes')
            ->selectRaw('COALESCE(presencas.oficinas, 0) as oficinas')
            ->orderBy('v.nome_completo')
            ->paginate(15)
            ->withQueryString();

        $ids = collect($integrantes->items())->pluck('id');

        $eventos = DB::table('evento_participantes as ep')
            ->join('eventos as e', 'e.id', '=', 'ep.evento_id')
            ->join('users as u', 'u.id', '=', 'ep.user_id')
            ->join('voluntarios as v', 'v.id', '=', 'u.voluntario_id')
            ->whereIn('ep.user_id', $ids)
            ->whereIn('e.tipo', ['reuniao', 'oficina'])
            ->where('e.status', 'finalizado')
            ->where('ep.presenca', 'presente')
            ->whereColumn('e.cidade_id', 'v.cidade_base_id')
            ->whereBetween('e.data_inicio', [$filtros['inicio'], $filtros['fim']])
            ->orderByDesc('e.data_inicio')
            ->get(['ep.user_id', 'e.id', 'e.tipo', 'e.titulo', 'e.data_inicio'])
            ->groupBy('user_id');

        $integrantes->getCollection()->transform(function (object $integrante) use ($eventos) {
            $integrante->eventos = $eventos->get($integrante->id, collect())
                ->map(fn (object $evento) => [
                    'id' => (int) $evento->id,
                    'tipo' => $evento->tipo,
                    'titulo' => $evento->titulo,
                    'data_inicio' => $evento->data_inicio,
                ])
                ->values()
                ->all();

            return $integrante;
        });

        return $integrantes;
    }
}
