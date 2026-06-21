<?php

namespace App\Queries\Visita;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Visita;

class Queries
{
    public function index(array $filtros): array
    {
        try {
            $query = Visita::query()
                ->with([
                    'hospital:id,nome',
                    'alaUnidade:id,nome',
                    'lider:id,name',
                    'participantes.voluntario:id,name',
                ]);

            $this->aplicarFiltros($query, $filtros);

            $query->orderBy('inicio_em');

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

    private function aplicarFiltros(Builder $query, array $filtros): void
    {
        foreach ($filtros as $campo => $valor) {
            if (empty($valor)) {
                continue;
            }

            switch ($campo) {
                case 'mes':
                    $inicio = Carbon::createFromFormat('Y-m', $valor)->startOfMonth();
                    $fim    = $inicio->copy()->endOfMonth();
                    $query->whereBetween('inicio_em', [$inicio, $fim]);
                    break;
            }
        }
    }
}
