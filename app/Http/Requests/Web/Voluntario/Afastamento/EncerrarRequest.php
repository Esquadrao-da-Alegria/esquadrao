<?php

namespace App\Http\Requests\Web\Voluntario\Afastamento;

use Illuminate\Foundation\Http\FormRequest;

class EncerrarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'observacoes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'observacoes.max' => 'As observações não podem exceder :max caracteres.',
        ];
    }
}
