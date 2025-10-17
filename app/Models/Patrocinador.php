<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patrocinador extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nome',
        'site',
        'categoria',
        'url_logotipo',
        'ativo',
        'observacoes',
    ];

    // public function endereco()
    // {
    //     return $this->morphOne(Endereco::class, 'recurso', 'recurso_tipo', 'recurso_id');
    // }
}
