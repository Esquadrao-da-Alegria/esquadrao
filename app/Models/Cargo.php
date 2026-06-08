<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cargo extends Model
{
    protected $table = 'cargos';

    protected $fillable = [
        'nome',
        'slug',
    ];

    /**
     * Usuários do sistema com este cargo.
     */
    public function voluntarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'voluntario_cargo', 'cargo_id', 'voluntario_id')
            ->withTimestamps();
    }
}
