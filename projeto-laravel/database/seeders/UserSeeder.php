<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::firstOrCreate([
            'name' => 'Diretor',
            'guard_name' => 'web'
        ]);

        $user = User::updateOrCreate(
            ['email' => 'teste@email.com'],
            [
                'name' => 'Esquadrão Admin',
                'password' => Hash::make('12345678'),
                'cargo' => 'Diretor',
                'active' => true,
                'profile_visibility' => 'public',
            ]
        );

    $user->assignRole($role);
    }
}