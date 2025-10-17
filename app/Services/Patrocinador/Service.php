<?php

namespace App\Services\Patrocinador;

use App\Models\Patrocinador;
use App\Queries\Patrocinador\Queries;
use Illuminate\Database\Eloquent\Collection;

class Service
{
    public function __construct(private Queries $queries) {}

    public function index(array $filtros): Collection|Patrocinador|null
    {
        $retornarLista = $filtros['retornar_lista'];

        try {

            return $this->queries->index($filtros);
        } catch (\Throwable $th) {

            return $retornarLista ? new Collection() : null;
        }
    }

    public function store(array $dados): int|null
    {
        try {

            $sucesso = $this->queries->store($dados);

            return $sucesso;
        } catch (\Throwable $th) {

            return null;
        }
    }

    public function update(string $id, array $dados): bool
    {
        try {

            $sucesso = $this->queries->update($id, $dados);

            return $sucesso;
        } catch (\Throwable $th) {

            return false;
        }
    }

    public function destroy(string $id): bool
    {
        try {

            $sucesso = $this->queries->destroy($id);

            return $sucesso;
        } catch (\Throwable $th) {

            return false;
        }
    }
}
