<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    private const EMAIL_ADMINISTRADOR = 'esquadraodaalegria.dados@gmail.com';

    private const CIDADE_BASE_PORTO_ALEGRE_ID = 4314902;

    public function run(): void
    {
        $cargoAdministrador = Cargo::query()
            ->where('slug', 'administrador')
            ->firstOrFail();

        $cidadeBaseId = DB::table('cidades')
            ->where('id', self::CIDADE_BASE_PORTO_ALEGRE_ID)
            ->exists()
            ? self::CIDADE_BASE_PORTO_ALEGRE_ID
            : null;

        $voluntario = Voluntario::query()->updateOrCreate(
            ['email' => self::EMAIL_ADMINISTRADOR],
            [
                'nome_completo' => 'Administrador',
                'cidade_base_id' => $cidadeBaseId,
                'status' => User::STATUS_ATIVO,
            ],
        );

        $user = User::query()->updateOrCreate(
            ['email' => self::EMAIL_ADMINISTRADOR],
            [
                'voluntario_id' => $voluntario->id,
                'name' => 'Administrador',
                'password' => 'esquadrao123',
                'status' => User::STATUS_ATIVO,
            ],
        );

        $user->cargos()->sync([$cargoAdministrador->id]);
    }
}
