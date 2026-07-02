<?php

namespace App\Enums;

enum VisitaStatus: string
{
    case Agendada = 'agendada';
    case Realizada = 'realizada';
    case Cancelada = 'cancelada';
    case PendenteRelatorio = 'pendente_relatorio';
    case Contabilizada = 'contabilizada';
    case NaoContabilizada = 'nao_contabilizada';
}
