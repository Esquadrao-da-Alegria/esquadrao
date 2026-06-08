<?php

namespace Database\Factories;

use App\Models\Estado;
use Illuminate\Database\Eloquent\Factories\Factory;

class CidadeFactory extends Factory
{
    public function definition(): array
    {
        $estado = Estado::inRandomOrder()->first() ?? Estado::factory()->create();

        return [
            'nome' => $this->faker->city(),
            'estado_id' => $estado->id,
        ];
    }
}
