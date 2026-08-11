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

        if ($slugs->intersect(['apoio', 'psicologia'])->isNotEmpty()) {
            return 'isento';
        }

        if ($slugs->intersect(['administrador', 'diretor', 'coordenador_geral', 'coordenador_local'])->isNotEmpty()) {
            return 'administrativo';
        }

        if ($slugs->intersect(['artista', 'voluntario'])->isNotEmpty()) {
            return 'visitas';
        }

        return 'dados_insuficientes';
    }
}
