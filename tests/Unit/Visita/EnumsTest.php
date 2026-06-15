<?php

namespace Tests\Unit\Visita;

use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use PHPUnit\Framework\TestCase;

class EnumsTest extends TestCase
{
    public function test_visita_tipo_tem_valores_esperados(): void
    {
        $this->assertSame('hospital', VisitaTipo::Hospital->value);
        $this->assertSame('residencia', VisitaTipo::Residencia->value);
        $this->assertSame('acao_especial', VisitaTipo::AcaoEspecial->value);
        $this->assertSame('oficina', VisitaTipo::Oficina->value);
        $this->assertSame('reuniao', VisitaTipo::Reuniao->value);
        $this->assertSame('outro', VisitaTipo::Outro->value);
        $this->assertCount(6, VisitaTipo::cases());
    }

    public function test_visita_status_tem_valores_esperados(): void
    {
        $this->assertSame('agendada', VisitaStatus::Agendada->value);
        $this->assertSame('cancelada', VisitaStatus::Cancelada->value);
        $this->assertSame('realizada', VisitaStatus::Realizada->value);
        $this->assertSame('pendente', VisitaStatus::Pendente->value);
        $this->assertCount(4, VisitaStatus::cases());
    }

    public function test_visita_origem_tem_valores_esperados(): void
    {
        $this->assertSame('sistema', VisitaOrigem::Sistema->value);
        $this->assertSame('importacao', VisitaOrigem::Importacao->value);
        $this->assertSame('outro', VisitaOrigem::Outro->value);
        $this->assertCount(3, VisitaOrigem::cases());
    }
}
