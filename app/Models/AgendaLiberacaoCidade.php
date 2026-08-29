<?php

namespace App\Models;

// ELOQUENT
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaLiberacaoCidade extends Model
{
    protected $table = 'agenda_liberacoes_cidades';

    protected $fillable = [
        'cidade_id',
        'ano',
        'mes',
        'liberado',
        'liberado_por_id',
    ];

    protected function casts(): array
    {
        return [
            'ano' => 'integer',
            'mes' => 'integer',
            'liberado' => 'boolean',
        ];
    }

    public function cidade(): BelongsTo
    {
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }

    public function liberadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'liberado_por_id');
    }
}
