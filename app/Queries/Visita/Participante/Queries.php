<?php

namespace App\Queries\Visita\Participante;

use App\Models\VisitaParticipante;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Queries
{
    public function index(array $filtros): array
    {
        $retornarLista = $filtros['retornar_lista'] ?? false;

        try {
            $query = VisitaParticipante::query();

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

    public function show(int $id): array
    {
        try {
            $model = VisitaParticipante::query()->findOrFail($id);

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
            $model = VisitaParticipante::create($dados);

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

    public function update(int $id, array $dados): array
    {
        try {
            $model = VisitaParticipante::findOrFail($id);
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

    public function destroy(int $id): array
    {
        try {
            $model = VisitaParticipante::findOrFail($id);
            $model->delete();

            return [
                'sucesso' => true,
                'dados'   => [],
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

    private function aplicarFiltros(Builder $query, array $filtros): void
    {
        foreach ($filtros as $campo => $valor) {
            if ($valor === null || $valor === '' || $campo === 'retornar_lista') {
                continue;
            }

            switch ($campo) {
                case 'visita_id':
                    $query->where('visita_id', $valor);
                    break;

                case 'voluntario_id':
                    $query->where('voluntario_id', $valor);
                    break;

                case 'papel_na_visita':
                    $query->where('papel_na_visita', $valor);
                    break;

                case 'status_participacao_in':
                    $query->whereIn('status_participacao', $valor);
                    break;
            }
        }
    }
}
