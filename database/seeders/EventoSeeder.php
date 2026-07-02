<?php

namespace Database\Seeders;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EventoSeeder extends Seeder
{
    private const PREFIXO_TESTE = 'TESTE - ';

    public function run(): void
    {
        $admin = User::query()
            ->where('email', 'esquadraodaalegria.dados@gmail.com')
            ->firstOrFail();

        $responsavel = User::query()
            ->where('email', 'bruno.coordenador@esquadrao.test')
            ->first();

        $lista = [
            [
                'titulo' => 'Oficina de maquiagem e improviso',
                'tipo' => 'oficina',
                'descricao' => 'Capacitação prática para palhaços iniciantes com foco em maquiagem e cenas curtas de improviso.',
                'local' => 'Sede do Esquadrão — sala de treinamento',
                'data_inicio' => now()->addDays(7)->setTime(19, 0),
                'data_fim' => now()->addDays(7)->setTime(21, 30),
                'limite_participantes' => 15,
                'status' => 'agendado',
            ],
            [
                'titulo' => 'Reunião mensal de coordenação',
                'tipo' => 'reuniao',
                'descricao' => 'Alinhamento da equipe sobre visitas do mês, escalas e demandas dos hospitais parceiros.',
                'local' => 'Sede do Esquadrão — sala de reuniões',
                'data_inicio' => now()->addDays(3)->setTime(20, 0),
                'data_fim' => now()->addDays(3)->setTime(22, 0),
                'limite_participantes' => null,
                'status' => 'agendado',
            ],
            [
                'titulo' => 'Festival do Doutor Palhaça',
                'tipo' => 'evento',
                'descricao' => 'Evento aberto à comunidade com apresentações, brincadeiras e arrecadação simbólica para as visitas.',
                'local' => 'Parque da Redenção — área central',
                'data_inicio' => now()->addDays(21)->setTime(10, 0),
                'data_fim' => now()->addDays(21)->setTime(16, 0),
                'limite_participantes' => 50,
                'status' => 'agendado',
            ],
            [
                'titulo' => 'Workshop de storytelling hospitalar',
                'tipo' => 'oficina',
                'descricao' => 'Oficina sobre narrativas lúdicas e técnicas de interação com crianças em ambiente hospitalar.',
                'local' => 'Hospital de Clínicas — auditório',
                'data_inicio' => now()->addDays(14)->setTime(14, 0),
                'data_fim' => now()->addDays(14)->setTime(17, 0),
                'limite_participantes' => 20,
                'status' => 'agendado',
            ],
            [
                'titulo' => 'Encontro regional de voluntários',
                'tipo' => 'reuniao',
                'descricao' => 'Encontro entre equipes de diferentes cidades para troca de experiências.',
                'local' => 'Centro de eventos — Canoas',
                'data_inicio' => now()->subDays(10)->setTime(9, 0),
                'data_fim' => now()->subDays(10)->setTime(12, 0),
                'limite_participantes' => 30,
                'status' => 'cancelado',
                'motivo_cancelamento' => 'Baixa adesão confirmada com antecedência.',
                'cancelado_em' => now()->subDays(15),
                'cancelado_por_id' => $admin->id,
            ],
        ];

        foreach ($lista as $dados) {
            $tituloBase = $dados['titulo'];
            $titulo = self::PREFIXO_TESTE.$tituloBase;
            unset($dados['titulo']);

            Evento::query()->where('titulo', $tituloBase)->delete();

            Evento::query()->updateOrCreate(
                ['titulo' => $titulo],
                [
                    ...$dados,
                    'titulo' => $titulo,
                    'criado_por_id' => $admin->id,
                    'responsavel_id' => $responsavel?->id,
                    'data_inicio' => Carbon::parse($dados['data_inicio']),
                    'data_fim' => isset($dados['data_fim']) ? Carbon::parse($dados['data_fim']) : null,
                    'cancelado_em' => isset($dados['cancelado_em']) ? Carbon::parse($dados['cancelado_em']) : null,
                ],
            );
        }
    }
}
