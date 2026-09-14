<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventoSessaoPresenca extends Model
{
    protected $table = 'evento_sessoes_presenca';

    protected $fillable = ['evento_id', 'aberta_por_id', 'aberta_em', 'encerrada_em', 'encerrada_por_id'];

    protected function casts(): array
    {
        return ['aberta_em' => 'datetime', 'encerrada_em' => 'datetime'];
    }

    public function evento(): BelongsTo { return $this->belongsTo(Evento::class); }
    public function abertaPor(): BelongsTo { return $this->belongsTo(User::class, 'aberta_por_id'); }
    public function confirmacoes(): HasMany { return $this->hasMany(EventoConfirmacaoPresenca::class, 'sessao_id'); }
}
