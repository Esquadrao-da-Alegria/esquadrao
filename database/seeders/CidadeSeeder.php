<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CidadeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cidades')->insert([
            ['id' => 4314902, 'nome' => 'Porto Alegre', 'estado_id' => 43, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4316907, 'nome' => 'Santa Maria',  'estado_id' => 43, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4314407, 'nome' => 'Pelotas',      'estado_id' => 43, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}