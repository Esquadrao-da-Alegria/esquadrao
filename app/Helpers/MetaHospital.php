<?php

namespace App\Helpers;

// LIBS EXTERNAS
use Carbon\Carbon;

class MetaHospital
{
    /**
     * Semanas do mês alinhadas ao calendário (domingo a sábado), a partir do dia 1.
     *
     * @return array<int, array{semana: int, dia_inicio: int, dia_fim: int}>
     */
    public static function semanasDoMes(int $ano, int $mes): array
    {
        $diasNoMes = Carbon::create($ano, $mes, 1)->daysInMonth;
        $semanas   = [];
        $dia       = 1;
        $numero    = 1;

        while ($dia <= $diasNoMes) {
            $diaSemana     = Carbon::create($ano, $mes, $dia)->dayOfWeek;
            $diasAteSabado = 6 - $diaSemana;
            $diaFim        = min($dia + $diasAteSabado, $diasNoMes);

            $semanas[] = [
                'semana'     => $numero,
                'dia_inicio' => $dia,
                'dia_fim'    => $diaFim,
            ];

            $dia = $diaFim + 1;
            $numero++;
        }

        return $semanas;
    }

    /**
     * @return array<int, int>
     */
    public static function numerosSemanasDoMes(int $ano, int $mes): array
    {
        return array_column(self::semanasDoMes($ano, $mes), 'semana');
    }

    public static function sqlSemanaVisita(int $ano, int $mes): string
    {
        $quando = [];

        foreach (self::semanasDoMes($ano, $mes) as $faixa) {
            $quando[] = sprintf(
                'WHEN DAY(inicio_em) BETWEEN %d AND %d THEN %d',
                $faixa['dia_inicio'],
                $faixa['dia_fim'],
                $faixa['semana'],
            );
        }

        return 'CASE ' . implode(' ', $quando) . ' END';
    }

    public static function semanaValida(int $ano, int $mes, int $semana): bool
    {
        return in_array($semana, self::numerosSemanasDoMes($ano, $mes), true);
    }
}
