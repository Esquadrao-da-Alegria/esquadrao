<?php

namespace Tests\Unit\Dashboard\Visita\Participante;

use App\Services\Dashboard\Visita\Participante\Compensacao\Service;
use PHPUnit\Framework\TestCase;

class CompensacaoTest extends TestCase
{
    public function test_credito_excedente_compensa_somente_mes_seguinte(): void
    {
        $resultado = (new Service())->calcular(
            ['2026-01' => 4, '2026-02' => 0, '2026-03' => 0],
            ['2026-01', '2026-02', '2026-03']
        );

        $this->assertSame(2, $resultado[0]['credito_transferido']);
        $this->assertSame(2, $resultado[1]['credito_anterior_utilizado']);
        $this->assertSame('compensado', $resultado[1]['situacao']);
        $this->assertSame(2, $resultado[2]['debito_transferido']);
    }

    public function test_excedente_do_mes_seguinte_compensa_debito_anterior(): void
    {
        $resultado = (new Service())->calcular(
            ['2026-01' => 1, '2026-02' => 3],
            ['2026-01', '2026-02']
        );

        $this->assertSame(1, $resultado[0]['debito_transferido']);
        $this->assertSame(1, $resultado[1]['debito_anterior_compensado']);
        $this->assertSame('compensado', $resultado[1]['situacao']);
    }

    public function test_credito_e_limitado_a_duas_visitas(): void
    {
        $resultado = (new Service())->calcular(['2026-01' => 8], ['2026-01']);

        $this->assertSame(2, $resultado[0]['credito_transferido']);
    }
}
