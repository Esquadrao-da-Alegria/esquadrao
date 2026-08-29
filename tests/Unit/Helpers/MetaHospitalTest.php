<?php

namespace Tests\Unit\Helpers;

use App\Helpers\MetaHospital;
use PHPUnit\Framework\TestCase;

class MetaHospitalTest extends TestCase
{
    public function test_calcula_semanas_alinhadas_ao_calendario_quando_mes_comeca_na_quarta(): void
    {
        $semanas = MetaHospital::semanasDoMes(2026, 4);

        $this->assertSame([
            [
                'semana'          => 1,
                'dia_inicio'      => 1,
                'dia_fim'         => 4,
                'nome_dia_inicio' => 'quarta-feira',
                'nome_dia_fim'    => 'sábado',
            ],
            [
                'semana'          => 2,
                'dia_inicio'      => 5,
                'dia_fim'         => 11,
                'nome_dia_inicio' => 'domingo',
                'nome_dia_fim'    => 'sábado',
            ],
            [
                'semana'          => 3,
                'dia_inicio'      => 12,
                'dia_fim'         => 18,
                'nome_dia_inicio' => 'domingo',
                'nome_dia_fim'    => 'sábado',
            ],
            [
                'semana'          => 4,
                'dia_inicio'      => 19,
                'dia_fim'         => 25,
                'nome_dia_inicio' => 'domingo',
                'nome_dia_fim'    => 'sábado',
            ],
            [
                'semana'          => 5,
                'dia_inicio'      => 26,
                'dia_fim'         => 30,
                'nome_dia_inicio' => 'domingo',
                'nome_dia_fim'    => 'quinta-feira',
            ],
        ], $semanas);
    }

    public function test_calcula_semanas_quando_mes_comeca_no_domingo(): void
    {
        $semanas = MetaHospital::semanasDoMes(2026, 2);

        $this->assertSame([
            [
                'semana'          => 1,
                'dia_inicio'      => 1,
                'dia_fim'         => 7,
                'nome_dia_inicio' => 'domingo',
                'nome_dia_fim'    => 'sábado',
            ],
            [
                'semana'          => 2,
                'dia_inicio'      => 8,
                'dia_fim'         => 14,
                'nome_dia_inicio' => 'domingo',
                'nome_dia_fim'    => 'sábado',
            ],
            [
                'semana'          => 3,
                'dia_inicio'      => 15,
                'dia_fim'         => 21,
                'nome_dia_inicio' => 'domingo',
                'nome_dia_fim'    => 'sábado',
            ],
            [
                'semana'          => 4,
                'dia_inicio'      => 22,
                'dia_fim'         => 28,
                'nome_dia_inicio' => 'domingo',
                'nome_dia_fim'    => 'sábado',
            ],
        ], $semanas);
    }

    public function test_calcula_semanas_quando_mes_comeca_no_sabado(): void
    {
        $semanas = MetaHospital::semanasDoMes(2026, 8);

        $this->assertSame([
            [
                'semana'          => 1,
                'dia_inicio'      => 1,
                'dia_fim'         => 1,
                'nome_dia_inicio' => 'sábado',
                'nome_dia_fim'    => 'sábado',
            ],
            [
                'semana'          => 2,
                'dia_inicio'      => 2,
                'dia_fim'         => 8,
                'nome_dia_inicio' => 'domingo',
                'nome_dia_fim'    => 'sábado',
            ],
            [
                'semana'          => 3,
                'dia_inicio'      => 9,
                'dia_fim'         => 15,
                'nome_dia_inicio' => 'domingo',
                'nome_dia_fim'    => 'sábado',
            ],
            [
                'semana'          => 4,
                'dia_inicio'      => 16,
                'dia_fim'         => 22,
                'nome_dia_inicio' => 'domingo',
                'nome_dia_fim'    => 'sábado',
            ],
            [
                'semana'          => 5,
                'dia_inicio'      => 23,
                'dia_fim'         => 29,
                'nome_dia_inicio' => 'domingo',
                'nome_dia_fim'    => 'sábado',
            ],
            [
                'semana'          => 6,
                'dia_inicio'      => 30,
                'dia_fim'         => 31,
                'nome_dia_inicio' => 'domingo',
                'nome_dia_fim'    => 'segunda-feira',
            ],
        ], $semanas);
    }

    public function test_sql_semana_visita_usa_faixas_do_mes(): void
    {
        $sql = MetaHospital::sqlSemanaVisita(2026, 4);

        $this->assertStringContainsString('WHEN DAY(inicio_em) BETWEEN 1 AND 4 THEN 1', $sql);
        $this->assertStringContainsString('WHEN DAY(inicio_em) BETWEEN 26 AND 30 THEN 5', $sql);
    }
}
