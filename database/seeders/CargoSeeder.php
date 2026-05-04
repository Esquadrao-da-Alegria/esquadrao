<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CargoSeeder extends Seeder
{
    /**
     * Cargos fixos do sistema (slug estável para código e políticas).
     */
    public const CARGOS = [
        ['nome' => 'Administrador', 'slug' => 'administrador'],
        ['nome' => 'Diretor', 'slug' => 'diretor'],
        ['nome' => 'Coordenador Geral', 'slug' => 'coordenador_geral'],
        ['nome' => 'Coordenador Local', 'slug' => 'coordenador_local'],
        ['nome' => 'Artista', 'slug' => 'artista'],
        ['nome' => 'Psicologia', 'slug' => 'psicologia'],
        ['nome' => 'Apoio', 'slug' => 'apoio'],
        ['nome' => 'Voluntário', 'slug' => 'voluntario'],
    ];

    public function run(): void
    {
        $now = now();

        $linhas = collect(self::CARGOS)->map(fn (array $cargo) => [
            'nome' => $cargo['nome'],
            'slug' => $cargo['slug'],
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        DB::table('cargos')->upsert(
            $linhas,
            ['slug'],
            ['nome', 'updated_at']
        );
    }
}
