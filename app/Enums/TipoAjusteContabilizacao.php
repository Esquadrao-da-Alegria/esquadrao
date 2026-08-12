<?php

namespace App\Enums;

enum TipoAjusteContabilizacao: string
{
    case CorrecaoParticipacao = 'correcao_participacao';
    case AceiteRelatorioForaPrazo = 'aceite_relatorio_fora_prazo';
}
