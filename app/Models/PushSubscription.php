<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'endpoint',
        'p256dh',
        'auth',
        'invalidado_em',
    ];

    protected function casts(): array
    {
        return [
            'invalidado_em' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAtivas(Builder $query): Builder
    {
        return $query->whereNull('invalidado_em');
    }

    public function invalidar(): void
    {
        $this->update(['invalidado_em' => now()]);
    }

    public function estaAtiva(): bool
    {
        return $this->invalidado_em === null;
    }
}