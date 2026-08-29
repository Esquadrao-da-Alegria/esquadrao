<?php

namespace App\Services\Dashboard\Visita\Hospital;

use App\Models\Ala;
use App\Models\Cidade;
use App\Models\Hospital;
use App\Models\User;
use App\Queries\Dashboard\Visita\Hospital\Queries;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class Service
{
    public function __construct(private Queries $queries) {}

    public function index(User $user, array $filtros): array
    {
        $filtros = $this->aplicarEscopo($user, $filtros);
        $dados = $this->queries->index($filtros);
        $indicadores = $dados['indicadores'];
        $totalVisitas = (int) $indicadores->total_visitas;

        $dados['indicadores'] = [
            'total_visitas' => $totalVisitas,
            'hospitais_visitados' => (int) $indicadores->hospitais_visitados,
            'total_participacoes' => (int) $indicadores->total_participacoes,
            'media_participantes' => $totalVisitas > 0
                ? round((int) $indicadores->total_participacoes / $totalVisitas, 1)
                : 0,
            'impacto_estimado' => round((float) $indicadores->impacto_estimado),
            'visitas_com_impacto' => (int) $indicadores->visitas_com_impacto,
            'visitas_sem_impacto' => $totalVisitas - (int) $indicadores->visitas_com_impacto,
        ];

        $totaisPorMes = $dados['evolucao']->pluck('total', 'mes');
        $dados['evolucao'] = collect(CarbonPeriod::create(
            Carbon::createFromFormat('Y-m', $filtros['mes_inicio'])->startOfMonth(),
            '1 month',
            Carbon::createFromFormat('Y-m', $filtros['mes_fim'])->startOfMonth(),
        ))->map(fn (Carbon $mes) => [
            'mes' => $mes->format('Y-m'),
            'rotulo' => $mes->translatedFormat('M/Y'),
            'total' => (int) ($totaisPorMes[$mes->format('Y-m')] ?? 0),
        ])->values();

        $dados['hospitais'] = $dados['hospitais']->map(fn ($hospital) => [
            'id' => (int) $hospital->id,
            'nome' => $hospital->nome,
            'cidade' => $hospital->cidade,
            'total_visitas' => (int) $hospital->total_visitas,
            'total_participacoes' => (int) $hospital->total_participacoes,
            'media_participantes' => (int) $hospital->total_visitas > 0
                ? round((int) $hospital->total_participacoes / (int) $hospital->total_visitas, 1)
                : 0,
            'impacto_estimado' => round((float) $hospital->impacto_estimado),
            'possui_alas' => (bool) $hospital->possui_alas,
        ]);

        if ($dados['detalhes']) {
            $dados['detalhes']['visitas']->through(fn ($visita) => [
                'id' => (int) $visita->id,
                'inicio_em' => $visita->inicio_em,
                'status' => $visita->status,
                'ala' => $visita->ala ?? 'Sem ala informada',
                'participantes' => (int) $visita->participantes,
                'impacto_estimado' => $visita->impacto_estimado !== null
                    ? round((float) $visita->impacto_estimado)
                    : null,
            ]);
        }

        return [
            ...$dados,
            'filtros' => $filtros,
            'opcoes' => $this->opcoes($user, $filtros),
            'escopo_global' => $this->possuiEscopoGlobal($user),
        ];
    }

    private function aplicarEscopo(User $user, array $filtros): array
    {
        resolverUsuario($user);

        $filtros = [
            'mes_inicio' => $filtros['mes_inicio'],
            'mes_fim' => $filtros['mes_fim'],
            'cidade_id' => isset($filtros['cidade_id']) ? (int) $filtros['cidade_id'] : null,
            'visao_global' => (bool) ($filtros['visao_global'] ?? false),
            'hospital_id' => isset($filtros['hospital_id']) ? (int) $filtros['hospital_id'] : null,
            'ala_id' => isset($filtros['ala_id']) ? (int) $filtros['ala_id'] : null,
        ];

        if ($this->possuiEscopoGlobal($user)) {
            if (! $filtros['visao_global'] && ! $filtros['cidade_id'] && $user->voluntario?->cidade_base_id) {
                $filtros['cidade_id'] = (int) $user->voluntario->cidade_base_id;
            }

            return $filtros;
        }

        $cidadeId = (int) $user->voluntario->cidade_base_id;

        if ($filtros['cidade_id'] && $filtros['cidade_id'] !== $cidadeId) {
            abort(403);
        }

        $filtros['cidade_id'] = $cidadeId;
        $filtros['visao_global'] = false;

        if ($filtros['hospital_id'] && ! Hospital::query()->whereKey($filtros['hospital_id'])->where('cidade_id', $cidadeId)->exists()) {
            abort(403);
        }

        return $filtros;
    }

    private function opcoes(User $user, array $filtros): array
    {
        $cidades = $this->possuiEscopoGlobal($user)
            ? Cidade::query()->orderBy('nome')->get(['id', 'nome'])
            : Cidade::query()->whereKey($filtros['cidade_id'])->get(['id', 'nome']);

        $hospitais = $filtros['cidade_id']
            ? Hospital::query()->where('cidade_id', $filtros['cidade_id'])->orderBy('nome')->get(['id', 'nome'])
            : collect();

        $alas = $filtros['hospital_id']
            ? Ala::query()->where('hospital_id', $filtros['hospital_id'])->orderBy('nome')->get(['id', 'nome'])
            : collect();

        return compact('cidades', 'hospitais', 'alas');
    }

    private function possuiEscopoGlobal(User $user): bool
    {
        resolverUsuario($user);

        return $user->cargos->contains(fn ($cargo) => in_array($cargo->slug, ['administrador', 'coordenador_geral'], true));
    }
}
