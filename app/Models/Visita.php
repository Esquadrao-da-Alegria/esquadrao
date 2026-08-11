<?php

namespace App\Models;

use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visita extends Model
{
    protected $table = 'visitas';

    protected $fillable = [
        'hospital_id',
        'ala_unidade_id',
        'criado_por_id',
        'lider_id',
        'inicio_em',
        'fim_em',
        'tipo',
        'limite_participantes',
        'status',
        'origem',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'inicio_em' => 'datetime',
            'fim_em' => 'datetime',
            'tipo' => VisitaTipo::class,
            'limite_participantes' => 'integer',
            'status' => VisitaStatus::class,
            'origem' => VisitaOrigem::class,
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

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }

    public function lider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lider_id');
    }

    public function participantes(): HasMany
    {
        return $this->hasMany(VisitaParticipante::class, 'visita_id');
    }

    public function relatorios(): HasMany
    {
        return $this->hasMany(VisitaRelatorio::class, 'visita_id');
    }

    public function ajustesContabilizacao(): HasMany
    {
        return $this->hasMany(VisitaAjusteContabilizacao::class, 'visita_id');
    }
}
