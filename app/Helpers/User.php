<?php

namespace App\Helpers;

// MODELS
use App\Models\User as UserModel;

class User
{
    /**
     * @return array<int, string>
     */
    public static function slugsGestor(): array
    {
        return [
            'administrador',
            'diretor',
            'coordenador_geral',
            'coordenador_local',
        ];
    }

    public static function ehGestor(UserModel $user): bool
    {
        resolverUsuario($user);

        if (! $user->voluntario?->cidade_base_id) {
            return false;
        }

        return $user->cargos->contains(
            fn ($cargo) => in_array($cargo->slug, self::slugsGestor(), true)
        );
    }
}
