<?php

namespace App\Queries\Hospital;

use App\Models\Hospital;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Queries
{
    public function index(array $filtros): array
    {
        $retornarLista = $filtros['retornar_lista'];

        try {
            $query = Hospital::query();

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
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }

    private function aplicarFiltros(Builder $query, array $filtros)
    {
        foreach ($filtros as $campo => $valor) {

            if (empty($valor)) continue;

            switch ($campo) {

                case 'id':

                    $query->where('id', $valor);

                    break;

                case 'nome':

                    $query->where('nome', 'like', "%{$valor}%");

                    break;

                case 'nome_exato':

                    $query->where('nome', $valor);

                    break;

                case 'ativo':

                    $query->where('ativo', $valor);

                    break;
            }
        }

        return $query;
    }

    public function store(array $dados): array
    {
        try {

            $model = Hospital::create($dados);

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

            $model = Hospital::findOrFail($id);

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

            $model = Hospital::findOrFail($id);

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
