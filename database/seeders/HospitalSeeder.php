<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HospitalSeeder extends Seeder
{
    public function run(): void
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk('public');

        $now = now();

        $lista = [

            // ---------------------------------------------------------
            // PORTO ALEGRE — cidade_id: 4314902
            // ---------------------------------------------------------

            [
                'cidade_id' => 4314902,
                'nome' => 'Hospital Santa Clara - Complexo Santa Casa',
                'cnpj' => '11111111000199',
                'endereco' => 'Rua Professor Annes Dias, 135 - Centro Histórico, Porto Alegre - RS',
                'telefone' => '(51) 3333-0001',
                'email' => 'contato@santaclara.com.br',
                'ativo' => true,
                'url_foto' => $storage->url('imagens/hospitais/hospital-santa-clara.png'),
                'observacoes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cidade_id' => 4314902,
                'nome' => 'Hospital Ernesto Dornelles',
                'cnpj' => '22222222000199',
                'endereco' => 'Av. Ipiranga, 1801 - Azenha, Porto Alegre - RS',
                'telefone' => '(51) 3333-0002',
                'email' => 'contato@ernestodornelles.com.br',
                'ativo' => true,
                'url_foto' => $storage->url('imagens/hospitais/hospital-ernesto-dornelli.png'),
                'observacoes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cidade_id' => 4314902,
                'nome' => 'Instituto de Cardiologia',
                'cnpj' => '33333333000199',
                'endereco' => 'Av. Princesa Isabel, 395 - Santana, Porto Alegre - RS',
                'telefone' => '(51) 3333-0003',
                'email' => 'contato@cardiologia.com.br',
                'ativo' => true,
                'url_foto' => $storage->url('imagens/hospitais/instituto-de-cardiologia.png'),
                'observacoes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cidade_id' => 4314902,
                'nome' => 'Hospital da Brigada Militar',
                'cnpj' => '55555555000199',
                'endereco' => 'R. Dr. Castro de Menezes, 155 - Vila Assunção, Porto Alegre - RS',
                'telefone' => '(51) 3333-0005',
                'email' => 'contato@bm.rs.gov.br',
                'ativo' => true,
                'url_foto' => $storage->url('imagens/hospitais/brigada-militar.png'),
                'observacoes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ---------------------------------------------------------
            // CANOAS — cidade_id: 4304606
            // ---------------------------------------------------------

            [
                'cidade_id' => 4304606,
                'nome' => 'Ulbra Canoas',
                'cnpj' => '44444444000199',
                'endereco' => 'Av. Farroupilha, 8001 - São José, Canoas - RS',
                'telefone' => '(51) 3333-1001',
                'email' => 'contato@ulbra.br',
                'ativo' => true,
                'url_foto' => $storage->url('imagens/hospitais/ulbra-canoas.png'),
                'observacoes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ---------------------------------------------------------
            // SÃO LEOPOLDO — cidade_id: 4318705
            // ---------------------------------------------------------

            [
                'cidade_id' => 4318705,
                'nome' => 'Hospital Centenário',
                'cnpj' => '66666666000199',
                'endereco' => 'Av. Theodomiro Porto da Fonseca, 799 - Fião, São Leopoldo - RS',
                'telefone' => '(51) 3333-2001',
                'email' => 'contato@centenario.com.br',
                'ativo' => true,
                'url_foto' => $storage->url('imagens/hospitais/hospital_centenario.png'),
                'observacoes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ---------------------------------------------------------
            // SANTA MARIA — cidade_id: 4316907
            // ---------------------------------------------------------

            [
                'cidade_id' => 4316907,
                'nome' => 'Hospital Geral de Santa Maria (HGSM)',
                'cnpj' => '77777777000199',
                'endereco' => "R. Mal. Hermes, 190 - Passo D'areia, Santa Maria - RS",
                'telefone' => '(55) 3222-0001',
                'email' => 'contato@hgsm.com.br',
                'ativo' => true,
                'url_foto' => $storage->url('imagens/hospitais/hospital-geral-santa-maria.png'),
                'observacoes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cidade_id' => 4316907,
                'nome' => 'Unidade de Pronto Atendimento 24HR',
                'cnpj' => '88888888000199',
                'endereco' => 'R. Venâncio Aires, 1078 - Centro, Santa Maria - RS',
                'telefone' => '(55) 3222-0002',
                'email' => 'contato@upa24h.com.br',
                'ativo' => true,
                'url_foto' => $storage->url('imagens/hospitais/unimed_24h.png'),
                'observacoes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cidade_id' => 4316907,
                'nome' => 'Hospital de São Francisco',
                'cnpj' => '99999999000199',
                'endereco' => 'R. Joana D Arc, 465 - Nossa Sra. de Lourdes, Santa Maria - RS',
                'telefone' => '(55) 3222-0003',
                'email' => 'contato@saofrancisco.com.br',
                'ativo' => true,
                'url_foto' => $storage->url('imagens/hospitais/hospital-sao-francisco-de-assis.png'),
                'observacoes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cidade_id' => 4316907,
                'nome' => 'Hospital Universitário de Santa Maria (HUSM)',
                'cnpj' => '10101010000199',
                'endereco' => 'Av. Roraima, 1000 Prédio 22 - Camobi, Santa Maria - RS',
                'telefone' => '(55) 3222-0004',
                'email' => 'contato@husm.ufsm.br',
                'ativo' => true,
                'url_foto' => $storage->url('imagens/hospitais/hospital-universitario.png'),
                'observacoes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cidade_id' => 4316907,
                'nome' => 'Hospital Casa de Saúde',
                'cnpj' => '20202020000199',
                'endereco' => 'R. Gen. Neto, 477 - Nossa Sra. de Lourdes, Santa Maria - RS',
                'telefone' => '(55) 3222-0005',
                'email' => 'contato@casadesaude.com.br',
                'ativo' => true,
                'url_foto' => $storage->url('imagens/hospitais/hospital-casa-de-saude.png'),
                'observacoes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cidade_id' => 4316907,
                'nome' => 'Hospital Regional de Santa Maria',
                'cnpj' => '30303030000199',
                'endereco' => 'R. Florianópolis, 1041 - Pinheiro Machado, Santa Maria - RS',
                'telefone' => '(55) 3222-0006',
                'email' => 'contato@regional-sm.com.br',
                'ativo' => true,
                'url_foto' => $storage->url('imagens/hospitais/hospital_regional.png'),
                'observacoes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cidade_id' => 4316907,
                'nome' => 'Hospital da Brigada Militar de Santa Maria',
                'cnpj' => '40404040000199',
                'endereco' => 'R. Euclídes da Cunha, 1800 - Pres. Joao Goulart, Santa Maria - RS',
                'telefone' => '(55) 3222-0007',
                'email' => 'contato@bm.sm.gov.br',
                'ativo' => true,
                'url_foto' => $storage->url('imagens/hospitais/hospital-da-brigada-sm.png'),
                'observacoes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cidade_id' => 4316907,
                'nome' => 'Hospital de Caridade Alcides Brum',
                'cnpj' => '50505050000199',
                'endereco' => 'R. Floriano Peixoto, 1745 - Centro, Santa Maria - RS',
                'telefone' => '(55) 3222-0008',
                'email' => 'contato@alcidesbrum.com.br',
                'ativo' => true,
                'url_foto' => $storage->url('imagens/hospitais/hospital-de-caridade.png'),
                'observacoes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cidade_id' => 4316907,
                'nome' => 'UPA 24h Santa Maria',
                'cnpj' => '60606060000199',
                'endereco' => 'R. Ari Lagranha Domingues, 188 - Santa Maria - RS',
                'telefone' => '(55) 3222-0009',
                'email' => 'contato@upa-sm.com.br',
                'ativo' => true,
                'url_foto' => $storage->url('imagens/hospitais/upa-sm.png'),
                'observacoes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ---------------------------------------------------------
            // PELOTAS — cidade_id: 4314407
            // ---------------------------------------------------------

            [
                'cidade_id' => 4314407,
                'nome' => 'Hospital Universitário São Francisco de Paula',
                'cnpj' => '70707070000199',
                'endereco' => 'R. Mal. Deodoro, 1123 - Centro, Pelotas - RS',
                'telefone' => '(53) 3222-0001',
                'email' => 'contato@sfp.com.br',
                'ativo' => true,
                'url_foto' => $storage->url('imagens/hospitais/hospital-sao-francisco-de-paula.png'),
                'observacoes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'cidade_id' => 4314407,
                'nome' => 'Hospital Escola da UFPel',
                'cnpj' => '80808080000199',
                'endereco' => 'R. Prof. Dr. Araújo, 538 - Centro, Pelotas - RS',
                'telefone' => '(53) 3222-0002',
                'email' => 'contato@ufpel.edu.br',
                'ativo' => true,
                'url_foto' => $storage->url('imagens/hospitais/hospital-escola-ufpel.png'),
                'observacoes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

        ];

        DB::table('hospitais')->insert($lista);
    }
}
