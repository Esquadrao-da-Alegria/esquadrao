<?php

namespace App\Services\Evento\Form;

use App\Models\Evento;
use App\Services\Cidade\Service as CidadeService;

class Service
{
    public function __construct(private CidadeService $cidadeService) {}

    public function buscarDados(Evento|null $evento): array
    {
        $cidades = $this->cidadeService->index(['estado_id' => 43])['dados'];
        $eventosOrigem = Evento::query()
            ->where('status', 'AGENDADO')
            ->when($evento, fn($query) => $query->where('id', '!=', $evento->id))
            ->orderBy('data_inicio')
            ->get();

        $retorno = [
            'cidades' => $cidades,
            'eventos_origem' => $eventosOrigem,
        ];

        if ($evento) {
            $retorno['evento'] = $evento;
        }

        return $retorno;
    }
}
