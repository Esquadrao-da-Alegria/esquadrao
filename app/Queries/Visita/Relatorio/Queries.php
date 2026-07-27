<?php

namespace App\Queries\Visita\Relatorio;

use App\Models\VisitaRelatorio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Queries
{
    public function index(int $visitaId): array
    {
        try {
            $query = VisitaRelatorio::query()
                ->where('visita_id', $visitaId);

            $this->carregarRelacionamentos($query);
            $this->aplicarOrdenacao($query);

            $dados = $query->get();

            return [
                'sucesso' => true,
                'dados'   => $dados,
                'erros'   => [],
            ];
        } catch (\Throwable $th) {
            return [
                'sucesso' => false,
                'dados'   => new Collection(),
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }

    public function show(int $id): array
    {
        try {
            $query = VisitaRelatorio::query();

            $this->carregarRelacionamentos($query);

            $model = $query->findOrFail($id);

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
            $model = VisitaRelatorio::create($dados);

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
            $model = VisitaRelatorio::findOrFail($id);
            $model->update($dados);

            return [
                'sucesso' => true,
                'dados'   => ['model' => $model->fresh(['autor:id,name'])],
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

    private function carregarRelacionamentos(Builder $query): void
    {
        $query->with([
            'autor:id,name',
            'alaUnidade:id,nome,hospital_id',
        ]);
    }

    private function aplicarOrdenacao(Builder $query): void
    {
        $query->orderByDesc('enviado_em');
    }
}
