<?php

namespace App\Http\Requests\Web\Dashboard\Evento\ParticipacaoSemestral;

use App\Services\Dashboard\Permissao\Service as DashboardPermissaoService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(DashboardPermissaoService::PARTICIPACAO_SEMESTRAL) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ano' => $this->input('ano', now()->year),
            'semestre' => $this->input('semestre', now()->month <= 6 ? 1 : 2),
            'situacao' => $this->input('situacao', 'ativos'),
        ]);
    }

    public function rules(): array
    {
        return [
            'ano' => ['required', 'integer', 'min:2020', 'max:2100'],
            'semestre' => ['required', 'integer', Rule::in([1, 2])],
            'cidade_id' => ['nullable', 'integer', 'exists:cidades,id'],
            'visao_global' => ['nullable', 'boolean'],
            'busca' => ['nullable', 'string', 'max:120'],
            'situacao' => ['required', Rule::in(['ativos', 'inativos', 'todos'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
