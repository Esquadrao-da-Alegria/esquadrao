<?php

namespace App\Services\Hospital;

use App\Queries\Hospital\Queries;

class Service
{
    public function __construct(private Queries $queries) {}

    public function index(array $filtros): array
    {
        try {

            $retorno = $this->queries->index($filtros);

            if (!$retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao listar dados!');
            }

            return $retorno;
        } catch (\Throwable $th) {dd($th);

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)]
            ];
        }
    }

    public function store(array $dados): array
    {
        try {

            $retorno = $this->queries->store($dados);

            if (!$retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao salvar dados!');
            } else {

                session()->flash('mensagem_sucesso', 'Dados salvos com sucesso!');
            }

            return $retorno;
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)]
            ];
        }
    }

    public function update(int $id, array $dados): array
    {
        try {

            $retorno = $this->queries->update($id, $dados);

            if (!$retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao salvar dados!');
            } else {

                session()->flash('mensagem_sucesso', 'Dados salvos com sucesso!');
            }

            return $retorno;
        } catch (\Throwable $th) {dd($th);

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)]
            ];
        }
    }

    public function destroy(int $id): array
    {
        try {

            $retorno = $this->queries->destroy($id);

            if (!$retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao excluir dados!');
            } else {

                session()->flash('mensagem_sucesso', 'Dados excluídos com sucesso!');
            }

            return $retorno;
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)]
            ];
        }
    }
}
