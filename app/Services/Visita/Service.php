<?php

namespace App\Services\Visita;

use App\Queries\Visita\Queries;

class Service
{
    public function __construct(private Queries $queries) {}

    public function index(array $filtros): array
    {
        try {
            $retorno = $this->queries->index($filtros);

            if (!$retorno['sucesso']) {
                session()->flash('mensagem_erro', 'Erro ao listar visitas!');
            }

            return $retorno;
        } catch (\Throwable $th) {
            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }
}
