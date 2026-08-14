<?php

namespace App\Services\Dashboard\Permissao;

use App\Models\User;

class Service
{
    public const MEU_DASHBOARD = 'dashboard.meu';
    public const VISAO_GERAL = 'dashboard.visao_geral';
    public const VISITAS_POR_HOSPITAL = 'dashboard.visitas_por_hospital';
    public const VISITAS_POR_PARTICIPANTE = 'dashboard.visitas_por_participante';

    public const PERMISSOES = [
        self::MEU_DASHBOARD,
        self::VISAO_GERAL,
        self::VISITAS_POR_HOSPITAL,
        self::VISITAS_POR_PARTICIPANTE,
    ];

    private const PERMISSOES_GERENCIAIS = [
        self::VISAO_GERAL,
        self::VISITAS_POR_HOSPITAL,
        self::VISITAS_POR_PARTICIPANTE,
    ];

    public function permite(User $user, string $permissao): bool
    {
        if ($permissao === self::MEU_DASHBOARD) {
            return true;
        }

        if (! in_array($permissao, self::PERMISSOES_GERENCIAIS, true)) {
            return false;
        }

        $user->loadMissing(['cargos', 'voluntario']);

        if ($permissao === self::VISITAS_POR_PARTICIPANTE) {
            return $user->cargos->contains(fn ($cargo) => $cargo->slug === 'administrador');
        }

        if ($user->cargos->contains(fn ($cargo) => in_array($cargo->slug, ['administrador', 'coordenador_geral'], true))) {
            return true;
        }

        return $user->cargos->contains(fn ($cargo) => $cargo->slug === 'coordenador_local')
            && $user->voluntario?->cidade_base_id !== null;
    }

    /**
     * @return array<string, bool>
     */
    public function permissoes(User $user): array
    {
        return [
            self::MEU_DASHBOARD => $this->permite($user, self::MEU_DASHBOARD),
            self::VISAO_GERAL => $this->permite($user, self::VISAO_GERAL),
            self::VISITAS_POR_HOSPITAL => $this->permite($user, self::VISITAS_POR_HOSPITAL),
            self::VISITAS_POR_PARTICIPANTE => $this->permite($user, self::VISITAS_POR_PARTICIPANTE),
        ];
    }
}
