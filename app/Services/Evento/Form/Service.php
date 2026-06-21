<?php

namespace App\Services\Evento\Form;

use App\Models\Evento;
use App\Models\User;
use App\Services\Cidade\Service as CidadeService;

class Service
{
    public function __construct(private CidadeService $cidadeService) {}

    public function buscarDados(?Evento $evento): array
    {
        $cidades  = $this->cidadeService->index(['estado_id' => 43])['dados'];
        $usuarios = User::orderBy('name')->get(['id', 'name']);

        $retorno = ['cidades' => $cidades, 'usuarios' => $usuarios];

        if ($evento) {
            $evento->load('responsaveis');
            $retorno['evento'] = $evento;
        }

        return $retorno;
    }
}
