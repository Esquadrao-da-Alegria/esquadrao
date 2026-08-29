<?php

namespace App\Http\Requests\Web\Hospital\Meta;

// HELPERS
use App\Helpers\User as UserHelper;

// SERVICES
use App\Services\Hospital\Meta\Service as MetaService;

// HTTP
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        return UserHelper::ehGestor($user);
    }

    public function rules(): array
    {
        return [
            'ano' => ['required', 'integer', 'min:2000', 'max:2100'],
            'mes' => ['required', 'integer', 'min:1', 'max:12'],
            'hospitais' => ['required', 'array'],
            'hospitais.*.hospital_id' => ['required', 'integer', 'exists:hospitais,id'],
            'hospitais.*.meta_mensal' => ['nullable', 'integer', 'min:0', 'max:' . MetaService::META_MENSAL_MAXIMA],
            'hospitais.*.metas_por_ala' => ['required', 'boolean'],
            'hospitais.*.metas_semanais' => ['nullable', 'array'],
            'hospitais.*.metas_semanais.*.semana' => ['required', 'integer', 'min:1', 'max:6'],
            'hospitais.*.metas_semanais.*.quantidade' => ['required', 'integer', 'min:0', 'max:' . MetaService::META_SEMANAL_MAXIMA],
            'hospitais.*.metas_semanais.*.ala_unidade_id' => ['nullable', 'integer', 'exists:alas_hospitais,id'],
        ];
    }
}
