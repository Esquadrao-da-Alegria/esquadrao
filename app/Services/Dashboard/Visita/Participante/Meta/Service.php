<?php

namespace App\Services\Dashboard\Visita\Participante\Meta;

use App\Models\User;

class Service
{
    public const META_VISITAS = 2;
    public const META_PRESENCA = 50;
    public const INATIVIDADE_DIAS = 60;

    public function tipo(User $user): string
    {
        $slugs = $user->cargos->pluck('slug');

        if ($slugs->contains('apoio')) {
            return 'isento';
        }

        if ($slugs->isNotEmpty()) {
            return 'visitas';
        }

        return 'dados_insuficientes';
    }
}
