<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ala extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nome',
        'observacoes',
        'hospital_id',
    ];

    protected $table = 'alas_hospitais';

    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }
}