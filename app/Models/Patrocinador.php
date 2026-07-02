<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Patrocinador extends Model
{
    use HasUuids;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'patrocinadores';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nome',
        'site',
        'categoria',
        'logo_path',
        'ativo',
        'ordem_exibicao', 
    ];

    // public function endereco()
    // {
    //     return $this->morphOne(Endereco::class, 'recurso', 'recurso_tipo', 'recurso_id');
    // }
}
