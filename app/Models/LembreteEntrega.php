<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LembreteEntrega extends Model
{
    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_SUCESSO = 'sucesso';
    public const STATUS_FALHA = 'falha';

    protected $table = 'lembrete_entregas';

    protected $fillable = [
        'lembrete_id',
        'atividade_tipo',
        'atividade_id',
        'tipo',
        'user_id',
        'push_subscription_id',
        'status',
        'erro',
        'enviado_em',
    ];

    protected function casts(): array
    {
        return [
            'enviado_em' => 'datetime',
        ];
    }

    public function lembrete(): BelongsTo
    {
        return $this->belongsTo(Lembrete::class, 'lembrete_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(PushSubscription::class, 'push_subscription_id');
    }

    public function scopeSucesso(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SUCESSO);
    }
}
