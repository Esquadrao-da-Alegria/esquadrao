<?php

namespace App\Queries\Voluntario;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class Queries
{
    public function index(array $filtros): array
    {
        $retornarLista = $filtros['retornar_lista'];

        try {
            $query = $this->queryBase();

            $this->aplicarFiltros($query, $filtros);
            $this->aplicarFiltroAba($query, $filtros['aba'] ?? 'voluntarios');

            $query->orderBy('name');

            $dados = $retornarLista
                ? $query
                    ->paginate((int) ($filtros['por_pagina'] ?? 10))
                    ->withQueryString()
                : $query->first();

            return [
                'sucesso' => true,
                'dados' => $dados,
                'contadores' => $retornarLista
                    ? $this->contarPorStatus($filtros)
                    : [],
                'erros' => [],
            ];
        } catch (\Throwable $th) {

            $dados = $retornarLista ? new LengthAwarePaginator([], 0, 10) : null;

            return [
                'sucesso' => false,
                'dados' => $dados,
                'contadores' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    private function queryBase(): Builder
    {
        return User::query()
            ->with('cargos')
            ->whereHas('cargos');
    }

    private function aplicarFiltros(Builder $query, array $filtros): Builder
    {
        foreach ($filtros as $campo => $valor) {

            if (empty($valor)) {
                continue;
            }

            switch ($campo) {
                case 'aba':

                    break;

                case 'busca':

                    $query->where(function (Builder $query) use ($valor) {
                        $query
                            ->where('name', 'like', "%{$valor}%")
                            ->orWhere('email', 'like', "%{$valor}%");
                    });

                    break;

                case 'status':

                    if ($valor !== 'todos' && ($filtros['aba'] ?? 'voluntarios') === 'convidados') {
                        $this->aplicarFiltroStatus($query, (string) $valor);
                    }

                    break;

                case 'id':

                    $query->where('id', $valor);

                    break;

                case 'name':

                    $query->where('name', 'like', "%{$valor}%");

                    break;

                case 'email':

                    $query->where('email', 'like', "%{$valor}%");

                    break;
            }
        }

        return $query;
    }

    private function contarPorStatus(array $filtros): array
    {
        $aba = $filtros['aba'] ?? 'voluntarios';

        if ($aba === 'voluntarios') {
            return [
                'voluntarios' => $this->contarAba('voluntarios', $filtros),
                'convidados' => $this->contarAba('convidados', $filtros),
            ];
        }

        $status = ['pendente', 'convite_enviado', 'convite_expirado'];

        $contadores = collect($status)
            ->mapWithKeys(function (string $status) use ($filtros) {
                $query = $this->queryBase();

                if (! empty($filtros['busca'])) {
                    $this->aplicarFiltros($query, ['busca' => $filtros['busca']]);
                }

                $this->aplicarFiltroStatus($query, $status);

                return [$status => $query->count()];
            })
            ->all();

        return [
            ...$contadores,
            'voluntarios' => $this->contarAba('voluntarios', $filtros),
            'convidados' => $this->contarAba('convidados', $filtros),
        ];
    }

    private function contarAba(string $aba, array $filtros): int
    {
        $query = $this->queryBase();

        if (! empty($filtros['busca'])) {
            $this->aplicarFiltros($query, ['busca' => $filtros['busca']]);
        }

        $this->aplicarFiltroAba($query, $aba);

        return $query->count();
    }

    private function aplicarFiltroAba(Builder $query, string $aba): Builder
    {
        if ($aba === 'convidados') {
            $query->where(function (Builder $query) {
                $query
                    ->whereNull('status')
                    ->orWhere('status', User::STATUS_CONVITE_ENVIADO);
            });

            return $query;
        }

        $query
            ->where(function (Builder $query) {
                $query
                    ->where('status', User::STATUS_ATIVO)
                    ->orWhereNotNull('email_verified_at');
            })
            ->where(function (Builder $query) {
                $query
                    ->whereNull('status')
                    ->orWhere('status', '!=', User::STATUS_INATIVO);
            });

        return $query;
    }

    private function aplicarFiltroStatus(Builder $query, string $status): Builder
    {
        switch ($status) {
            case 'pendente':
                $query->whereNull('status');

                break;

            case 'convite_enviado':
                $query
                    ->where('status', User::STATUS_CONVITE_ENVIADO)
                    ->where(function (Builder $query) {
                        $query
                            ->whereNull('convite_expira_em')
                            ->orWhere('convite_expira_em', '>=', now());
                    });

                break;

            case 'convite_expirado':
                $query
                    ->where('status', User::STATUS_CONVITE_ENVIADO)
                    ->whereNotNull('convite_expira_em')
                    ->where('convite_expira_em', '<', now());

                break;

            case 'cadastro_completo':
                $query
                    ->whereNotNull('email_verified_at')
                    ->where('status', '!=', User::STATUS_INATIVO);

                break;

            case 'inativo':
                $query->where('status', User::STATUS_INATIVO);

                break;

            case 'ativo':
            default:
                $query
                    ->where('status', User::STATUS_ATIVO)
                    ->whereNull('email_verified_at');

                break;
        }

        return $query;
    }

    public function store(array $dados): array
    {
        try {

            $model = User::create($dados);

            $sucesso = $model && $model->id !== null;

            return [
                'sucesso' => $sucesso,
                'dados' => ['id' => $model->id, 'model' => $model],
                'erros' => [],
            ];
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function update(string $id, array $dados): array
    {
        try {

            $model = User::findOrFail($id);

            $model->update($dados);

            $sucesso = $model->save();

            return [
                'sucesso' => $sucesso,
                'dados' => ['model' => $model],
                'erros' => [],
            ];
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function destroy(string $id): array
    {
        try {

            $model = User::findOrFail($id);

            $sucesso = $model->update([
                'status' => User::STATUS_INATIVO,
                'inativado_em' => now(),
            ]);

            return [
                'sucesso' => $sucesso,
                'dados' => ['model' => $model],
                'erros' => [],
            ];
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function delete(string $id): array
    {
        try {

            $model = User::findOrFail($id);

            $sucesso = $model->delete();

            return [
                'sucesso' => $sucesso,
                'dados' => [],
                'erros' => [],
            ];
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }
}
