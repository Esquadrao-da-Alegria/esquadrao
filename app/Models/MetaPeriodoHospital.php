<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaPeriodoHospital extends Model
{
    protected $table = 'metas_periodos_hospitais';

    protected $fillable = [
        'hospital_id',
        'ala_unidade_id',
        'ano',
        'mes',
        'periodo',
        'quantidade',
    ];

    protected function casts(): array
    {
        return [
            'ano' => 'integer',
            'mes' => 'integer',
            'periodo' => 'integer',
            'quantidade' => 'integer',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function alaUnidade(): BelongsTo
    {
        return $this->belongsTo(Ala::class, 'ala_unidade_id');
    }
}
