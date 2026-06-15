<?php

namespace App\Enums;

enum VisitaStatus: string
{
    case Agendada = 'agendada';
    case Cancelada = 'cancelada';
    case Realizada = 'realizada';
    case Pendente = 'pendente';
}
