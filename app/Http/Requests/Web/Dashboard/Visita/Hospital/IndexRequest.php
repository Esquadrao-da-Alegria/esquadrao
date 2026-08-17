<?php

namespace App\Http\Requests\Web\Dashboard\Visita\Hospital;

use App\Models\Ala;
use App\Models\Hospital;
use App\Services\Dashboard\Permissao\Service as DashboardPermissaoService;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user?->can(DashboardPermissaoService::VISITAS_POR_HOSPITAL)) {
            return false;
        }

        $user->loadMissing(['cargos', 'voluntario']);

        if ($user->cargos->contains(fn ($cargo) => in_array($cargo->slug, ['administrador', 'coordenador_geral'], true))) {
            return true;
        }

        $cidadeId = (int) ($user->voluntario?->cidade_base_id ?? 0);
        if ($cidadeId === 0) {
            return false;
        }

        $cidadeSolicitada = $this->integer('cidade_id') ?: null;
        $hospitalId = $this->integer('hospital_id') ?: null;

        return (! $cidadeSolicitada || $cidadeSolicitada === $cidadeId)
            && (! $hospitalId || Hospital::query()->whereKey($hospitalId)->where('cidade_id', $cidadeId)->exists());
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mes_inicio' => $this->input('mes_inicio', now()->startOfYear()->format('Y-m')),
            'mes_fim' => $this->input('mes_fim', now()->format('Y-m')),
        ]);
    }

    public function rules(): array
    {
        return [
            'mes_inicio' => ['required', 'date_format:Y-m'],
            'mes_fim' => ['required', 'date_format:Y-m', 'after_or_equal:mes_inicio'],
            'cidade_id' => ['nullable', 'integer', 'exists:cidades,id'],
            'visao_global' => ['nullable', 'boolean'],
            'hospital_id' => ['nullable', 'integer', 'exists:hospitais,id'],
            'ala_id' => ['nullable', 'integer', 'exists:alas_hospitais,id'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $cidadeId = $this->integer('cidade_id') ?: null;
            $hospitalId = $this->integer('hospital_id') ?: null;
            $alaId = $this->integer('ala_id') ?: null;

            if ($hospitalId && ! $cidadeId) {
                $validator->errors()->add('hospital_id', 'Selecione uma cidade antes do hospital.');
            }

            if ($hospitalId && $cidadeId && ! Hospital::query()->whereKey($hospitalId)->where('cidade_id', $cidadeId)->exists()) {
                $validator->errors()->add('hospital_id', 'O hospital não pertence à cidade selecionada.');
            }

            if ($alaId && ! $hospitalId) {
                $validator->errors()->add('ala_id', 'Selecione um hospital antes da ala.');
            }

            if ($alaId && $hospitalId && ! Ala::query()->whereKey($alaId)->where('hospital_id', $hospitalId)->exists()) {
                $validator->errors()->add('ala_id', 'A ala não pertence ao hospital selecionado.');
            }
        });
    }
}
