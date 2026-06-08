<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\TipoEvento;
use App\Enums\StatusEvento;

class Evento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'eventos';

    protected $fillable = [
        'tipo',
        'titulo',
        'descricao',
        'data_inicio',
        'data_fim',
        'local',
        'cidade_id',
        'status',
        'limite_vagas',
        'feedback_habilitado',
        'evento_origem_id',
        'criado_por_id',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'feedback_habilitado' => 'boolean',
        'limite_vagas' => 'integer',
        'tipo' => TipoEvento::class,
        'status' => StatusEvento::class,
    ];

    public function cidade()
    {
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }

    public function eventoOrigem()
    {
        return $this->belongsTo(self::class, 'evento_origem_id');
    }

    public function eventosTransferidos()
    {
        return $this->hasMany(self::class, 'evento_origem_id');
    }

    public function participantes()
    {
        return $this->hasMany(EventoParticipante::class, 'evento_id');
    }
}
