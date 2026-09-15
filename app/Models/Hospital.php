<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
        'url_foto',
        'observacoes',
        'created_at',
        'updated_at',
    ];

    protected $with = ['alas'];

    protected $table = 'hospitais';

    public function cidade(): BelongsTo
    {
        return $this->belongsTo(Cidade::class);
    }

    public function alas()
    {
        return $this->hasMany(Ala::class, 'hospital_id');
    }
}
