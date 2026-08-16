<?php

namespace App\Enums;

enum StatusAfastamento: string
{
    case Ativo = 'ativo';
    case Encerrado = 'encerrado';
    case Prorrogado = 'prorrogado';
    case Cancelado = 'cancelado';
}
