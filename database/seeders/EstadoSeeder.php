<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $now = date("Y-m-d H:i:s");

        DB::table("estados")->insert(
            [
                [
                    "id"         => 11,
                    "nome"       => "Rondônia",
                    "sigla"      => "RO",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 12,
                    "nome"       => "Acre",
                    "sigla"      => "AC",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 13,
                    "nome"       => "Amazonas",
                    "sigla"      => "AM",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 14,
                    "nome"       => "Roraima",
                    "sigla"      => "RR",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 15,
                    "nome"       => "Pará",
                    "sigla"      => "PA",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 16,
                    "nome"       => "Amapá",
                    "sigla"      => "AP",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 17,
                    "nome"       => "Tocantins",
                    "sigla"      => "TO",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 21,
                    "nome"       => "Maranhão",
                    "sigla"      => "MA",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 22,
                    "nome"       => "Piauí",
                    "sigla"      => "PI",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 23,
                    "nome"       => "Ceará",
                    "sigla"      => "CE",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 24,
                    "nome"       => "Rio Grande do Norte",
                    "sigla"      => "RN",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 25,
                    "nome"       => "Paraíba",
                    "sigla"      => "PB",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 26,
                    "nome"       => "Pernambuco",
                    "sigla"      => "PE",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 27,
                    "nome"       => "Alagoas",
                    "sigla"      => "AL",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 28,
                    "nome"       => "Sergipe",
                    "sigla"      => "SE",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 29,
                    "nome"       => "Bahia",
                    "sigla"      => "BA",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 31,
                    "nome"       => "Minas Gerais",
                    "sigla"      => "MG",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 32,
                    "nome"       => "Espírito Santo",
                    "sigla"      => "ES",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 33,
                    "nome"       => "Rio de Janeiro",
                    "sigla"      => "RJ",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 35,
                    "nome"       => "São Paulo",
                    "sigla"      => "SP",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 41,
                    "nome"       => "Paraná",
                    "sigla"      => "PR",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 42,
                    "nome"       => "Santa Catarina",
                    "sigla"      => "SC",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 43,
                    "nome"       => "Rio Grande do Sul",
                    "sigla"      => "RS",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 50,
                    "nome"       => "Mato Grosso do Sul",
                    "sigla"      => "MS",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 51,
                    "nome"       => "Mato Grosso",
                    "sigla"      => "MT",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 52,
                    "nome"       => "Goiás",
                    "sigla"      => "GO",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
                [
                    "id"         => 53,
                    "nome"       => "Distrito Federal",
                    "sigla"      => "DF",
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
            ]
        );
    }
}
