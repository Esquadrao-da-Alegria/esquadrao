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
            ['semana' => 1, 'dia_inicio' => 1, 'dia_fim' => 4],
            ['semana' => 2, 'dia_inicio' => 5, 'dia_fim' => 11],
            ['semana' => 3, 'dia_inicio' => 12, 'dia_fim' => 18],
            ['semana' => 4, 'dia_inicio' => 19, 'dia_fim' => 25],
            ['semana' => 5, 'dia_inicio' => 26, 'dia_fim' => 30],
        ], $semanas);
    }

    public function test_calcula_semanas_quando_mes_comeca_no_sabado(): void
    {
        $semanas = MetaHospital::semanasDoMes(2026, 8);

        $this->assertSame([
            ['semana' => 1, 'dia_inicio' => 1, 'dia_fim' => 1],
            ['semana' => 2, 'dia_inicio' => 2, 'dia_fim' => 8],
            ['semana' => 3, 'dia_inicio' => 9, 'dia_fim' => 15],
            ['semana' => 4, 'dia_inicio' => 16, 'dia_fim' => 22],
            ['semana' => 5, 'dia_inicio' => 23, 'dia_fim' => 29],
            ['semana' => 6, 'dia_inicio' => 30, 'dia_fim' => 31],
        ], $semanas);
    }

    public function test_sql_semana_visita_usa_faixas_do_mes(): void
    {
        $sql = MetaHospital::sqlSemanaVisita(2026, 4);

        $this->assertStringContainsString('WHEN DAY(inicio_em) BETWEEN 1 AND 4 THEN 1', $sql);
        $this->assertStringContainsString('WHEN DAY(inicio_em) BETWEEN 26 AND 30 THEN 5', $sql);
    }
}
