<?php

namespace App\Http\Requests\Web\Hospital;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permitir o envio (ajuste conforme políticas de acesso)
        return true;
    }

    public function rules(): array
    {
        return [];

        return [
            'cidade_id'      => ['nullable', 'integer', 'exists:cidades,id'],
            'nome'           => ['required', 'string', 'max:255'],
            'cnpj'           => ['required', 'string', 'min:14'],
            'endereco'       => ['required', 'string', 'max:255'],
            'telefone'       => ['required', 'string', 'max:14'],
            'email'          => ['required', 'string', 'email', 'max:50'],
            'ativo'          => ['boolean'],
            'alas_unidades'  => ['required', 'array'],
            'alas_unidades.*' => ['string', 'max:255'],
            'observacoes'    => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required'      => 'O nome do hospital é obrigatório.',
            'cnpj.required'      => 'O CNPJ é obrigatório.',
            'cnpj.size'          => 'O CNPJ deve conter exatamente 14 dígitos.',
            'email.email'        => 'Informe um e-mail válido.',
            'alas_unidades.required' => 'Informe ao menos uma ala ou unidade.',
        ];
    }
}
