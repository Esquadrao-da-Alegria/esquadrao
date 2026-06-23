<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Evento extends Model
{
    protected $fillable = [
        'titulo', 'tipo', 'descricao', 'local', 'data_inicio', 'data_fim',
        'limite_participantes', 'status', 'responsavel_id', 'criado_por_id',
        'motivo_cancelamento', 'cancelado_em', 'cancelado_por_id',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'datetime',
            'data_fim' => 'datetime',
            'cancelado_em' => 'datetime',
            'limite_participantes' => 'integer',
        ];
    }

    public function responsavel(): BelongsTo { return $this->belongsTo(User::class, 'responsavel_id'); }
    public function criadoPor(): BelongsTo { return $this->belongsTo(User::class, 'criado_por_id'); }
    public function canceladoPor(): BelongsTo { return $this->belongsTo(User::class, 'cancelado_por_id'); }

    public function participantes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'evento_participantes')
            ->using(EventoParticipante::class)
            ->withPivot(['status', 'inscrito_em', 'cancelado_em'])
            ->withTimestamps();
    }

    public function participantesAtivos(): BelongsToMany
    {
        return $this->participantes()->wherePivot('status', 'inscrito');
    }
}
