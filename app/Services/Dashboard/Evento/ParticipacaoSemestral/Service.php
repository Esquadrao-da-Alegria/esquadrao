<?php

namespace App\Services\Dashboard\Evento\ParticipacaoSemestral;

use Carbon\Carbon;

// QUERIES
use App\Queries\Dashboard\Evento\ParticipacaoSemestral\Queries;

// UTILS
use App\Helpers\User as UserHelper;

// MODELS
use App\Models\Cidade;
use App\Models\User;

class Service
{
    public function __construct(private Queries $queries) {}

    public function index(User $gestor, array $filtros): array
    {
        $filtros = $this->normalizarFiltros($gestor, $filtros);

        return [
            'integrantes' => $this->queries->index($filtros),
            'filtros' => collect($filtros)->except(['inicio', 'fim'])->all(),
            'opcoes' => [
                'cidades' => $this->cidades($gestor, $filtros),
            ],
            'escopo_global' => UserHelper::possuiEscopoGlobal($gestor),
        ];
    }

    private function normalizarFiltros(User $gestor, array $filtros): array
    {
        resolverUsuario($gestor);

        $ano = (int) $filtros['ano'];
        $semestre = (int) $filtros['semestre'];
        $inicio = Carbon::create($ano, $semestre === 1 ? 1 : 7)->startOfMonth();
        $fim = $inicio->copy()->addMonths(5)->endOfMonth();
        $cidadeId = isset($filtros['cidade_id']) ? (int) $filtros['cidade_id'] : null;
        $escopoGlobal = UserHelper::possuiEscopoGlobal($gestor);

        if ($escopoGlobal && ! ($filtros['visao_global'] ?? false) && ! $cidadeId && $gestor->voluntario?->cidade_base_id) {
            $cidadeId = (int) $gestor->voluntario->cidade_base_id;
        }

        if (! $escopoGlobal) {
            $cidadeGestor = (int) $gestor->voluntario?->cidade_base_id;
            abort_if($cidadeGestor === 0 || ($cidadeId && $cidadeId !== $cidadeGestor), 403);
            $cidadeId = $cidadeGestor;
        }

        return [
            ...$filtros,
            'cidade_id' => $cidadeId,
            'visao_global' => $escopoGlobal ? (bool) ($filtros['visao_global'] ?? false) : false,
            'busca' => $filtros['busca'] ?? null,
            'situacao' => $filtros['situacao'] ?? 'ativos',
            'inicio' => $inicio,
            'fim' => $fim,
        ];
    }

    private function cidades(User $gestor, array $filtros): array
    {
        if (UserHelper::possuiEscopoGlobal($gestor)) {
            return Cidade::query()->orderBy('nome')->get(['id', 'nome'])->all();
        }

        return Cidade::query()->whereKey($filtros['cidade_id'])->get(['id', 'nome'])->all();
    }
}
