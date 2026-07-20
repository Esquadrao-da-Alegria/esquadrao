<?php

namespace App\Http\Requests\Web\RelatorioVisita;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'tipo_relatorio' => ['required', 'string', Rule::in(['artista', 'paisana', 'geral'])],
            'resumo' => ['required', 'string', 'max:5000'],
            'feedback' => ['nullable', 'string', 'max:5000'],
            'ala_unidade' => ['nullable', 'string', 'max:255'],
            'quartos_visitados' => ['nullable', 'integer', 'min:0'],
            'pessoas_impactadas' => ['nullable', 'integer', 'min:0'],
            'observacao_visitantes_externos' => ['nullable', 'string', 'max:5000'],
            'observacoes_gerais' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
