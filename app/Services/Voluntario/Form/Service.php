<?php

namespace App\Services\Voluntario\Form;

use App\Models\Cargo;
use App\Models\User;

class Service
{
    public function buscarDados(?User $voluntario): array
    {
        $cargos = Cargo::query()->orderBy('nome')->get();

        $retorno = [
            'cargos' => $cargos,
        ];

        if ($voluntario) {
            $voluntario->load('cargos');
            $retorno['voluntario'] = $voluntario;
        }

        return $retorno;
    }
}
