<?php

namespace App\Enums;

enum MotivoAfastamento: string
{
    case AtestadoMedico = 'atestado_medico';
    case LicencaPessoal = 'licenca_pessoal';
    case Estudos = 'estudos';
    case Outro = 'outro';
}
