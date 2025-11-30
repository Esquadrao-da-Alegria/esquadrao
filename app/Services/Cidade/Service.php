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

    public function index(array $filtros): array
    {
        try {

            $lista = $this->queries->index($filtros);

            return [
                'sucesso' => true,
                'dados'   => $lista,
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
