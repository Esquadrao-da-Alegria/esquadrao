<?php

namespace App\Http\Requests\Web\Dashboard\Meu;

use App\Services\Dashboard\Permissao\Service as DashboardPermissaoService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(DashboardPermissaoService::MEU_DASHBOARD) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'periodo_tipo' => $this->input('periodo_tipo', 'mes'),
            'ano' => $this->input('ano', now()->year),
            'mes' => $this->input('mes', now()->month),
        ]);
    }

    public function rules(): array
    {
        return [
            'periodo_tipo' => ['required', Rule::in(['mes', 'semestre', 'ano', 'personalizado'])],
            'ano' => ['required_unless:periodo_tipo,personalizado', 'nullable', 'integer', 'min:2020', 'max:2100'],
            'mes' => ['required_if:periodo_tipo,mes', 'nullable', 'integer', 'between:1,12'],
            'semestre' => ['required_if:periodo_tipo,semestre', 'nullable', 'integer', Rule::in([1, 2])],
            'data_inicio' => ['required_if:periodo_tipo,personalizado', 'nullable', 'date'],
            'data_fim' => ['required_if:periodo_tipo,personalizado', 'nullable', 'date', 'after_or_equal:data_inicio'],
            'cidade_id' => ['nullable', 'integer', 'exists:cidades,id'],
            'atividade' => ['nullable', Rule::in(['visitas', 'reunioes', 'oficinas'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('periodo_tipo') !== 'personalizado'
                || ! $this->filled(['data_inicio', 'data_fim'])) {
                return;
            }

            $inicio = Carbon::parse($this->input('data_inicio'))->startOfDay();
            $fim = Carbon::parse($this->input('data_fim'))->endOfDay();

            if ($inicio->copy()->addMonths(24)->endOfDay()->lt($fim)) {
                $validator->errors()->add('data_fim', 'O período personalizado pode abranger no máximo 24 meses.');
            }
        });
    }
}
