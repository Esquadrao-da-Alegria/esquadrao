<?php

namespace App\Services\Visita\Relatorio\Prazo;

use App\Models\Visita;
use Carbon\CarbonInterface;

class Service
{
    public const HORAS = 48;

    public function foraDoPrazo(Visita $visita, CarbonInterface $enviadoEm): bool
    {
        return $enviadoEm->gt($visita->fim_em->copy()->addHours(self::HORAS));
    }
}
