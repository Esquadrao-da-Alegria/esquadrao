<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EventoParticipante extends Pivot
{
    protected $table = 'evento_participantes';

    protected $fillable = ['evento_id', 'user_id', 'status', 'inscrito_em', 'cancelado_em'];

    protected function casts(): array
    {
        return ['inscrito_em' => 'datetime', 'cancelado_em' => 'datetime'];
    }
}
