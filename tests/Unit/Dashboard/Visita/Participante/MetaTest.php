<?php

namespace Tests\Unit\Dashboard\Visita\Participante;

use App\Models\Cargo;
use App\Models\User;
use App\Services\Dashboard\Visita\Participante\Meta\Service;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

class MetaTest extends TestCase
{
    public function test_voluntario_e_artista_recebem_meta_de_visitas(): void
    {
        $this->assertSame('visitas', $this->tipo('voluntario'));
        $this->assertSame('visitas', $this->tipo('artista'));
    }

    public function test_apoio_e_psicologia_sao_isentos(): void
    {
        $this->assertSame('isento', $this->tipo('apoio'));
        $this->assertSame('isento', $this->tipo('psicologia'));
    }

    public function test_coordenacao_e_classificada_como_administrativa(): void
    {
        $this->assertSame('administrativo', $this->tipo('coordenador_geral'));
    }

    private function tipo(string $slug): string
    {
        $user = new User();
        $user->setRelation('cargos', new Collection([new Cargo(['slug' => $slug])]));

        return (new Service())->tipo($user);
    }
}
