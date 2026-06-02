<?php

namespace App\Queries\Patrocinador;

use App\Models\Patrocinador;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Queries
{
    public function index(array $filtros): Collection|Patrocinador|null
    {
        $retornarLista = $filtros['retornar_lista'];

        try {
            $query = Patrocinador::query();

            $this->aplicarFiltros($query, $filtros);

            return $retornarLista ? $query->get() : $query->first();
        } catch (\Throwable $th) {

            return $retornarLista ? new Collection() : null;
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

    public function store(array $dados): string|null
    {
        try {

            $model = Patrocinador::create($dados);

            return $model->id;
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function update(string $id, array $dados): bool
    {
        try {

            $model = Patrocinador::findOrFail($id);

            $model->update($dados);

            return $model->save();
        } catch (\Throwable $th) {

            return false;
        }
    }

    public function destroy(string $id): bool
    {
        try {

            $model = Patrocinador::findOrFail($id);

            return $model->delete();
        } catch (\Throwable $th) {

            return false;
        }
    }
}
