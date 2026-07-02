<?php

namespace App\Services\Patrocinador\Form;

use App\Models\Patrocinador;

class Service
{
    public function buscarDados(?Patrocinador $patrocinador = null): array
    {
        $retorno = [];

        if ($patrocinador) {
            $retorno['patrocinador'] = $patrocinador;
        }

        return $retorno;
    }
}