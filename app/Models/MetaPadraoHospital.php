<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaPadraoHospital extends Model
{
    protected $table = 'metas_padrao_hospitais';

    protected $fillable = [
        'hospital_id',
        'quantidade',
        'periodicidade',
        'metas_por_ala',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
            'metas_por_ala' => 'boolean',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function periodos(): HasMany
    {
        return $this->hasMany(MetaPeriodoPadraoHospital::class, 'meta_padrao_hospital_id');
    }
}
