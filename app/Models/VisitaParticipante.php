<?php

namespace App\Models;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitaParticipante extends Model
{
    protected $table = 'visita_participante';

    protected $fillable = [
        'visita_id',
        'voluntario_id',
        'tipo_participacao',
        'papel_na_visita',
        'status_participacao',
    ];

    protected function casts(): array
    {
        return [
            'tipo_participacao' => TipoParticipacao::class,
            'papel_na_visita' => PapelNaVisita::class,
            'status_participacao' => StatusParticipacao::class,
        ];
    }

    public function visita(): BelongsTo
    {
        return $this->belongsTo(Visita::class, 'visita_id');
    }

    public function voluntario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voluntario_id');
    }
}
