<?php

namespace App\Services\Dashboard\Visita\Participante\Compensacao;

use App\Services\Dashboard\Visita\Participante\Meta\Service as MetaService;

class Service
{
    /**
     * @param array<string, int> $visitasPorMes
     * @param list<string> $meses
     * @return list<array<string, int|string>>
     */
    public function calcular(array $visitasPorMes, array $meses): array
    {
        $resultado = [];
        $creditoAnterior = 0;
        $debitoAnterior = 0;

        foreach ($meses as $indice => $mes) {
            $visitas = $visitasPorMes[$mes] ?? 0;
            $excedente = max(0, $visitas - MetaService::META_VISITAS);
            $debitoCompensado = min($debitoAnterior, $excedente);
            $excedente -= $debitoCompensado;
            $faltaAtual = max(0, MetaService::META_VISITAS - $visitas);
            $creditoUtilizado = min($creditoAnterior, $faltaAtual);
            $debitoTransferido = max(0, $faltaAtual - $creditoUtilizado);
            $creditoTransferido = min(MetaService::META_VISITAS, $excedente);
            $mesAtualFormatado = now()->format('Y-m');
            $ehMesFuturo = $mes > $mesAtualFormatado;
            $debitoExpirado = $ehMesFuturo ? 0 : max(0, $debitoAnterior - $debitoCompensado);

            $situacao = 'dentro_meta';

            if ($debitoExpirado > 0) {
                $situacao = 'requer_analise';
            } elseif ($debitoTransferido > 0) {
                $situacao = ($indice === array_key_last($meses) || $ehMesFuturo) ? 'compensacao_pendente' : 'atencao';
            } elseif ($creditoUtilizado > 0 || $debitoCompensado > 0) {
                $situacao = 'compensado';
            }

            $resultado[] = [
                'mes' => $mes,
                'meta' => MetaService::META_VISITAS,
                'visitas' => $visitas,
                'saldo' => $visitas - MetaService::META_VISITAS,
                'credito_anterior_utilizado' => $creditoUtilizado,
                'debito_anterior_compensado' => $debitoCompensado,
                'credito_transferido' => $creditoTransferido,
                'debito_transferido' => $debitoTransferido,
                'debito_expirado' => $debitoExpirado,
                'situacao' => $situacao,
            ];

            $creditoAnterior = $creditoTransferido;
            $debitoAnterior = $debitoTransferido;
        }

        return $resultado;
    }
}
