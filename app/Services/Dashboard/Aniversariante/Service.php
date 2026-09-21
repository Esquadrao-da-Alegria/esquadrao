<?php

namespace App\Services\Dashboard\Aniversariante;

use Carbon\Carbon;

// UTILS
use App\Helpers\User as UserHelper;

// MODELS
use App\Models\Cidade;
use App\Models\User;
use App\Models\Voluntario;

class Service
{
    public function index(User $user, ?string $cidadeSolicitada = null): array
    {
        resolverUsuario($user);

        $hoje = now();
        $cidadeId = $this->cidade($user, $cidadeSolicitada);
        $possuiEscopoGlobal = UserHelper::possuiEscopoGlobal($user);
        $itens = $this->aniversariantes($hoje, $cidadeId);

        return [
            'itens' => $itens,
            'mes' => $hoje->month,
            'cidade_id' => $cidadeId,
            'possui_escopo_global' => $possuiEscopoGlobal,
            'cidades' => $possuiEscopoGlobal
                ? Cidade::query()->orderBy('nome')->get(['id', 'nome'])->map(fn (Cidade $cidade) => ['id' => $cidade->id, 'nome' => $cidade->nome])->all()
                : [],
        ];
    }

    public function atual(User $user): ?array
    {
        resolverUsuario($user);

        $voluntario = $user->voluntario;

        if (! $voluntario || $user->status !== User::STATUS_ATIVO || $voluntario->status !== User::STATUS_ATIVO || ! $voluntario->data_nascimento) {
            return null;
        }

        $hoje = now();

        if (! $this->ehHoje($voluntario->data_nascimento, $hoje)) {
            return null;
        }

        return [
            'nome' => (string) str($voluntario->nome_completo)->explode(' ')->first(),
            'data' => $hoje->toDateString(),
        ];
    }

    private function aniversariantes(Carbon $hoje, ?int $cidadeId): array
    {
        if ($cidadeId === 0) {
            return [];
        }

        return Voluntario::query()
            ->where('status', User::STATUS_ATIVO)
            ->whereNotNull('data_nascimento')
            ->whereMonth('data_nascimento', $hoje->month)
            ->whereHas('user', fn ($query) => $query->where('status', User::STATUS_ATIVO))
            ->when($cidadeId !== null, fn ($query) => $query->where('cidade_base_id', $cidadeId))
            ->get(['id', 'nome_completo', 'foto_perfil', 'data_nascimento'])
            ->sortBy(fn (Voluntario $voluntario) => [$voluntario->data_nascimento->day, $voluntario->nome_completo])
            ->values()
            ->map(fn (Voluntario $voluntario) => [
                'id' => $voluntario->id,
                'nome' => $voluntario->nome_completo,
                'foto_url' => $voluntario->url_foto,
                'dia' => $voluntario->data_nascimento->day,
                'mes' => $voluntario->data_nascimento->month,
                'eh_hoje' => $this->ehHoje($voluntario->data_nascimento, $hoje),
            ])
            ->all();
    }

    private function cidade(User $user, ?string $cidadeSolicitada): ?int
    {
        $cidadeUsuarioId = $user->voluntario?->cidade_base_id;

        if (! UserHelper::possuiEscopoGlobal($user)) {
            return $cidadeUsuarioId ?: 0;
        }

        if ($cidadeSolicitada === 'todas') {
            return null;
        }

        if (ctype_digit((string) $cidadeSolicitada) && Cidade::query()->whereKey((int) $cidadeSolicitada)->exists()) {
            return (int) $cidadeSolicitada;
        }

        return $cidadeUsuarioId;
    }

    private function ehHoje(Carbon $nascimento, Carbon $hoje): bool
    {
        if ($nascimento->month === $hoje->month && $nascimento->day === $hoje->day) {
            return true;
        }

        return $nascimento->month === 2
            && $nascimento->day === 29
            && ! $hoje->isLeapYear()
            && $hoje->month === 2
            && $hoje->day === 28;
    }
}
