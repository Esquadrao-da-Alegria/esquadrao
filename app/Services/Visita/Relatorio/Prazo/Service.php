<?php

namespace App\Services\Visita\Relatorio\Prazo;

use App\Models\Visita;
use Carbon\CarbonInterface;

class Service
{
    public const HORAS = 48;

    public function foraDoPrazo(Visita $visita, CarbonInterface $enviadoEm): bool
    {
        $base = $visita->created_at && $visita->created_at->gt($visita->fim_em)
            ? $visita->created_at
            : $visita->fim_em;

        return $enviadoEm->gt($base->copy()->addHours(self::HORAS));
    }
}
