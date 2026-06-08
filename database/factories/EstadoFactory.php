<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EstadoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => 'Rio Grande do Sul',
            'sigla' => 'RS',
        ];
    }
}
