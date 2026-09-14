<?php

namespace App\Queries\Voluntario;

use App\Models\User;
use App\Models\ConviteCadastro;
use App\Models\Voluntario;
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
            $this->aplicarFiltroAba($query, $filtros['aba'] ?? 'voluntarios', $filtros['status'] ?? 'todos');

            $query->orderBy('nome_completo');

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
        return Voluntario::query()
            ->with(['user.cargos', 'conviteCadastroAtual', 'cidadeBase', 'afastamentos.registradoPor']);
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
                            ->where('nome_completo', 'like', "%{$valor}%")
                            ->orWhere('email', 'like', "%{$valor}%");
                    });

                    break;

                case 'status':

                    if (($filtros['aba'] ?? 'voluntarios') === 'voluntarios') {
                        if ($valor === 'afastados' || $valor === 'afastado') {
                            $query->whereHas('afastamentos', function (Builder $query) {
                                $query->where('status', \App\Enums\StatusAfastamento::Ativo->value)
                                    ->where('data_inicio', '<=', now()->toDateString())
                                    ->where('data_fim', '>=', now()->toDateString());
                            });
                        } elseif ($valor === 'ativos' || $valor === 'ativo') {
                            $query->whereDoesntHave('afastamentos', function (Builder $query) {
                                $query->where('status', \App\Enums\StatusAfastamento::Ativo->value)
                                    ->where('data_inicio', '<=', now()->toDateString())
                                    ->where('data_fim', '>=', now()->toDateString());
                            });
                        }
                    } elseif ($valor !== 'todos' && ($filtros['aba'] ?? 'voluntarios') === 'convidados') {
                        $this->aplicarFiltroStatus($query, (string) $valor);
                    }

                    break;

                case 'cidade_id':

                    if ($valor !== 'todas') {
                        $query->where('cidade_base_id', $valor);
                    }

                    break;

                case 'id':

                    $query->where('id', $valor);

                    break;

                case 'name':

                    $query->where('nome_completo', 'like', "%{$valor}%");

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

        $status = ['pendente', 'aceito', 'expirado', 'cancelado'];

        $contadores = collect($status)
            ->mapWithKeys(function (string $status) use ($filtros) {
                $query = $this->queryBase();

                $filtrosContagem = array_filter([
                    'busca' => $filtros['busca'] ?? null,
                    'cidade_id' => $filtros['cidade_id'] ?? null,
                ]);

                if (! empty($filtrosContagem)) {
                    $this->aplicarFiltros($query, $filtrosContagem);
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

        $filtrosContagem = array_filter([
            'busca' => $filtros['busca'] ?? null,
            'cidade_id' => $filtros['cidade_id'] ?? null,
        ]);

        if (! empty($filtrosContagem)) {
            $this->aplicarFiltros($query, $filtrosContagem);
        }

        $this->aplicarFiltroAba($query, $aba);

        return $query->count();
    }

    private function aplicarFiltroAba(Builder $query, string $aba, string $status = 'todos'): Builder
    {
        if ($aba === 'convidados') {
            $query->where(function (Builder $query) {
                $query
                    ->whereHas('convitesCadastro')
                    ->orWhereNull('status')
                    ->orWhere('status', User::STATUS_CONVITE_ENVIADO);
            });

            return $query;
        }

        if ($status === 'inativos') {
            $query->where(function (Builder $query) {
                $query
                    ->where('status', User::STATUS_INATIVO)
                    ->orWhereHas('user', fn (Builder $query) => $query->where('status', User::STATUS_INATIVO));
            });

            return $query;
        }

        $query
            ->where(function (Builder $query) {
                $query
                    ->where('status', User::STATUS_ATIVO)
                    ->orWhereHas('user', fn (Builder $query) => $query->whereNotNull('email_verified_at'));
            })
            ->where(function (Builder $query) {
                $query
                    ->whereNull('status')
                    ->orWhere('status', '!=', User::STATUS_INATIVO);
            })
            ->whereDoesntHave('user', fn (Builder $query) => $query->where('status', User::STATUS_INATIVO));

        return $query;
    }

    private function aplicarFiltroStatus(Builder $query, string $status): Builder
    {
        switch ($status) {
            case 'pendente':
                $query->where(function (Builder $query) {
                    $query
                        ->whereHas('conviteCadastroAtual', function (Builder $query) {
                            $query
                                ->whereIn('status', [
                                    ConviteCadastro::STATUS_PENDENTE,
                                    ConviteCadastro::STATUS_ENVIADO,
                                ])
                                ->where(function (Builder $query) {
                                    $query
                                        ->whereNull('expira_em')
                                        ->orWhere('expira_em', '>=', now());
                                });
                        })
                        ->orWhere(function (Builder $query) {
                            $query
                                ->whereDoesntHave('convitesCadastro')
                                ->where(function (Builder $query) {
                                    $query
                                        ->whereNull('status')
                                        ->orWhere(function (Builder $query) {
                                            $query
                                                ->where('status', User::STATUS_CONVITE_ENVIADO)
                                                ->whereHas('user', function (Builder $query) {
                                                    $query
                                                        ->whereNull('convite_expira_em')
                                                        ->orWhere('convite_expira_em', '>=', now());
                                                });
                                        });
                                });
                        });
                });

                break;

            case 'aceito':
                $query->whereHas('conviteCadastroAtual', function (Builder $query) {
                    $query
                        ->where('status', ConviteCadastro::STATUS_UTILIZADO)
                        ->orWhereNotNull('utilizado_em');
                });

                break;

            case 'expirado':
                $query->whereHas('conviteCadastroAtual', function (Builder $query) {
                    $query
                        ->where('status', ConviteCadastro::STATUS_EXPIRADO)
                        ->orWhere(function (Builder $query) {
                            $query
                                ->whereIn('status', [
                                    ConviteCadastro::STATUS_PENDENTE,
                                    ConviteCadastro::STATUS_ENVIADO,
                                ])
                                ->whereNotNull('expira_em')
                                ->where('expira_em', '<', now());
                        });
                });

                break;

            case 'cancelado':
                $query->whereHas(
                    'conviteCadastroAtual',
                    fn (Builder $query) => $query->where('status', ConviteCadastro::STATUS_CANCELADO)
                );

                break;

            default:
                break;
        }

        return $query;
    }

    public function store(array $dados): array
    {
        try {

            $model = Voluntario::create($dados);

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

            $model = Voluntario::findOrFail($id);

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

            $model = Voluntario::findOrFail($id);

            $sucesso = $model->update([
                'status' => User::STATUS_INATIVO,
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

            $model = Voluntario::findOrFail($id);

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
