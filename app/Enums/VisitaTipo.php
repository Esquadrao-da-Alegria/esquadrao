<?php

namespace App\Enums;

enum VisitaTipo: string
{
    case Hospital = 'hospital';
    case Residencia = 'residencia';
    case AcaoEspecial = 'acao_especial';
    case Outro = 'outro';
}
