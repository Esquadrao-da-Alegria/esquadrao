<?php

namespace App\Models;

use App\Enums\StatusEvento;
use App\Enums\TipoEvento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'complemento',
        'local_latitude',
        'local_longitude',
        'cidade_id',
        'status',
        'limite_vagas',
        'feedback_habilitado',
        'criado_por_id',
    ];

    protected $casts = [
        'data_inicio'      => 'datetime',
        'data_fim'         => 'datetime',
        'feedback_habilitado' => 'boolean',
        'limite_vagas'     => 'integer',
        'local_latitude'   => 'float',
        'local_longitude'  => 'float',
        'tipo'             => TipoEvento::class,
        'status'           => StatusEvento::class,
    ];

    public function cidade()
    {
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }

    public function participantes()
    {
        return $this->hasMany(EventoParticipante::class, 'evento_id');
    }

    public function responsaveis()
    {
        return $this->hasMany(EventoResponsavel::class, 'evento_id');
    }

}
