<?php

namespace App\Queries\Hospital\Meta;

// MODELS
use App\Models\MetaMensalHospital;

// ELOQUENT
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Queries
{
    public function index(array $filtros): array
    {
        $retornarLista = $filtros['retornar_lista'] ?? true;

        try {
            $query = MetaMensalHospital::query();

            $this->aplicarFiltros($query, $filtros);

            $dados = $retornarLista ? $query->get() : $query->first();

            return [
                'sucesso' => true,
                'dados'   => $dados,
                'erros'   => [],
            ];
        } catch (\Throwable $th) {
            $dados = $retornarLista ? new Collection() : null;

            return [
                'sucesso' => false,
                'dados'   => $dados,
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }

    public function show(string|int $id): array
    {
        try {
            $model = MetaMensalHospital::query()->findOrFail($id);

            return [
                'sucesso' => true,
                'dados'   => ['model' => $model],
                'erros'   => [],
            ];
        } catch (\Throwable $th) {
            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }

    public function store(array $dados): array
    {
        try {
            $model = MetaMensalHospital::create($dados);

            return [
                'sucesso' => true,
                'dados'   => ['id' => $model->id, 'model' => $model],
                'erros'   => [],
            ];
        } catch (\Throwable $th) {
            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }

    public function update(string|int $id, array $dados): array
    {
        try {
            $model = MetaMensalHospital::findOrFail($id);
            $model->update($dados);

            return [
                'sucesso' => true,
                'dados'   => ['model' => $model->fresh()],
                'erros'   => [],
            ];
        } catch (\Throwable $th) {
            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }

    public function destroy(string|int $id): array
    {
        try {
            $model = MetaMensalHospital::findOrFail($id);
            $model->delete();

            return [
                'sucesso' => true,
                'dados'   => ['model' => $model],
                'erros'   => [],
            ];
        } catch (\Throwable $th) {
            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }

    private function aplicarFiltros(Builder $query, array $filtros): Builder
    {
        if (! empty($filtros['hospital_ids'])) {
            $query->whereIn('hospital_id', $filtros['hospital_ids']);
        }

        if (! empty($filtros['hospital_id'])) {
            $query->where('hospital_id', $filtros['hospital_id']);
        }

        if (! empty($filtros['ano'])) {
            $query->where('ano', $filtros['ano']);
        }

        if (! empty($filtros['mes'])) {
            $query->where('mes', $filtros['mes']);
        }

        return $query;
    }
}
