<?php

namespace Tests\Unit\Dashboard\Visita\Participante;

use App\Models\Cargo;
use App\Models\User;
use App\Services\Dashboard\Visita\Participante\Meta\Service;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

class MetaTest extends TestCase
{
    public function test_meta_de_presenca_e_cinquenta_por_cento(): void
    {
        $this->assertSame(50, Service::META_PRESENCA);
    }

    public function test_voluntario_e_artista_recebem_meta_de_visitas(): void
    {
        $this->assertSame('visitas', $this->tipo('voluntario'));
        $this->assertSame('visitas', $this->tipo('artista'));
    }

    public function test_somente_apoio_e_isento(): void
    {
        $this->assertSame('isento', $this->tipo('apoio'));
        $this->assertSame('visitas', $this->tipo('psicologia'));
    }

    public function test_cargos_de_gestao_recebem_meta_de_visitas(): void
    {
        foreach (['administrador', 'diretor', 'coordenador_geral', 'coordenador_local'] as $slug) {
            $this->assertSame('visitas', $this->tipo($slug));
        }
    }

    public function test_apoio_prevalece_em_usuario_com_multiplos_cargos(): void
    {
        $user = new User();
        $user->setRelation('cargos', new Collection([
            new Cargo(['slug' => 'administrador']),
            new Cargo(['slug' => 'voluntario']),
            new Cargo(['slug' => 'apoio']),
        ]));

        $this->assertSame('isento', (new Service())->tipo($user));
    }

    private function tipo(string $slug): string
    {
        $user = new User();
        $user->setRelation('cargos', new Collection([new Cargo(['slug' => $slug])]));

        return (new Service())->tipo($user);
    }
}
