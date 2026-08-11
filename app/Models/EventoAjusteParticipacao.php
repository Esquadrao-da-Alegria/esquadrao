<?php

namespace App\Models;

use App\Enums\TipoAjusteEvento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoAjusteParticipacao extends Model
{
    protected $table = 'eventos_ajustes_participacao';

    protected $fillable = ['evento_id', 'voluntario_id', 'administrador_id', 'tipo', 'justificativa', 'dados_anteriores', 'dados_posteriores'];

    protected function casts(): array
    {
        return ['tipo' => TipoAjusteEvento::class, 'dados_anteriores' => 'array', 'dados_posteriores' => 'array'];
    }

    public function evento(): BelongsTo { return $this->belongsTo(Evento::class); }
    public function voluntario(): BelongsTo { return $this->belongsTo(User::class, 'voluntario_id'); }
    public function administrador(): BelongsTo { return $this->belongsTo(User::class, 'administrador_id'); }
}
