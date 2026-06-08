<?php

namespace App\Queries\Evento;

use App\Models\Evento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Queries
{
    public function index(array $filtros): array
    {
        $retornarLista = $filtros['retornar_lista'] ?? true;

        try {
            $query = Evento::query()->with('cidade');

            $this->aplicarFiltros($query, $filtros);

            $dados = $retornarLista ? $query->get() : $query->first();

            return [
                'sucesso' => true,
                'dados'   => $dados,
                'erros'   => []
            ];
        } catch (\Throwable $th) {

            $dados = $retornarLista ? new Collection() : null;

            return [
                'sucesso' => false,
                'dados'   => $dados,
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }

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
    }

    public function store(array $dados): array
    {
        try {

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
        }
    }

    public function update(string $id, array $dados): array
    {
        try {

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
        }
    }

    public function destroy(string $id): array
    {
        try {

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
        }
    }
}
