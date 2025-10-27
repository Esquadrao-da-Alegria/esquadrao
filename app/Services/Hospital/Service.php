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

                session()->flash('message_error', 'Erro ao listar dados!');
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
        dd($dados);
        try {

            $retorno = $this->queries->store($dados);

            if (!$retorno['sucesso']) {

                session()->flash('message_error', 'Erro ao salvar dados!');
            } else {

                session()->flash('message_error', 'Dados salvos com sucesso!');
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

                session()->flash('message_error', 'Erro ao salvar dados!');
            } else {

                session()->flash('message_error', 'Dados salvos com sucesso!');
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

    public function destroy(int $id): array
    {
        try {

            $retorno = $this->queries->destroy($id);

            if (!$retorno['sucesso']) {

                session()->flash('message_error', 'Erro ao excluir dados!');
            } else {

                session()->flash('message_error', 'Dados excluídos com sucesso!');
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
