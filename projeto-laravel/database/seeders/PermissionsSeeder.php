<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {

        $permissions = [
            'create voluntarios',
            'read voluntarios',
            'update voluntarios',
            'delete voluntarios',

            'create hospitais',
            'read hospitais',
            'update hospitais',
            'delete hospitais',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // cria roles
        $diretor = Role::firstOrCreate(['name' => 'Diretor']);
        $coordenador_geral = Role::firstOrCreate(['name' => 'Coordenador Geral']);
        $coordenador_local = Role::firstOrCreate(['name' => 'Coordenador Local']);
        $artista = Role::firstOrCreate(['name' => 'Artista']);
        $psicologia = Role::firstOrCreate(['name' => 'Psicologia']);
        $apoio = Role::firstOrCreate(['name' => 'Apoio']);
        $voluntario = Role::firstOrCreate(['name' => 'Voluntário']);

        $diretor->syncPermissions($permissions);

        $coordenador->syncPermissions([
            'read voluntarios',
            'update voluntarios',

            'read hospitais',
            'update hospitais',
        ]);

        $voluntario->syncPermissions([
            'read voluntarios',
            'read hospitais',
        ]);
    }
}
