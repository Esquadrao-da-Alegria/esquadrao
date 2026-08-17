<?php

namespace App\Queries\Voluntario\Afastamento;

use App\Models\VoluntarioAfastamento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Queries
{
    public function index(array $filtros): array
    {
        try {
            $query = VoluntarioAfastamento::query()->with(['registradoPor:id,name']);

            if (! empty($filtros['voluntario_id'])) {
                $query->where('voluntario_id', $filtros['voluntario_id']);
            }

            if (! empty($filtros['status'])) {
                $query->where('status', $filtros['status']);
            }

            $query->orderByDesc('data_inicio');

            $dados = (! empty($filtros['retornar_lista']))
                ? $query->get()
                : $query->first();

            return [
                'sucesso' => true,
                'dados' => $dados,
                'erros' => [],
            ];
        } catch (\Throwable $th) {
            return [
                'sucesso' => false,
                'dados' => (! empty($filtros['retornar_lista'])) ? new Collection() : null,
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function show(string|int $id): array
    {
        try {
            $model = VoluntarioAfastamento::query()
                ->with(['registradoPor:id,name', 'voluntario'])
                ->findOrFail($id);

            return [
                'sucesso' => true,
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

    public function store(array $dados): array
    {
        try {
            $model = VoluntarioAfastamento::create($dados);

            return [
                'sucesso' => true,
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

    public function update(string|int $id, array $dados): array
    {
        try {
            $model = VoluntarioAfastamento::findOrFail($id);
            $model->update($dados);

            return [
                'sucesso' => true,
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

    public function destroy(string|int $id): array
    {
        try {
            $model = VoluntarioAfastamento::findOrFail($id);
            $model->delete();

            return [
                'sucesso' => true,
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
}
