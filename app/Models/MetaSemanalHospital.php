<?php

namespace App\Models;

// ELOQUENT
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaSemanalHospital extends Model
{
    protected $table = 'metas_semanais_hospitais';

    protected $fillable = [
        'hospital_id',
        'ala_unidade_id',
        'ano',
        'mes',
        'semana',
        'quantidade',
    ];

    protected function casts(): array
    {
        return [
            'ano' => 'integer',
            'mes' => 'integer',
            'semana' => 'integer',
            'quantidade' => 'integer',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }

    public function alaUnidade(): BelongsTo
    {
        return $this->belongsTo(Ala::class, 'ala_unidade_id');
    }
}
