<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\StatusInscricao;

class EventoParticipante extends Model
{
    use HasFactory;

    protected $table = 'evento_participantes';

    protected $fillable = [
        'evento_id',
        'user_id',
        'status',
        'presenca_confirmada_por_id',
        'presenca_confirmada_em',
    ];

    protected $casts = [
        'presenca_confirmada_em' => 'datetime',
        'status' => StatusInscricao::class,
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function confirmadoPor()
    {
        return $this->belongsTo(User::class, 'presenca_confirmada_por_id');
    }
}
