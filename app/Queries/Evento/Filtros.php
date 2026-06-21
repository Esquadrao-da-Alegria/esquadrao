<?php

namespace App\Queries\Evento;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Filtros
{
    public function aplicar(Builder $query, array $filtros): Builder
    {
        foreach ($filtros as $campo => $valor) {

            if (empty($valor) && $valor !== 0 && $valor !== '0') continue;

            switch ($campo) {

                case 'id':
                    $query->where('id', $valor);
                    break;

                case 'titulo':
                    $query->where('titulo', 'like', "%{$valor}%");
                    break;

                case 'titulo_exato':
                    $query->where('titulo', $valor);
                    break;

                case 'tipo':
                    $query->where('tipo', strtoupper((string) $valor));
                    break;

                case 'cidade_id':
                    $query->where('cidade_id', $valor);
                    break;

                case 'status':
                    $query->where('status', strtoupper((string) $valor));
                    break;

                case 'criado_por_id':
                    $query->where('criado_por_id', $valor);
                    break;

                case 'data':
                    $query->whereDate('data_inicio', $valor);
                    break;

                case 'data_inicio_de':
                    $query->where('data_inicio', '>=', $valor);
                    break;

                case 'data_inicio_ate':
                    $query->where('data_inicio', '<=', $valor);
                    break;

                case 'ordenar_por':
                    $campo_ordem = in_array($valor, ['data_inicio', 'data_fim']) ? $valor : 'data_inicio';
                    $query->orderBy($campo_ordem, 'asc');
                    break;

                case 'user_id':
                    $query->select('eventos.*')
                        ->selectRaw('EXISTS (
                            SELECT 1 FROM evento_participantes
                            WHERE evento_participantes.evento_id = eventos.id
                              AND evento_participantes.user_id = ?
                              AND evento_participantes.status = ?
                        ) as inscrito', [$valor, 'INSCRITO'])
                        ->selectRaw('(
                            eventos.criado_por_id = ? OR EXISTS (
                                SELECT 1 FROM evento_responsaveis
                                WHERE evento_responsaveis.evento_id = eventos.id
                                  AND evento_responsaveis.voluntario_id = ?
                            )
                        ) as pode_editar', [$valor, $valor])
                        ->withCount([
                            'participantes as total_inscritos' => fn (Builder $q) => $q->where('status', 'INSCRITO'),
                        ]);
                    break;

                case 'hora_inicio':
                    $query->whereRaw('TIME(data_inicio) >= ?', [$valor])
                          ->orderBy('data_inicio', 'asc');
                    break;

                case 'hora_fim':
                    $query->whereRaw('TIME(data_fim) >= ?', [$valor])
                          ->orderBy('data_fim', 'asc');
                    break;

                case 'minha_relacao':
                    $userId = $filtros['user_id'] ?? null;
                    if (!$userId) break;

                    if ($valor === 'inscrito') {
                        $query->whereExists(fn (QueryBuilder $q) =>
                            $q->selectRaw('1')
                              ->from('evento_participantes')
                              ->whereColumn('evento_participantes.evento_id', 'eventos.id')
                              ->where('evento_participantes.user_id', $userId)
                              ->where('evento_participantes.status', 'INSCRITO')
                        );
                    } elseif ($valor === 'responsavel') {
                        $query->where(fn (Builder $q) =>
                            $q->where('criado_por_id', $userId)
                              ->orWhereExists(fn (QueryBuilder $r) =>
                                  $r->selectRaw('1')
                                    ->from('evento_responsaveis')
                                    ->whereColumn('evento_responsaveis.evento_id', 'eventos.id')
                                    ->where('evento_responsaveis.voluntario_id', $userId)
                              )
                        );
                    }
                    break;

                case 'retornar_lista':
                    break;
            }
        }

        if (!isset($filtros['ordenar_por'])) {
            $query->orderBy('data_inicio', 'asc')
                  ->orderBy('titulo', 'asc');
        }

        return $query;
    }
}
