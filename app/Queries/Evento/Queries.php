<?php

namespace App\Queries\Evento;

use App\Models\Evento;
use App\Models\EventoParticipante;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class Queries
{
    public function __construct(private Filtros $filtros) {}

    public function index(array $filtros): array
    {
        $retornarLista = $filtros['retornar_lista'] ?? true;

        try {
            $query = Evento::query()->with(['cidade:id,nome']);

            $this->filtros->aplicar($query, $filtros);

            $dados = $retornarLista ? $query->get() : $query->first();

            return [
                'sucesso' => true,
                'dados'   => $dados,
                'erros'   => [],
            ];
        } catch (\Throwable $th) {
            \Log::error('Queries::index falhou: ' . $th->getMessage(), [
                'filtros' => $filtros,
                'sql'     => method_exists($query, 'toSql') ? $query->toSql() : 'indisponível',
            ]);

            $dados = $retornarLista ? new Collection() : null;

            return [
                'sucesso' => false,
                'dados'   => $dados,
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }

    public function dashboard(array $filtros): array
    {
        try {
            $query = User::query()
                ->selectRaw('users.id, users.name, COUNT(ep.id) as total_presencas')
                ->leftJoin('evento_participantes as ep', function ($join) {
                    $join->on('ep.user_id', '=', 'users.id')
                         ->where('ep.status', '=', 'PRESENTE');
                })
                ->leftJoin('eventos as e', function ($join) {
                    $join->on('e.id', '=', 'ep.evento_id')
                         ->whereNull('e.deleted_at');
                });

            if (!empty($filtros['semestre'])) {
                [$ano, $sem] = explode('/', $filtros['semestre']);
                $op = $sem === '1' ? '<=' : '>';
                $query->whereRaw(
                    "STRFTIME('%Y', e.data_inicio) = ? AND CAST(STRFTIME('%m', e.data_inicio) AS INTEGER) {$op} 6",
                    [$ano]
                );
            }

            if (!empty($filtros['nome'])) {
                $query->where('users.name', 'like', '%' . $filtros['nome'] . '%');
            }

            $dados = $query->groupBy('users.id', 'users.name')
                ->orderBy('users.name', 'asc')
                ->get();

            $semestres = Evento::query()
                ->selectRaw("
                    CASE
                        WHEN CAST(STRFTIME('%m', data_inicio) AS INTEGER) <= 6
                        THEN STRFTIME('%Y', data_inicio) || '/1'
                        ELSE STRFTIME('%Y', data_inicio) || '/2'
                    END as semestre
                ")
                ->whereNull('deleted_at')
                ->distinct()
                ->orderByDesc('semestre')
                ->pluck('semestre')
                ->filter()
                ->values();

            return ['sucesso' => true, 'dados' => compact('dados', 'semestres'), 'erros' => []];
        } catch (\Throwable $th) {
            return ['sucesso' => false, 'dados' => ['dados' => [], 'semestres' => []], 'erros' => [formatarMensagemErro($th)]];
        }
    }

    public function store(array $dados): array
    {
        try {
            $model = Evento::create($dados);

            return [
                'sucesso' => $model && $model->id !== null,
                'dados'   => ['id' => $model->id, 'model' => $model],
                'erros'   => [],
            ];
        } catch (\Throwable $th) {
            return ['sucesso' => false, 'dados' => [], 'erros' => [formatarMensagemErro($th)]];
        }
    }

    public function update(string $id, array $dados): array
    {
        try {
            $model = Evento::findOrFail($id);
            $model->update($dados);

            return ['sucesso' => true, 'dados' => ['model' => $model], 'erros' => []];
        } catch (\Throwable $th) {
            return ['sucesso' => false, 'dados' => [], 'erros' => [formatarMensagemErro($th)]];
        }
    }

    public function destroy(string $id): array
    {
        try {
            $model   = Evento::findOrFail($id);
            $sucesso = $model->delete();

            return ['sucesso' => $sucesso, 'dados' => [], 'erros' => []];
        } catch (\Throwable $th) {
            return ['sucesso' => false, 'dados' => [], 'erros' => [formatarMensagemErro($th)]];
        }
    }
}
