<?php

namespace App\Services\Estado;

use App\Queries\Estado\Queries;

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
