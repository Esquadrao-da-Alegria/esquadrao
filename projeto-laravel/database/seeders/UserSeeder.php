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
        //cria roles
        $diretor_role = Role::firstOrCreate([
            'name' => 'diretor',
            'guard_name' => 'web',
            'label' => 'Diretor',
        ]);
        $admin_role = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
            'label' => 'Admin',
        ]);
        $coordenador_geral_role = Role::firstOrCreate([
            'name' => 'coordenador_geral',
            'guard_name' => 'web',
            'label' => 'Coordenador Geral',
        ]);
        $coordenador_local_role = Role::firstOrCreate([
            'name' => 'coordenador_local',
            'guard_name' => 'web',
            'label' => 'Coordenador Local',
        ]);
        $voluntario_role = Role::firstOrCreate([
            'name' => 'voluntario',
            'guard_name' => 'web',
            'label' => 'Voluntário',
        ]);
        $psicologia_role = Role::firstOrCreate([
            'name' => 'psicologia',
            'guard_name' => 'web',
            'label' => 'Psicologia',
        ]);
        // cria usuarios
        $diretor = User::firstOrCreate([
            'name' => 'Diretor',
            'email' => 'diretor@email.com',
            'password' => Hash::make('diretor'),

        ]);

        $admin = User::firstOrCreate([
            'name' => 'T.I. Esquadrão',
            'email' => 'admin@email.com',
            'password' => Hash::make('admin'),

        ]);

        $coordenador_geral = User::firstOrCreate([
            'name' => 'Coordenador Geral',
            'email' => 'coordenador_geral@email.com',
            'password' => Hash::make('coordenador_geral'),

        ]);

        $coordenador_local = User::firstOrCreate([
            'name' => 'Coordenador Local',
            'email' => 'coordenador_local@email.com',
            'password' => Hash::make('coordenador_local'),

        ]);

        $voluntario = User::firstOrCreate([
            'name' => 'Voluntário',
            'email' => 'voluntario@email.com',
            'password' => Hash::make('voluntario'),

        ]);

        $psicologia = User::firstOrCreate([
            'name' => 'Psicologia',
            'email' => 'psicologia@email.com',
            'password' => Hash::make('psicologia'),

        ]);
    //atribui roles
        $diretor->assignRole($diretor_role);
        $admin->assignRole($admin_role);
        $coordenador_geral->assignRole($coordenador_geral_role);
        $coordenador_local->assignRole($coordenador_local_role);
        $voluntario->assignRole($voluntario_role);
        $psicologia->assignRole($psicologia_role);

    }
}