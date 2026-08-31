<?php

namespace App\Queries\Visita;

use App\Enums\VisitaStatus;
use App\Models\Visita;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Queries
{
    public function index(array $filtros): array
    {
        try {
            $query = Visita::query()
                ->where('status', '!=', VisitaStatus::Cancelada->value);

            $this->carregarRelacionamentos($query);
            $this->aplicarFiltros($query, $filtros);
            $this->aplicarOrdenacao($query);

            $dados = $query->get();

            return ['sucesso' => true, 'dados' => $dados, 'erros' => []];
        } catch (\Throwable $th) {
            return [
                'sucesso' => false,
                'dados'   => new Collection(),
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }

    public function show(array $filtros): array
    {
        try {
            $query = Visita::query();

            $this->carregarRelacionamentos($query);
            $this->aplicarFiltros($query, $filtros);

            $dados = $query->first();

            return ['sucesso' => true, 'dados' => $dados, 'erros' => []];
        } catch (\Throwable $th) {
            return [
                'sucesso' => false,
                'dados'   => null,
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }

    public function store(array $dados): array
    {
        try {
            $model = Visita::query()->create($dados);

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

    public function update(int $id, array $dados): array
    {
        try {
            $model = Visita::query()->findOrFail($id);
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

    private function carregarRelacionamentos(Builder $query): void
    {
        $query->with([
            'hospital:id,nome,cidade_id',
            'alaUnidade:id,nome',
            'lider:id,name',
            'participantes.voluntario:id,name',
        ]);
    }

    private function aplicarFiltros(Builder $query, array $filtros): void
    {
        foreach ($filtros as $campo => $valor) {
            if (empty($valor)) {
                continue;
            }

            switch ($campo) {
                case 'id':
                    $query->where('id', $valor);
                    break;

                case 'mes':
                    $inicio = Carbon::createFromFormat('!Y-m', $valor)->startOfMonth();
                    $fim    = $inicio->copy()->endOfMonth();
                    $query->whereBetween('inicio_em', [$inicio, $fim]);
                    break;

                case 'cidade_id':
                    $query->where(function (Builder $q) use ($valor) {
                        $q->whereHas('hospital', function (Builder $h) use ($valor) {
                            $h->where('cidade_id', $valor);
                        })->orWhere(function (Builder $h) use ($valor) {
                            $h->whereNull('hospital_id')
                                ->whereHas('lider.voluntario', function (Builder $v) use ($valor) {
                                    $v->where('cidade_base_id', $valor);
                                });
                        });
                    });
                    break;
            }
        }
    }

    private function aplicarOrdenacao(Builder $query): void
    {
        $query->orderBy('inicio_em');
    }
}
