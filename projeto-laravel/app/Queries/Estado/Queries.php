<?php

namespace App\Queries\Estado;

use App\Models\Estado;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Queries
{
    public function getByCustom(array $filtros): Collection|Estado|null
    {
        try {
            $collection = $filtros['lista'];

            $query = Estado::query();

            $this->aplicarFiltros($query, $filtros);

            return $collection ? $query->get() : $query->first();
        } catch (\Throwable $th) {
            return null;
        }
    }

    private function aplicarFiltros(Builder $query, array $filtros): void
    {
        // NOME
        if (!empty($filtros['nome'])) {

            $query->where('nome', $filtros['nome']);
        }

        // ESTADO
        if (!empty($filtros['sigla'])) {

            $query->where('sigla', $filtros['sigla']);
        }
    }
}
