<?php

namespace App\Enums;

enum StatusInscricao: string
{
    case INSCRITO = 'INSCRITO';
    case PRESENTE = 'PRESENTE';
    case AUSENTE = 'AUSENTE';
    case CANCELADO = 'CANCELADO';
}
