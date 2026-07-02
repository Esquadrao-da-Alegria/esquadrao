<?php

namespace Tests\Feature\Visita;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_tabela_visitas(): void
    {
        $this->assertTrue(Schema::hasTable('visitas'));
        $this->assertTrue(Schema::hasColumn('visitas', 'hospital_id'));
        $this->assertTrue(Schema::hasColumn('visitas', 'inicio_em'));
        $this->assertTrue(Schema::hasColumn('visitas', 'tipo'));
        $this->assertTrue(Schema::hasColumn('visitas', 'status'));
    }

    public function test_cria_tabela_visita_participante(): void
    {
        $this->assertTrue(Schema::hasTable('visita_participante'));
        $this->assertTrue(Schema::hasColumn('visita_participante', 'visita_id'));
        $this->assertTrue(Schema::hasColumn('visita_participante', 'voluntario_id'));
        $this->assertTrue(Schema::hasColumn('visita_participante', 'status_participacao'));
    }
}
