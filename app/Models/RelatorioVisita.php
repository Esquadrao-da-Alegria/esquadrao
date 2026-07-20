<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelatorioVisita extends Model
{
    protected $table = 'relatorios_visita';

    protected $fillable = [
        'visita_id',
        'autor_id',
        'tipo_relatorio',
        'resumo',
        'feedback',
        'ala_unidade',
        'quartos_visitados',
        'pessoas_impactadas',
        'observacao_visitantes_externos',
        'observacoes_gerais',
        'enviado_em',
        'fora_do_prazo',
    ];

    protected function casts(): array
    {
        return [
            'enviado_em' => 'datetime',
            'fora_do_prazo' => 'boolean',
        ];
    }

    public function visita(): BelongsTo
    {
        return $this->belongsTo(Visita::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
