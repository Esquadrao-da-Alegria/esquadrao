<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventoResponsavel extends Model
{
    use HasFactory;

    protected $table = 'evento_responsaveis';

    protected $fillable = [
        'evento_id',
        'voluntario_id',
        'tipo_responsavel', // Reservado — não utilizado até definição dos cargos de voluntários
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    public function voluntario()
    {
        return $this->belongsTo(User::class, 'voluntario_id');
    }
}
