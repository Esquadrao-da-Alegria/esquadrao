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
                VoluntarioSeeder::class,
                HospitalSeeder::class,
                PatrocinadorSeeder::class,
                VisitaSeeder::class,
                EventoSeeder::class,
            ]
        );
    }
}
