<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'admin',
            'diretor', 
            'coordenador',
            'voluntario'
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['nomeRole' => $roleName]);
        }
    }
}