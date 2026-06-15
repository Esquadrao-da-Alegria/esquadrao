<?php

namespace App\Http\Requests\Web\Evento;

use Illuminate\Foundation\Http\FormRequest;

class FinalizarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'presencas'            => ['required', 'array'],
            'presencas.*.user_id'  => ['required', 'integer', 'exists:users,id'],
            'presencas.*.status'   => ['required', 'string', 'in:PRESENTE,AUSENTE'],
        ];
    }

    public function messages(): array
    {
        return [
            'presencas.required'           => 'A lista de presenças é obrigatória.',
            'presencas.array'              => 'A lista de presenças deve ser um array.',
            'presencas.*.user_id.required' => 'O voluntário é obrigatório.',
            'presencas.*.user_id.exists'   => 'O voluntário informado não existe.',
            'presencas.*.status.required'  => 'O status de presença é obrigatório.',
            'presencas.*.status.in'        => 'O status deve ser PRESENTE ou AUSENTE.',
        ];
    }
}
