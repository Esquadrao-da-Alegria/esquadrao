<?php

namespace App\Models;

use App\Enums\MotivoAfastamento;
use App\Enums\StatusAfastamento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoluntarioAfastamento extends Model
{
    use HasFactory;

    protected $table = 'voluntario_afastamentos';

    protected $fillable = [
        'voluntario_id',
        'registrado_por_id',
        'data_inicio',
        'data_fim',
        'motivo',
        'observacoes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim' => 'date',
            'motivo' => MotivoAfastamento::class,
            'status' => StatusAfastamento::class,
        ];
    }

    public function voluntario(): BelongsTo
    {
        return $this->belongsTo(Voluntario::class, 'voluntario_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_id');
    }
}
