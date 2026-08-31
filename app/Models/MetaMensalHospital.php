<?php

namespace App\Models;

// ELOQUENT
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaMensalHospital extends Model
{
    protected $table = 'metas_mensais_hospitais';

    protected $fillable = [
        'hospital_id',
        'ano',
        'mes',
        'quantidade',
    ];

    protected function casts(): array
    {
        return [
            'ano' => 'integer',
            'mes' => 'integer',
            'quantidade' => 'integer',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }
}
