<?php

namespace App\Models;

use App\Enums\StatusAfastamento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Voluntario extends Model
{
    protected $table = 'voluntarios';

    protected $fillable = [
        'nome_completo',
        'nome_doutor',
        'email',
        'telefone',
        'data_nascimento',
        'cpf',
        'cidade_base_id',
        'data_entrada_ong',
        'status',
        'observacoes',
        'foto_perfil',
    ];

    protected $appends = [
        'name',
        'email_verified_at',
        'convite_enviado_em',
        'convite_expira_em',
        'convite_status',
        'convite_utilizado_em',
        'inativado_em',
        'cargos',
        'url_foto',
        'esta_afastado',
        'afastamento_atual',
    ];

    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date',
            'data_entrada_ong' => 'date',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function convitesCadastro(): HasMany
    {
        return $this->hasMany(ConviteCadastro::class);
    }

    public function conviteCadastroAtual(): HasOne
    {
        return $this->hasOne(ConviteCadastro::class)->latestOfMany();
    }

    public function cidadeBase(): BelongsTo
    {
        return $this->belongsTo(Cidade::class, 'cidade_base_id');
    }

    public function afastamentos(): HasMany
    {
        return $this->hasMany(VoluntarioAfastamento::class, 'voluntario_id');
    }

    public function afastamentoAtivo(): HasOne
    {
        return $this->hasOne(VoluntarioAfastamento::class, 'voluntario_id')
            ->where('status', StatusAfastamento::Ativo->value)
            ->where('data_inicio', '<=', now()->toDateString())
            ->where('data_fim', '>=', now()->toDateString())
            ->latestOfMany();
    }

    public function estaAfastado(?\DateTimeInterface $data = null): bool
    {
        $dataRef = $data ? $data->format('Y-m-d') : now()->toDateString();

        if ($this->relationLoaded('afastamentos')) {
            return $this->afastamentos->contains(function ($afastamento) use ($dataRef) {
                $status = $afastamento->status instanceof StatusAfastamento
                    ? $afastamento->status->value
                    : (string) $afastamento->status;

                $inicio = $afastamento->data_inicio instanceof \DateTimeInterface
                    ? $afastamento->data_inicio->format('Y-m-d')
                    : (string) $afastamento->data_inicio;

                $fim = $afastamento->data_fim instanceof \DateTimeInterface
                    ? $afastamento->data_fim->format('Y-m-d')
                    : (string) $afastamento->data_fim;

                return $status === StatusAfastamento::Ativo->value
                    && $inicio <= $dataRef
                    && $fim >= $dataRef;
            });
        }

        return $this->afastamentos()
            ->whereIn('status', [StatusAfastamento::Ativo, StatusAfastamento::Ativo->value])
            ->whereDate('data_inicio', '<=', $dataRef)
            ->whereDate('data_fim', '>=', $dataRef)
            ->exists();
    }

    public function getEstaAfastadoAttribute(): bool
    {
        return $this->estaAfastado();
    }

    public function getAfastamentoAtualAttribute(): ?VoluntarioAfastamento
    {
        if ($this->relationLoaded('afastamentos')) {
            return $this->afastamentos
                ->filter(function ($afastamento) {
                    $status = $afastamento->status instanceof StatusAfastamento
                        ? $afastamento->status->value
                        : $afastamento->status;

                    $inicio = $afastamento->data_inicio instanceof \DateTimeInterface
                        ? $afastamento->data_inicio->format('Y-m-d')
                        : (string) $afastamento->data_inicio;

                    $fim = $afastamento->data_fim instanceof \DateTimeInterface
                        ? $afastamento->data_fim->format('Y-m-d')
                        : (string) $afastamento->data_fim;

                    $hoje = now()->toDateString();

                    return $status === StatusAfastamento::Ativo->value
                        && $inicio <= $hoje
                        && $fim >= $hoje;
                })
                ->sortByDesc('data_fim')
                ->first();
        }

        return $this->afastamentoAtivo;
    }

    public function getNameAttribute(): string
    {
        return $this->nome_completo;
    }

    public function getEmailVerifiedAtAttribute(): mixed
    {
        return $this->user?->email_verified_at;
    }

    public function getConviteEnviadoEmAttribute(): mixed
    {
        return $this->conviteCadastroAtual?->enviado_em ?? $this->user?->convite_enviado_em;
    }

    public function getConviteExpiraEmAttribute(): mixed
    {
        return $this->conviteCadastroAtual?->expira_em ?? $this->user?->convite_expira_em;
    }

    public function getConviteStatusAttribute(): ?string
    {
        return $this->conviteCadastroAtual?->status;
    }

    public function getConviteUtilizadoEmAttribute(): mixed
    {
        return $this->conviteCadastroAtual?->utilizado_em;
    }

    public function getInativadoEmAttribute(): mixed
    {
        return $this->user?->inativado_em;
    }

    public function getCargosAttribute(): mixed
    {
        return $this->user?->cargos ?? collect();
    }

    public function getUrlFotoAttribute(): ?string
    {
        return $this->foto_perfil ? Storage::disk('public')->url($this->foto_perfil) : null;
    }
}
