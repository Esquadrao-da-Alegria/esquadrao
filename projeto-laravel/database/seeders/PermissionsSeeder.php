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

            'create visitas',
            'read visitas',
            'update visitas',
            'delete visitas',

            'create patrocinadores',
            'read patrocinadores',
            'update patrocinadores',
            'delete patrocinadores',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // cria roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'label' => 'Admin']);
        $diretor = Role::firstOrCreate(['name' => 'diretor', 'label' => 'Diretor']);
        $coordenador_geral = Role::firstOrCreate(['name' => 'coordenador geral', 'label' => 'Coordenador Geral']);
        $coordenador_local = Role::firstOrCreate(['name' => 'coordenador_local', 'label' => 'Coordenador Local']);
        $artista = Role::firstOrCreate(['name' => 'artista', 'label' => 'Artista']);
        $psicologia = Role::firstOrCreate(['name' => 'psicologia', 'label' => 'Psicologia']);
        $apoio = Role::firstOrCreate(['name' => 'apoio', 'label' => 'Apoio']);
        $voluntario = Role::firstOrCreate(['name' => 'voluntario', 'label' => 'Voluntário']);

        $admin->syncPermissions($permissions);
        
        $diretor->syncPermissions($permissions);

        $coordenador_geral->syncPermissions([
            'read voluntarios',
            'update voluntarios',

            'read hospitais',
            'update hospitais',
        ]);

        $voluntario->syncPermissions([
            'read voluntarios',
            'read hospitais',
            'read visitas',
        ]);
    }
}
