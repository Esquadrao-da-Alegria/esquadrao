<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lembrete extends Model
{
    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_PROCESSADO = 'processado';
    public const STATUS_CANCELADO = 'cancelado';

    public const TIPO_RELATORIO_VISITA = 'relatorio_visita';
    public const TIPO_EVENTO_24H = 'evento_24h';
    public const TIPO_EVENTO_1H = 'evento_1h';

    protected $table = 'lembretes';

    protected $fillable = [
        'atividade_tipo',
        'atividade_id',
        'tipo',
        'programado_para',
        'status',
        'processado_em',
        'cancelado_em',
    ];

    protected function casts(): array
    {
        return [
            'programado_para' => 'datetime',
            'processado_em' => 'datetime',
            'cancelado_em' => 'datetime',
        ];
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(LembreteEntrega::class, 'lembrete_id');
    }

    public function scopePendentes(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDENTE);
    }

    public function scopeVencidos(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDENTE)
            ->where('programado_para', '<=', now());
    }

    public function estaPendente(): bool
    {
        return $this->status === self::STATUS_PENDENTE;
    }

    public function estaCancelado(): bool
    {
        return $this->status === self::STATUS_CANCELADO;
    }
}
