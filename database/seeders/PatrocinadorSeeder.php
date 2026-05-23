<?php

namespace Database\Seeders;

use App\Models\Patrocinador;
use Illuminate\Database\Seeder;

class PatrocinadorSeeder extends Seeder
{
    public function run(): void
    {
        $lista = [
            [
                'nome'            => 'Nota Fiscal Gaúcha',
                'site'            => 'https://nfg.sefaz.rs.gov.br/site/index.aspx',
                'categoria'       => 'diamante',
                'logo_path'       => '/assets/images/logo-nfg.jpg',
                'ativo'           => true,
                'ordem_exibicao'  => 1,
            ],
            [
                'nome'            => 'Sicredi',
                'site'            => 'https://www.sicredi.com.br/home/',
                'categoria'       => 'diamante',
                'logo_path'       => '/assets/images/logo-sicredi.jpg',
                'ativo'           => true,
                'ordem_exibicao'  => 2,
            ],
        ];

        foreach ($lista as $dados) {
            Patrocinador::create($dados);
        }
    }
}
