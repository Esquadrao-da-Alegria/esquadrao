<?php

namespace App\Http\Requests\Web\Hospital\Meta;

// HELPERS
use App\Helpers\User as UserHelper;

// SERVICES
use App\Services\Hospital\Meta\Service as MetaService;

// HTTP
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'periodicidade' => $this->input('periodicidade', 'semanal'),
        ]);
    }

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
            'meta_mensal' => ['nullable', 'integer', 'min:0', 'max:' . MetaService::META_MENSAL_MAXIMA],
            'periodicidade' => ['required', Rule::in(['semanal', 'quinzenal'])],
            'metas_por_ala' => ['required', 'boolean'],
            'salvar_como_padrao' => ['nullable', 'boolean'],
            'metas_periodos' => ['nullable', 'array'],
            'metas_periodos.*.periodo' => ['required', 'integer', 'min:1', 'max:6'],
            'metas_periodos.*.quantidade' => ['required', 'integer', 'min:0', 'max:' . MetaService::META_MENSAL_MAXIMA],
            'metas_periodos.*.ala_unidade_id' => ['nullable', 'integer', 'exists:alas_hospitais,id'],
            'metas_semanais' => ['nullable', 'array'],
            'metas_semanais.*.semana' => ['required', 'integer', 'min:1', 'max:6'],
            'metas_semanais.*.quantidade' => ['required', 'integer', 'min:0', 'max:' . MetaService::META_SEMANAL_MAXIMA],
            'metas_semanais.*.ala_unidade_id' => ['nullable', 'integer', 'exists:alas_hospitais,id'],
        ];
    }
}
