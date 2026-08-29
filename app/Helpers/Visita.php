<?php

namespace App\Helpers;

// ENUMS
use App\Enums\VisitaStatus;

class Visita
{
    /**
     * @return array<int, VisitaStatus>
     */
    public static function statusRealizadas(): array
    {
        return [
            VisitaStatus::Realizada,
            VisitaStatus::PendenteRelatorio,
            VisitaStatus::Contabilizada,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function statusRealizadasValores(): array
    {
        return array_map(
            fn (VisitaStatus $status) => $status->value,
            self::statusRealizadas()
        );
    }
}
