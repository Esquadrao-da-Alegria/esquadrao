<?php

namespace App\Queries\Visita\Ajuste;

use App\Models\Visita;
use App\Models\VisitaAjusteContabilizacao;

class Queries
{
    public function index(Visita $visita): object
    {
        return VisitaAjusteContabilizacao::query()
            ->with(['voluntario:id,name', 'administrador:id,name', 'relatorio:id,visita_id,autor_id,fora_do_prazo,enviado_em'])
            ->where('visita_id', $visita->id)
            ->latest()
            ->get();
    }
}
