<?php

namespace App\Services\Voluntario\Form;

use App\Models\Cargo;
use App\Models\Voluntario;

class Service
{
    public function buscarDados(?Voluntario $voluntario): array
    {
        $cargos = Cargo::query()->orderBy('nome')->get();

        $retorno = [
            'cargos' => $cargos,
        ];

        if ($voluntario) {
            $voluntario->load('user.cargos');
            $retorno['voluntario'] = $voluntario;
        }

        return $retorno;
    }
}
