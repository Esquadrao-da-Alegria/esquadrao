<?php

namespace App\Enums;

enum StatusParticipacao: string
{
    case Confirmado = 'confirmado';
    case Pendente = 'pendente';
    case Cancelado = 'cancelado';
    case Falta = 'falta';
}
