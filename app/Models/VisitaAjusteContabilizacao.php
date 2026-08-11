<?php

namespace App\Models;

use App\Enums\TipoAjusteContabilizacao;
use App\Enums\TipoParticipacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitaAjusteContabilizacao extends Model
{
    protected $table = 'visitas_ajustes_contabilizacao';

    protected $fillable = [
        'visita_id',
        'voluntario_id',
        'relatorio_id',
        'administrador_id',
        'tipo',
        'tipo_participacao',
        'justificativa',
        'dados_anteriores',
        'dados_posteriores',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoAjusteContabilizacao::class,
            'tipo_participacao' => TipoParticipacao::class,
            'dados_anteriores' => 'array',
            'dados_posteriores' => 'array',
        ];
    }

    public function visita(): BelongsTo
    {
        return $this->belongsTo(Visita::class);
    }

    public function voluntario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voluntario_id');
    }

    public function relatorio(): BelongsTo
    {
        return $this->belongsTo(VisitaRelatorio::class, 'relatorio_id');
    }

    public function administrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'administrador_id');
    }
}
