<?php

namespace App\Http\Requests\Web\Voluntario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var \App\Models\User $voluntario */
        $voluntario = $this->route('voluntario');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($voluntario->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'cargo_ids' => ['required', 'array', 'min:1'],
            'cargo_ids.*' => ['integer', 'exists:cargos,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'password.min' => 'A senha deve ter pelo menos :min caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'cargo_ids.required' => 'Selecione pelo menos um cargo.',
            'cargo_ids.min' => 'Selecione pelo menos um cargo.',
            'cargo_ids.*.exists' => 'Um dos cargos selecionados é inválido.',
        ];
    }
}
