<?php

namespace App\Enums;

enum TipoAjusteEvento: string
{
    case CorrecaoInscricao = 'correcao_inscricao';
    case CorrecaoPresenca = 'correcao_presenca';
}
