<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConviteCadastro extends Model
{
    public const STATUS_PENDENTE = 'PENDENTE';
    public const STATUS_ENVIADO = 'ENVIADO';
    public const STATUS_UTILIZADO = 'UTILIZADO';
    public const STATUS_EXPIRADO = 'EXPIRADO';
    public const STATUS_CANCELADO = 'CANCELADO';

    protected $table = 'convites_cadastro';

    protected $fillable = [
        'voluntario_id',
        'token',
        'email',
        'status',
        'enviado_em',
        'utilizado_em',
        'expira_em',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'enviado_em' => 'datetime',
            'utilizado_em' => 'datetime',
            'expira_em' => 'datetime',
        ];
    }

    public function voluntario(): BelongsTo
    {
        return $this->belongsTo(Voluntario::class);
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function expirado(): bool
    {
        return $this->expira_em !== null && $this->expira_em->isPast();
    }
}
