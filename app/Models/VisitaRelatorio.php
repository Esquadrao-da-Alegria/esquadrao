<?php

namespace App\Models;

use App\Enums\TipoRelatorio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitaRelatorio extends Model
{
    protected $table = 'visitas_relatorios';

    protected $fillable = [
        'visita_id',
        'autor_id',
        'tipo_relatorio',
        'ala_unidade_id',
        'resumo',
        'feedback',
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
            'tipo_relatorio' => TipoRelatorio::class,
            'enviado_em'     => 'datetime',
            'fora_do_prazo'  => 'boolean',
        ];
    }

    public function visita(): BelongsTo
    {
        return $this->belongsTo(Visita::class, 'visita_id');
    }

    public function alaUnidade(): BelongsTo
    {
        return $this->belongsTo(Ala::class, 'ala_unidade_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function ajustesContabilizacao(): HasMany
    {
        return $this->hasMany(VisitaAjusteContabilizacao::class, 'relatorio_id');
    }
}
