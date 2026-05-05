<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $cargoAdministrador = Cargo::query()
            ->where('slug', 'administrador')
            ->firstOrFail();

        $user = User::query()->updateOrCreate(
            ['email' => 'esquadraodaalegria.dados@gmail.com'],
            [
                'name' => 'Administrador',
                'password' => 'esquadrao123',
            ],
        );

        $user->cargos()->sync([$cargoAdministrador->id]);
    }
}
