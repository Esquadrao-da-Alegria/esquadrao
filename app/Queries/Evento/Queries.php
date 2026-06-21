<?php

namespace App\Queries\Evento;

use App\Models\Evento;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Queries
{
=======
use App\Models\EventoParticipante;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class Queries
{
    public function __construct(private Filtros $filtros) {}

>>>>>>> 002-eventos-aprimoramentos
    public function index(array $filtros): array
    {
        $retornarLista = $filtros['retornar_lista'] ?? true;

        try {
<<<<<<< HEAD
            $query = Evento::query()->with('cidade');

            $this->aplicarFiltros($query, $filtros);
=======
            $query = Evento::query()->with(['cidade:id,nome']);

            $this->filtros->aplicar($query, $filtros);
>>>>>>> 002-eventos-aprimoramentos

            $dados = $retornarLista ? $query->get() : $query->first();

            return [
                'sucesso' => true,
                'dados'   => $dados,
<<<<<<< HEAD
                'erros'   => []
            ];
        } catch (\Throwable $th) {
=======
                'erros'   => [],
            ];
        } catch (\Throwable $th) {
            Log::error('Queries::index falhou: ' . $th->getMessage(), [
                'filtros' => $filtros,
                'sql'     => method_exists($query, 'toSql') ? $query->toSql() : 'indisponível',
            ]);
>>>>>>> 002-eventos-aprimoramentos

            $dados = $retornarLista ? new Collection() : null;

            return [
                'sucesso' => false,
                'dados'   => $dados,
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }

<<<<<<< HEAD
    private function aplicarFiltros(Builder $query, array $filtros)
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
                    $query->where('tipo', $valor);
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

                case 'data_inicio_de':
                    $query->where('data_inicio', '>=', $valor);
                    break;

                case 'data_inicio_ate':
                    $query->where('data_inicio', '<=', $valor);
                    break;

                case 'retornar_lista':
                    // handled externally
                    break;
            }
        }

        return $query;
=======
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
>>>>>>> 002-eventos-aprimoramentos
    }

    public function store(array $dados): array
    {
        try {
<<<<<<< HEAD

            $model = Evento::create($dados);

            $sucesso = $model && $model->id !== null;

            return [
                'sucesso' => $sucesso,
                'dados'   => ['id' => $model->id, 'model' => $model],
                'erros'   => []
            ];
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)],
            ];
=======
            $model = Evento::create($dados);

            return [
                'sucesso' => $model && $model->id !== null,
                'dados'   => ['id' => $model->id, 'model' => $model],
                'erros'   => [],
            ];
        } catch (\Throwable $th) {
            return ['sucesso' => false, 'dados' => [], 'erros' => [formatarMensagemErro($th)]];
>>>>>>> 002-eventos-aprimoramentos
        }
    }

    public function update(string $id, array $dados): array
    {
        try {
<<<<<<< HEAD

            $model = Evento::findOrFail($id);

            $model->update($dados);

            $sucesso = $model->save();

            return [
                'sucesso' => $sucesso,
                'dados'   => ['model' => $model],
                'erros'   => []
            ];
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)],
            ];
=======
            $model = Evento::findOrFail($id);
            $model->update($dados);

            return ['sucesso' => true, 'dados' => ['model' => $model], 'erros' => []];
        } catch (\Throwable $th) {
            return ['sucesso' => false, 'dados' => [], 'erros' => [formatarMensagemErro($th)]];
>>>>>>> 002-eventos-aprimoramentos
        }
    }

    public function destroy(string $id): array
    {
        try {
<<<<<<< HEAD

            $model = Evento::findOrFail($id);

            $sucesso = $model->delete();

            return [
                'sucesso' => $sucesso,
                'dados'   => [],
                'erros'   => []
            ];
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)],
            ];
=======
            $model   = Evento::findOrFail($id);
            $sucesso = $model->delete();

            return ['sucesso' => $sucesso, 'dados' => [], 'erros' => []];
        } catch (\Throwable $th) {
            return ['sucesso' => false, 'dados' => [], 'erros' => [formatarMensagemErro($th)]];
>>>>>>> 002-eventos-aprimoramentos
        }
    }
}
