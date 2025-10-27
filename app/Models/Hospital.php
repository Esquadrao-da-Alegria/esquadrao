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
        'cidade_id',
        'nome',
        'cnpj',
        'endereco',
        'telefone',
        'email',
        'ativo',
        'observacoes',
        'created_at',
        'updated_at',
    ];

    protected $table = 'hospitais';

    // public function endereco()
    // {
    //     return $this->morphOne(Endereco::class, 'recurso', 'recurso_tipo', 'recurso_id');
    // }
}
