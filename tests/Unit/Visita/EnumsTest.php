<?php

namespace Tests\Unit\Visita;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
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
        $this->assertSame('realizada', VisitaStatus::Realizada->value);
        $this->assertSame('cancelada', VisitaStatus::Cancelada->value);
        $this->assertSame('pendente_relatorio', VisitaStatus::PendenteRelatorio->value);
        $this->assertSame('contabilizada', VisitaStatus::Contabilizada->value);
        $this->assertSame('nao_contabilizada', VisitaStatus::NaoContabilizada->value);
        $this->assertCount(6, VisitaStatus::cases());
    }

    public function test_visita_origem_tem_valores_esperados(): void
    {
        $this->assertSame('sistema', VisitaOrigem::Sistema->value);
        $this->assertSame('importacao', VisitaOrigem::Importacao->value);
        $this->assertSame('outro', VisitaOrigem::Outro->value);
        $this->assertCount(3, VisitaOrigem::cases());
    }

    public function test_tipo_participacao_tem_valores_esperados(): void
    {
        $this->assertSame('palhaco', TipoParticipacao::Palhaco->value);
        $this->assertSame('paisana', TipoParticipacao::Paisana->value);
        $this->assertCount(2, TipoParticipacao::cases());
    }

    public function test_papel_na_visita_tem_valores_esperados(): void
    {
        $this->assertSame('participante', PapelNaVisita::Participante->value);
        $this->assertSame('relator', PapelNaVisita::Relator->value);
        $this->assertCount(2, PapelNaVisita::cases());
    }

    public function test_status_participacao_tem_valores_esperados(): void
    {
        $this->assertSame('confirmado', StatusParticipacao::Confirmado->value);
        $this->assertSame('pendente', StatusParticipacao::Pendente->value);
        $this->assertSame('cancelado', StatusParticipacao::Cancelado->value);
        $this->assertSame('falta', StatusParticipacao::Falta->value);
        $this->assertCount(4, StatusParticipacao::cases());
    }
}
