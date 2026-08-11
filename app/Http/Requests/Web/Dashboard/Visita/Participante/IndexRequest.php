<?php

namespace App\Http\Requests\Web\Dashboard\Visita\Participante;

use App\Services\Dashboard\Permissao\Service as DashboardPermissaoService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(DashboardPermissaoService::VISITAS_POR_PARTICIPANTE) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'periodo_tipo' => $this->input('periodo_tipo', 'ano'),
            'ano' => $this->input('ano', now()->year),
        ]);
    }

    public function rules(): array
    {
        return [
            'periodo_tipo' => ['required', Rule::in(['mes', 'semestre', 'ano'])],
            'ano' => ['required', 'integer', 'min:2020', 'max:2100'],
            'mes' => ['nullable', 'integer', 'between:1,12', 'required_if:periodo_tipo,mes'],
            'semestre' => ['nullable', 'integer', Rule::in([1, 2]), 'required_if:periodo_tipo,semestre'],
            'cidade_id' => ['nullable', 'integer', 'exists:cidades,id'],
            'visao_global' => ['nullable', 'boolean'],
            'busca' => ['nullable', 'string', 'max:120'],
            'participante_id' => ['nullable', 'integer', 'exists:users,id'],
            'cargo_id' => ['nullable', 'integer', 'exists:cargos,id'],
            'tipo_atuacao' => ['nullable', Rule::in(['visitas', 'administrativo', 'isento', 'dados_insuficientes'])],
            'situacao' => ['nullable', Rule::in(['dentro_meta', 'atencao', 'compensacao_pendente', 'requer_analise', 'isento', 'dados_insuficientes'])],
            'atividade' => ['nullable', Rule::in(['visitas', 'reunioes', 'oficinas'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
