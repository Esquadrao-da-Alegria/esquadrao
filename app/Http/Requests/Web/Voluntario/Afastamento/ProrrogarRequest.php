<?php

namespace App\Http\Requests\Web\Voluntario\Afastamento;

use Illuminate\Foundation\Http\FormRequest;

class ProrrogarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nova_data_fim' => ['required_without:dias', 'nullable', 'date'],
            'dias' => ['required_without:nova_data_fim', 'nullable', 'integer', 'min:1'],
            'observacoes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nova_data_fim.required_without' => 'Informe a nova data final ou os dias a prorrogar.',
            'nova_data_fim.date' => 'Informe uma data válida.',
            'dias.required_without' => 'Informe os dias a prorrogar ou a nova data final.',
            'dias.integer' => 'A quantidade de dias deve ser um número inteiro.',
            'dias.min' => 'A prorrogação deve ser de pelo menos :min dia.',
            'observacoes.max' => 'As observações não podem exceder :max caracteres.',
        ];
    }
}
