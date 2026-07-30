<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CidadeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cidades')->whereNotIn('nome', ['Porto Alegre', 'Santa Maria', 'Pelotas'])->delete();

        DB::table('cidades')->updateOrInsert(
            ['id' => 4314902],
            ['nome' => 'Porto Alegre', 'estado_id' => 43, 'updated_at' => now(), 'created_at' => now()]
        );
        DB::table('cidades')->updateOrInsert(
            ['id' => 4316907],
            ['nome' => 'Santa Maria',  'estado_id' => 43, 'updated_at' => now(), 'created_at' => now()]
        );
        DB::table('cidades')->updateOrInsert(
            ['id' => 4314407],
            ['nome' => 'Pelotas',      'estado_id' => 43, 'updated_at' => now(), 'created_at' => now()]
        );
    }
}