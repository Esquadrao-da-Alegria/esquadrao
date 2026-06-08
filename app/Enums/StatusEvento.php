<?php

namespace App\Enums;

enum StatusEvento: string
{
    case AGENDADO = 'AGENDADO';
    case FINALIZADO = 'FINALIZADO';
    case CANCELADO = 'CANCELADO';
    case TRANSFERIDO = 'TRANSFERIDO';
}
