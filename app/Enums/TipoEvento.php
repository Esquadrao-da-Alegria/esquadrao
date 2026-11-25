<?php

namespace App\Enums;

enum TipoEvento: string
{
    case Oficina = 'oficina';
    case Reuniao = 'reuniao';
    case Evento = 'evento';
}
