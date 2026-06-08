<?php

namespace Database\Seeders;

use App\Models\Evento;
use Illuminate\Database\Seeder;

class EventoSeeder extends Seeder
{
    /**
     * Seed the application's events.
     */
    public function run(): void
    {
        Evento::factory()->count(10)->create();
    }
}
