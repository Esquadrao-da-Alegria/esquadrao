<?php

namespace App\Queries\Estado;

use App\Models\Estado;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Queries
{
    public function index(array $filtros): Collection
    {
        try {

            $query = Estado::query();

            $this->aplicarFiltros($query, $filtros);

            return $query->get();
        } catch (\Throwable $th) {

            return new Collection();
        }
    }
    public function show(array $filtros): Collection|Estado|null
    {
        try {

            $query = Estado::query();

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

            $query->where('nome', $filtros['nome']);
        }

        // ESTADO
        $apenasAtivos = $filtros['apenas_ativos'] ?? false;
        if ($apenasAtivos) {

            $query->where('id', 24);
        }
    }
}
