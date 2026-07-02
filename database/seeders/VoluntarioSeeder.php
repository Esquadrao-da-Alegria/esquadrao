<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Database\Seeder;

class VoluntarioSeeder extends Seeder
{
    private const PREFIXO_TESTE = 'TESTE - ';

    public function run(): void
    {
        $cidadeBaseId = 4314902;

        $cargos = Cargo::query()
            ->whereIn('slug', [
                'artista',
                'psicologia',
                'apoio',
                'coordenador_local',
                'voluntario',
            ])
            ->get()
            ->keyBy('slug');

        $lista = [
            [
                'nome_completo' => 'Carla Mendes',
                'nome_doutor' => 'Dra. Risadinha',
                'email' => 'carla.artista@esquadrao.test',
                'telefone' => '(51) 99876-5432',
                'data_nascimento' => '1992-03-15',
                'cpf' => '111.111.111-01',
                'data_entrada_ong' => '2022-01-10',
                'observacoes' => 'Artista principal das visitas hospitalares aos sábados.',
                'cargo_slug' => 'artista',
            ],
            [
                'nome_completo' => 'Rafael Souza',
                'nome_doutor' => null,
                'email' => 'rafael.psicologia@esquadrao.test',
                'telefone' => '(51) 99765-4321',
                'data_nascimento' => '1988-07-22',
                'cpf' => '222.222.222-02',
                'data_entrada_ong' => '2021-06-01',
                'observacoes' => 'Acompanhamento emocional de famílias e voluntários.',
                'cargo_slug' => 'psicologia',
            ],
            [
                'nome_completo' => 'Fernanda Lima',
                'nome_doutor' => null,
                'email' => 'fernanda.apoio@esquadrao.test',
                'telefone' => '(51) 99654-3210',
                'data_nascimento' => '1995-11-08',
                'cpf' => '333.333.333-03',
                'data_entrada_ong' => '2023-02-20',
                'observacoes' => 'Apoio logístico e organização de materiais das visitas.',
                'cargo_slug' => 'apoio',
            ],
            [
                'nome_completo' => 'Bruno Alves',
                'nome_doutor' => null,
                'email' => 'bruno.coordenador@esquadrao.test',
                'telefone' => '(51) 99543-2109',
                'data_nascimento' => '1985-04-30',
                'cpf' => '444.444.444-04',
                'data_entrada_ong' => '2019-09-12',
                'observacoes' => 'Coordena a equipe local de Porto Alegre.',
                'cargo_slug' => 'coordenador_local',
            ],
            [
                'nome_completo' => 'Juliana Costa',
                'nome_doutor' => 'Dra. Alegria',
                'email' => 'juliana.voluntaria@esquadrao.test',
                'telefone' => '(51) 99432-1098',
                'data_nascimento' => '1998-12-05',
                'cpf' => '555.555.555-05',
                'data_entrada_ong' => '2024-08-01',
                'observacoes' => 'Voluntária recém-integrada à equipe.',
                'cargo_slug' => 'voluntario',
            ],
        ];

        foreach ($lista as $dados) {
            $cargo = $cargos->get($dados['cargo_slug']);

            if (! $cargo) {
                continue;
            }

            $voluntario = Voluntario::query()->updateOrCreate(
                ['email' => $dados['email']],
                [
                    'nome_completo' => self::PREFIXO_TESTE.$dados['nome_completo'],
                    'nome_doutor' => $dados['nome_doutor']
                        ? self::PREFIXO_TESTE.$dados['nome_doutor']
                        : null,
                    'telefone' => $dados['telefone'],
                    'data_nascimento' => $dados['data_nascimento'],
                    'cpf' => $dados['cpf'],
                    'cidade_base_id' => $cidadeBaseId,
                    'data_entrada_ong' => $dados['data_entrada_ong'],
                    'status' => User::STATUS_ATIVO,
                    'observacoes' => $dados['observacoes'],
                ],
            );

            $usuario = User::query()->updateOrCreate(
                ['email' => $dados['email']],
                [
                    'voluntario_id' => $voluntario->id,
                    'name' => self::PREFIXO_TESTE.$dados['nome_completo'],
                    'password' => 'esquadrao123',
                    'status' => User::STATUS_ATIVO,
                ],
            );

            $usuario->cargos()->sync([$cargo->id]);
        }
    }
}
