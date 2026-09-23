<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoConfirmacaoPresenca extends Model
{
    protected $table = 'evento_confirmacoes_presenca';

    protected $fillable = ['evento_id', 'user_id', 'sessao_id', 'aberta_por_id', 'metodo', 'confirmada_em'];

    protected function casts(): array
    {
        return ['confirmada_em' => 'datetime'];
    }

    public function usuario(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
}
