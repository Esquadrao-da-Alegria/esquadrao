<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    public const STATUS_ATIVO = 'ativo';
    public const STATUS_CONVITE_ENVIADO = 'convite_enviado';
    public const STATUS_INATIVO = 'inativo';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'voluntario_id',
        'name',
        'email',
        'password',
        'status',
        'convite_enviado_em',
        'convite_expira_em',
        'inativado_em',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'convite_enviado_em' => 'datetime',
            'convite_expira_em' => 'datetime',
            'inativado_em' => 'datetime',
        ];
    }

    protected $with = ['cargos'];

    public function voluntario(): BelongsTo
    {
        return $this->belongsTo(Voluntario::class);
    }

    /**
     * Cargos deste usuário do sistema.
     */
    public function eventosInscritos(): BelongsToMany
    {
        return $this->belongsToMany(Evento::class, 'evento_participantes')
            ->using(EventoParticipante::class)
            ->withPivot(['status', 'inscrito_em', 'cancelado_em'])
            ->withTimestamps();
    }

    public function cargos(): BelongsToMany
    {
        return $this->belongsToMany(Cargo::class, 'voluntario_cargo', 'voluntario_id', 'cargo_id')
            ->withTimestamps();
    }

    public function temCargo(string $slug): bool
    {
        return $this->cargos()->where('slug', $slug)->exists();
    }
}
