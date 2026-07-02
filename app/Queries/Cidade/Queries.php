<?php

namespace App\Queries\Cidade;

use App\Models\Cidade;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Queries
{
    public function index(array $filtros): Collection
    {
        try {
            $query = Cidade::query();

            $this->aplicarFiltros($query, $filtros);

            return $query->get();
        } catch (\Throwable $th) {

            return new Collection();
        }
    }

    public function show(array $filtros): Cidade|null
    {
        try {

            $query = Cidade::query();

            $this->aplicarFiltros($query, $filtros);

            return $query->first();
        } catch (\Throwable $th) {

            return null;
        }
    }

    private function aplicarFiltros(Builder $query, array $filtros): void
    {
        // NOME
        if (!empty($filtros['nome'])) {

            $query->where('nome', 'like', '%' . $filtros['nome'] . '%');
        }

        // ESTADO
        if (!empty($filtros['estado_id'])) {

            $query->where('estado_id', $filtros['estado_id']);
        }
    }
}
