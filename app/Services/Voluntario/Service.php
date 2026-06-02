<?php

namespace App\Services\Voluntario;

use App\Queries\Voluntario\Queries;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Service
{
    public function __construct(private Queries $queries) {}

    public function index(array $filtros): array
    {
        try {

            $retorno = $this->queries->index($filtros);

            if (! $retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao listar dados!');
            }

            return $retorno;
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function store(array $dados): array
    {
        try {

            DB::beginTransaction();

            $cargoIds = array_values(array_unique(array_map('intval', $dados['cargo_ids'] ?? [])));

            $dadosUsuario = Arr::only($dados, ['name', 'email', 'password']);

            $retorno = $this->queries->store($dadosUsuario);

            if (! $retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao salvar dados!');

                DB::rollBack();

                return $retorno;
            }

            /** @var \App\Models\User $model */
            $model = $retorno['dados']['model'];

            $model->cargos()->sync($cargoIds);

            session()->flash('mensagem_sucesso', 'Dados salvos com sucesso!');

            DB::commit();

            return $retorno;
        } catch (\Throwable $th) {

            session()->flash('mensagem_erro', 'Erro ao salvar dados!');

            DB::rollBack();

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function update(int $id, array $dados): array
    {
        try {

            DB::beginTransaction();

            $cargoIds = array_values(array_unique(array_map('intval', $dados['cargo_ids'] ?? [])));

            $dadosUsuario = Arr::only($dados, ['name', 'email']);

            if (! empty($dados['password'])) {
                $dadosUsuario['password'] = $dados['password'];
            }

            $retorno = $this->queries->update((string) $id, $dadosUsuario);

            if (! $retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao salvar dados!');

                DB::rollBack();

                return $retorno;
            }

            /** @var \App\Models\User $model */
            $model = $retorno['dados']['model'];

            $model->cargos()->sync($cargoIds);

            session()->flash('mensagem_sucesso', 'Dados salvos com sucesso!');

            DB::commit();

            return $retorno;
        } catch (\Throwable $th) {

            session()->flash('mensagem_erro', 'Erro ao salvar dados!');

            DB::rollBack();

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function destroy(int $id): array
    {
        try {

            $retorno = $this->queries->destroy((string) $id);

            if (! $retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao excluir dados!');
            } else {

                session()->flash('mensagem_sucesso', 'Dados excluídos com sucesso!');
            }

            return $retorno;
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }
}
