<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HospitalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $lista = [
            [
                'cidade_id' => 4314902,
                'nome' => 'Hospital São Lucas',
                'cnpj' => '12345678000190',
                'endereco' => 'Rua das Flores, 150 - Centro',
                'telefone' => '(11)3456-7890',
                'email' => 'contato@saolucas.com.br',
                'ativo' => true,
                'observacoes' => 'Referência em atendimento de emergência.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cidade_id' => 4314902,
                'nome' => 'Hospital Vida e Saúde',
                'cnpj' => '98765432000145',
                'endereco' => 'Avenida Brasil, 2000 - Bairro Novo',
                'telefone' => '(21)4002-8922',
                'email' => 'atendimento@vidaesaude.com.br',
                'ativo' => true,
                'observacoes' => 'Hospital com pronto-socorro 24 horas.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cidade_id' => 4309407,
                'nome' => 'Hospital Regional de Santa Maria',
                'cnpj' => '45678912000133',
                'endereco' => 'Rodovia BR-040, Km 10 - Zona Rural',
                'telefone' => '(31)3222-5566',
                'email' => 'regional@santamaria.gov.br',
                'ativo' => true,
                'observacoes' => 'Atende pacientes do SUS e convênios.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cidade_id' => 4309407,
                'nome' => 'Hospital Nossa Senhora Aparecida',
                'cnpj' => '11223344000100',
                'endereco' => 'Rua Padre Anchieta, 45 - Centro',
                'telefone' => '(41)3344-7788',
                'email' => 'adm@nossasenhora.com.br',
                'ativo' => false,
                'observacoes' => 'Atendimento suspenso temporariamente para reformas.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'cidade_id' => 4309407,
                'nome' => 'Hospital Universitário São Bento',
                'cnpj' => '99887766000155',
                'endereco' => 'Av. Universitária, 1000 - Campus Norte',
                'telefone' => '(85)3090-4455',
                'email' => 'hu@saobento.edu.br',
                'ativo' => true,
                'observacoes' => 'Hospital-escola vinculado à Universidade São Bento.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('hospitais')->insert($lista);
    }
}
