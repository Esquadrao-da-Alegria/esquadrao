<?php

namespace Database\Factories;

use App\Enums\TipoEvento;
use App\Enums\StatusEvento;
use App\Models\Cidade;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventoFactory extends Factory
{
    public function definition(): array
    {
        $faker = FakerFactory::create();
        $cidade = Cidade::inRandomOrder()->first() ?? Cidade::factory()->create();
        $user = User::inRandomOrder()->first() ?? User::factory()->create();

        $start = now()->addDays($faker->numberBetween(1, 30))->setTime(9, 0, 0);
        $end = (clone $start)->addHours($faker->numberBetween(1, 4));

        return [
            'tipo' => $faker->randomElement([TipoEvento::OFICINA->value, TipoEvento::REUNIAO->value]),
            'titulo' => $faker->sentence(3),
            'descricao' => $faker->sentence(6),
            'data_inicio' => $start,
            'data_fim' => $end,
            'complemento' => null,
            'local_latitude' => $faker->latitude(-33.7, -1.0),
            'local_longitude' => $faker->longitude(-73.9, -34.8),
            'cidade_id' => $cidade->id,
            'status' => StatusEvento::AGENDADO->value ?? 'AGENDADO',
            'limite_vagas' => 100,
            'feedback_habilitado' => false,
            'criado_por_id' => $user->id,
        ];
    }
}
