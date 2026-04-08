<?php

namespace App\Services\Hospital\Form;

use App\Models\Hospital;
use App\Services\Cidade\Service as CidadeService;

class Service
{
    public function __construct(
        private CidadeService $cidadeService
    ) {
        //
    }

    public function buscarDados(Hospital|null $hospital): array
    {
        $cidades = $this->cidadeService->index(['estado_id' => 43])['dados'];

        $retorno = [
            'cidades' => $cidades
        ];

        if ($hospital) $retorno['hospital'] = $hospital;

        return $retorno;
    }
}
