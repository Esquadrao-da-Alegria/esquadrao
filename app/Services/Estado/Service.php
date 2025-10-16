<?php

namespace App\Services\Estado;

use App\Models\Estado;
use App\Queries\Estado\Queries;
use Illuminate\Database\Eloquent\Collection;

class Service
{
    public function __construct(private Queries $queries)
    {
        //
    }

    public function index(array $filtros): Collection|Estado|null
    {
        return $this->queries->getByCustom($filtros);
    }
}
