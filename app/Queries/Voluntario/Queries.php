<?php

namespace App\Queries\Voluntario;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Queries
{
    public function index(array $filtros): array
    {
        $retornarLista = $filtros['retornar_lista'];

        try {
            $query = User::query()->with('cargos')->whereHas('cargos');

            $this->aplicarFiltros($query, $filtros);

            $query->orderBy('name');

            $dados = $retornarLista ? $query->get() : $query->first();

            return [
                'sucesso' => true,
                'dados' => $dados,
                'erros' => [],
            ];
        } catch (\Throwable $th) {

            $dados = $retornarLista ? new Collection : null;

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    private function aplicarFiltros(Builder $query, array $filtros): Builder
    {
        foreach ($filtros as $campo => $valor) {

            if (empty($valor)) {
                continue;
            }

            switch ($campo) {

                case 'id':

                    $query->where('id', $valor);

                    break;

                case 'name':

                    $query->where('name', 'like', "%{$valor}%");

                    break;

                case 'email':

                    $query->where('email', 'like', "%{$valor}%");

                    break;
            }
        }

        return $query;
    }

    public function store(array $dados): array
    {
        try {

            $model = User::create($dados);

            $sucesso = $model && $model->id !== null;

            return [
                'sucesso' => $sucesso,
                'dados' => ['id' => $model->id, 'model' => $model],
                'erros' => [],
            ];
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function update(string $id, array $dados): array
    {
        try {

            $model = User::findOrFail($id);

            $model->update($dados);

            $sucesso = $model->save();

            return [
                'sucesso' => $sucesso,
                'dados' => ['model' => $model],
                'erros' => [],
            ];
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function destroy(string $id): array
    {
        try {

            $model = User::findOrFail($id);

            $sucesso = $model->delete();

            return [
                'sucesso' => $sucesso,
                'dados' => [],
                'erros' => [],
            ];
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }
}
