<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Endereco extends Model
{
    protected $fillable = [
        'cidade_id',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cep',
    ];

    // Passa o nome customizado da coluna "type"
    public function recurso()
    {
        return $this->morphTo(__FUNCTION__, 'recurso_tipo', 'recurso_id');
    }

    public function cidade()
    {
        return $this->belongsTo(Cidade::class);
    }
}
