<?php

namespace App\Services\Cidade;

use App\Models\Cidade;
use App\Queries\Cidade\Queries;
use Illuminate\Database\Eloquent\Collection;

class Service
{
    public function __construct(private Queries $queries)
    {
        //
    }

    public function index(array $filtros): Collection|Cidade|null
    {
        return $this->queries->getByCustom($filtros);
    }
}
