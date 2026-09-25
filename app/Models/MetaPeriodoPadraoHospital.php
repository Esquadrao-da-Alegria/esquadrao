<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaPeriodoPadraoHospital extends Model
{
    protected $table = 'metas_periodos_padrao_hospitais';

    protected $fillable = [
        'meta_padrao_hospital_id',
        'ala_unidade_id',
        'periodo',
        'quantidade',
    ];

    protected function casts(): array
    {
        return [
            'periodo' => 'integer',
            'quantidade' => 'integer',
        ];
    }

    public function metaPadrao(): BelongsTo
    {
        return $this->belongsTo(MetaPadraoHospital::class, 'meta_padrao_hospital_id');
    }

    public function alaUnidade(): BelongsTo
    {
        return $this->belongsTo(Ala::class, 'ala_unidade_id');
    }
}
