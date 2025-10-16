<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nome',
        'ativo',
        'observacoes',
    ];

    public function endereco()
    {
        return $this->morphOne(Endereco::class, 'recurso', 'recurso_tipo', 'recurso_id');
    }
}
