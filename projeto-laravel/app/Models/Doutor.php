<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doutor extends Model
{
    protected $table = 'doutores';
    protected $fillable = [
        'nome_doutor',
        'descricao_doutor',
        'id_user',
    ];

    public function user()
        {
            return $this->belongsTo(\App\Models\User::class, 'id_user');
        }
}
