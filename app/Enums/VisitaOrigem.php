<?php

namespace App\Enums;

enum VisitaOrigem: string
{
    case Sistema = 'sistema';
    case Importacao = 'importacao';
    case Outro = 'outro';
}
