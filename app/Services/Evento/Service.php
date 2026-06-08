<?php

namespace App\Services\Evento;

use App\Queries\Evento\Queries;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Service
{
    public function __construct(private Queries $queries) {}

    public function index(array $filtros): array
    {
        try {

            $retorno = $this->queries->index($filtros);

            if (!$retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao listar eventos!');
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

    public function store(array $dados): array
    {
        try {

            DB::beginTransaction();

            $dadosDatabase = Arr::except($dados, []);
            $dadosDatabase['criado_por_id'] = Auth::id();

            $retorno = $this->queries->store($dadosDatabase);

            if (!$retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao salvar evento!');

                DB::rollBack();

                return $retorno;
            }

            session()->flash('mensagem_sucesso', 'Evento salvo com sucesso!');

            DB::commit();

            return $retorno;
        } catch (\Throwable $th) {

            session()->flash('mensagem_erro', 'Erro ao salvar evento!');

            DB::rollBack();

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)]
            ];
        }
    }

    public function update(string $id, array $dados): array
    {
        try {

            DB::beginTransaction();

            $dadosDatabase = Arr::except($dados, []);

            $retorno = $this->queries->update($id, $dadosDatabase);

            if (!$retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao atualizar evento!');

                DB::rollBack();

                return $retorno;
            }

            session()->flash('mensagem_sucesso', 'Evento atualizado com sucesso!');

            DB::commit();

            return $retorno;
        } catch (\Throwable $th) {

            session()->flash('mensagem_erro', 'Erro ao atualizar evento!');

            DB::rollBack();

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)]
            ];
        }
    }

    public function destroy(string $id): array
    {
        try {

            $retorno = $this->queries->destroy($id);

            if (!$retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao excluir evento!');
            } else {

                session()->flash('mensagem_sucesso', 'Evento excluído com sucesso!');
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
