<?php

namespace Database\Seeders;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Cargo;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaParticipante;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class VisitaSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()
            ->where('email', 'esquadraodaalegria.dados@gmail.com')
            ->firstOrFail();

        $cargoVoluntario = Cargo::query()
            ->where('slug', 'voluntario')
            ->firstOrFail();

        $voluntarios = $this->criarVoluntarios($cargoVoluntario);

        $hospitais = Hospital::query()
            ->where('ativo', true)
            ->orderBy('id')
            ->get();

        if ($hospitais->isEmpty()) {
            return;
        }

        $lista = $this->montarCalendarioDoMes($admin, $voluntarios, $hospitais);

        foreach ($lista as $dados) {
            $participantes = $dados['participantes'];
            unset($dados['participantes']);

            $visita = Visita::query()->create($dados);

            foreach ($participantes as $participante) {
                VisitaParticipante::query()->create([
                    'visita_id' => $visita->id,
                    ...$participante,
                ]);
            }
        }
    }

    /** @return list<User> */
    private function criarVoluntarios(Cargo $cargoVoluntario): array
    {
        $lista = [
            ['name' => 'Maria Palhaça',   'email' => 'maria.palhaca@esquadrao.test'],
            ['name' => 'João Risadinha',  'email' => 'joao.risadinha@esquadrao.test'],
            ['name' => 'Ana Alegria',     'email' => 'ana.alegria@esquadrao.test'],
            ['name' => 'Pedro Sorriso',   'email' => 'pedro.sorriso@esquadrao.test'],
            ['name' => 'Luiza Fantasia',  'email' => 'luiza.fantasia@esquadrao.test'],
        ];

        $voluntarios = [];

        foreach ($lista as $dados) {
            $voluntario = User::query()->updateOrCreate(
                ['email' => $dados['email']],
                [
                    'name'     => $dados['name'],
                    'password' => 'esquadrao123',
                ],
            );

            $voluntario->cargos()->syncWithoutDetaching([$cargoVoluntario->id]);
            $voluntarios[] = $voluntario;
        }

        return $voluntarios;
    }

    /**
     * @param  list<User>  $voluntarios
     * @return list<array<string, mixed>>
     */
    private function montarCalendarioDoMes(User $admin, array $voluntarios, Collection $hospitais): array
    {
        $inicioDoMes = now()->startOfMonth();
        $fimDoMes    = now()->endOfMonth();
        $visitas     = [];
        $indiceHospital = 0;

        $proximoHospital = function () use ($hospitais, &$indiceHospital): Hospital {
            $hospital = $hospitais[$indiceHospital % $hospitais->count()];
            $indiceHospital++;

            return $hospital;
        };

        for ($data = $inicioDoMes->copy(); $data->lte($fimDoMes); $data->addDay()) {
            if (! $data->isSaturday()) {
                continue;
            }

            $hospital = $proximoHospital();
            $inicio   = $data->copy()->setTime(9, 0);
            $fim      = $data->copy()->setTime(11, 30);

            $visitas[] = $this->montarVisita(
                admin: $admin,
                hospital: $hospital,
                inicio: $inicio,
                fim: $fim,
                tipo: VisitaTipo::Hospital,
                status: $this->statusPorData($inicio),
                observacoes: "Teste - Visita semanal — {$hospital->nome}",
                voluntarios: $voluntarios,
                indiceParticipantes: count($visitas),
            );
        }

        $eventosFixos = [
            [
                'dia'        => 5,
                'hora_inicio' => 19,
                'hora_fim'    => 21,
                'tipo'       => VisitaTipo::Reuniao,
                'observacoes' => 'Reunião mensal de coordenação da equipe.',
            ],
            [
                'dia'        => 10,
                'hora_inicio' => 14,
                'hora_fim'    => 16,
                'tipo'       => VisitaTipo::Oficina,
                'observacoes' => 'Oficina de maquiagem e improviso teatral.',
            ],
            [
                'dia'        => 18,
                'hora_inicio' => 10,
                'hora_fim'    => 12,
                'tipo'       => VisitaTipo::Residencia,
                'observacoes' => 'Visita domiciliar vinculada ao hospital de referência.',
            ],
            [
                'dia'        => 22,
                'hora_inicio' => 15,
                'hora_fim'    => 17,
                'tipo'       => VisitaTipo::AcaoEspecial,
                'status'     => VisitaStatus::Cancelada,
                'observacoes' => 'Ação especial cancelada por indisponibilidade do local.',
            ],
            [
                'dia'        => 28,
                'hora_inicio' => 9,
                'hora_fim'    => 12,
                'tipo'       => VisitaTipo::AcaoEspecial,
                'observacoes' => 'Dia das crianças — ação especial no hospital.',
            ],
        ];

        foreach ($eventosFixos as $evento) {
            $inicio = $inicioDoMes->copy()->day($evento['dia'])->setTime($evento['hora_inicio'], 0);
            $fim    = $inicioDoMes->copy()->day($evento['dia'])->setTime($evento['hora_fim'], 0);

            if ($inicio->gt($fimDoMes)) {
                continue;
            }

            $visitas[] = $this->montarVisita(
                admin: $admin,
                hospital: $proximoHospital(),
                inicio: $inicio,
                fim: $fim,
                tipo: $evento['tipo'],
                status: $evento['status'] ?? $this->statusPorData($inicio),
                observacoes: $evento['observacoes'],
                voluntarios: $voluntarios,
                indiceParticipantes: count($visitas),
            );
        }

        usort($visitas, fn (array $a, array $b) => $a['inicio_em'] <=> $b['inicio_em']);

        return $visitas;
    }

    /**
     * @param  list<User>  $voluntarios
     * @return array<string, mixed>
     */
    private function montarVisita(
        User $admin,
        Hospital $hospital,
        Carbon $inicio,
        Carbon $fim,
        VisitaTipo $tipo,
        VisitaStatus $status,
        string $observacoes,
        array $voluntarios,
        int $indiceParticipantes,
    ): array {
        $lider = $voluntarios[$indiceParticipantes % count($voluntarios)];

        return [
            'hospital_id'     => $hospital->id,
            'criado_por_id'   => $admin->id,
            'lider_id'        => $lider->id,
            'inicio_em'       => $inicio,
            'fim_em'          => $fim,
            'tipo'            => $tipo,
            'status'          => $status,
            'origem'          => VisitaOrigem::Sistema,
            'observacoes'      => $observacoes,
            'participantes'   => $this->montarParticipantes($voluntarios, $indiceParticipantes, $status),
        ];
    }

    /**
     * @param  list<User>  $voluntarios
     * @return list<array<string, mixed>>
     */
    private function montarParticipantes(array $voluntarios, int $indiceBase, VisitaStatus $statusVisita): array
    {
        $quantidade = min(4, count($voluntarios));
        $participantes = [];

        for ($i = 0; $i < $quantidade; $i++) {
            $voluntario = $voluntarios[($indiceBase + $i) % count($voluntarios)];

            $participantes[] = [
                'voluntario_id'        => $voluntario->id,
                'tipo_participacao'    => $i % 2 === 0 ? TipoParticipacao::Palhaco : TipoParticipacao::Paisana,
                'papel_na_visita'      => $i === 0 ? PapelNaVisita::Relator : PapelNaVisita::Participante,
                'status_participacao'  => $this->statusParticipacaoPorVisita($statusVisita, $i),
            ];
        }

        return $participantes;
    }

    private function statusPorData(Carbon $data): VisitaStatus
    {
        if ($data->copy()->endOfDay()->isPast()) {
            return VisitaStatus::Realizada;
        }

        return VisitaStatus::Agendada;
    }

    private function statusParticipacaoPorVisita(VisitaStatus $statusVisita, int $indice): StatusParticipacao
    {
        if ($statusVisita === VisitaStatus::Cancelada) {
            return StatusParticipacao::Cancelado;
        }

        if ($this->visitaJaOcorreu($statusVisita)) {
            return $indice === 2 ? StatusParticipacao::Falta : StatusParticipacao::Confirmado;
        }

        return $indice === 1 ? StatusParticipacao::Pendente : StatusParticipacao::Confirmado;
    }

    private function visitaJaOcorreu(VisitaStatus $statusVisita): bool
    {
        return in_array($statusVisita, [
            VisitaStatus::Realizada,
            VisitaStatus::PendenteRelatorio,
            VisitaStatus::Contabilizada,
            VisitaStatus::NaoContabilizada,
        ], true);
    }
}
