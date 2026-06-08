<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(
            [
                EstadoSeeder::class,
                CidadeSeeder::class,
                CargoSeeder::class,
                UserSeeder::class,
                HospitalSeeder::class,
                EventoSeeder::class,
            ]
        );
    }
}
