<?php

namespace App\Helpers;

// LIBS EXTERNAS
use Carbon\Carbon;

class MetaHospital
{
    /**
     * Semanas do mês com ciclos de domingo a sábado.
     *
     * - Semanas completas: domingo → sábado (fecha no sábado).
     * - Primeira semana quebrada: quando o mês não começa no domingo (dia 1 até o sábado).
     * - Última semana quebrada: quando o mês não termina no sábado (domingo até o último dia).
     *
     * @return array<int, array{
     *     semana: int,
     *     dia_inicio: int,
     *     dia_fim: int,
     *     nome_dia_inicio: string,
     *     nome_dia_fim: string,
     * }>
     */
    public static function semanasDoMes(int $ano, int $mes): array
    {
        $diasNoMes = Carbon::create($ano, $mes, 1)->daysInMonth;
        $semanas   = [];
        $dia       = 1;
        $numero    = 1;

        while ($dia <= $diasNoMes) {
            $diaSemana = Carbon::create($ano, $mes, $dia)->dayOfWeek;

            if ($numero === 1 && $diaSemana !== Carbon::SUNDAY) {
                $diaFim = min($dia + (Carbon::SATURDAY - $diaSemana), $diasNoMes);
            } else {
                $diaFim = min($dia + 6, $diasNoMes);
            }

            $semanas[] = [
                'semana'          => $numero,
                'dia_inicio'      => $dia,
                'dia_fim'         => $diaFim,
                'nome_dia_inicio' => self::nomeDia($ano, $mes, $dia),
                'nome_dia_fim'    => self::nomeDia($ano, $mes, $diaFim),
            ];

            $dia = $diaFim + 1;
            $numero++;
        }

        return $semanas;
    }

    private static function nomeDia(int $ano, int $mes, int $dia): string
    {
        return mb_strtolower(
            Carbon::create($ano, $mes, $dia)->locale('pt_BR')->translatedFormat('l'),
        );
    }

    /**
     * @return array<int, int>
     */
    public static function numerosSemanasDoMes(int $ano, int $mes): array
    {
        return array_column(self::semanasDoMes($ano, $mes), 'semana');
    }

    public static function sqlSemanaVisita(int $ano, int $mes, string $expressaoDia = 'DAY(inicio_em)'): string
    {
        $quando = [];

        foreach (self::semanasDoMes($ano, $mes) as $faixa) {
            $quando[] = sprintf(
                'WHEN %s BETWEEN %d AND %d THEN %d',
                $expressaoDia,
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
