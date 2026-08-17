<?php

namespace App\Http\Requests\Web\Voluntario\Afastamento;

use App\Enums\MotivoAfastamento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
            'motivo' => ['required', Rule::enum(MotivoAfastamento::class)],
            'observacoes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_inicio.date' => 'Informe uma data de início válida.',
            'data_fim.required' => 'A data de fim é obrigatória.',
            'data_fim.date' => 'Informe uma data de fim válida.',
            'data_fim.after_or_equal' => 'A data final deve ser igual ou posterior à data de início.',
            'motivo.required' => 'O motivo do afastamento é obrigatório.',
            'motivo.enum' => 'O motivo selecionado é inválido.',
            'observacoes.max' => 'As observações não podem exceder :max caracteres.',
        ];
    }
}
