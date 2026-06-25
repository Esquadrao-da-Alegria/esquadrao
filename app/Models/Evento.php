<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Evento extends Model
{
    protected $fillable = [
        'titulo', 'tipo', 'descricao', 'local', 'data_inicio', 'data_fim', 'limite_inscricao',
        'limite_participantes', 'status', 'responsavel_id', 'criado_por_id',
        'motivo_cancelamento', 'cancelado_em', 'cancelado_por_id',
        'finalizado_em', 'finalizado_por_id', 'observacoes_finalizacao',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'datetime',
            'data_fim' => 'datetime',
            'limite_inscricao' => 'datetime',
            'cancelado_em' => 'datetime',
            'finalizado_em' => 'datetime',
            'limite_participantes' => 'integer',
        ];
    }

    public function responsavel(): BelongsTo { return $this->belongsTo(User::class, 'responsavel_id'); }
    public function criadoPor(): BelongsTo { return $this->belongsTo(User::class, 'criado_por_id'); }
    public function canceladoPor(): BelongsTo { return $this->belongsTo(User::class, 'cancelado_por_id'); }
    public function finalizadoPor(): BelongsTo { return $this->belongsTo(User::class, 'finalizado_por_id'); }

    public function estaAgendado(): bool { return $this->status === 'agendado'; }
    public function estaCancelado(): bool { return $this->status === 'cancelado'; }
    public function estaFinalizado(): bool { return $this->status === 'finalizado'; }
    public function podeReceberInscricoes(): bool
    {
        if (! $this->estaAgendado() || $this->data_inicio->isPast()) return false;
        if ($this->limite_inscricao?->isPast()) return false;
        return true;
    }
    public function podeRegistrarPresenca(): bool { return ! $this->estaCancelado(); }

    public function participantes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'evento_participantes')
            ->using(EventoParticipante::class)
            ->withPivot([
                'status', 'inscrito_em', 'cancelado_em',
                'presenca', 'presenca_registrada_em', 'presenca_registrada_por_id', 'observacao_presenca',
            ])
            ->withTimestamps();
    }

    public function participantesAtivos(): BelongsToMany
    {
        return $this->participantes()->wherePivot('status', 'inscrito');
    }
}
