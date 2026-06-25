<?php

namespace App\Http\Requests\Web\Evento;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->temCargo('administrador') === true; }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'tipo' => ['required', Rule::in(['oficina', 'reuniao', 'evento'])],
            'descricao' => ['nullable', 'string'],
            'local' => ['nullable', 'string', 'max:255'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after:data_inicio'],
            'limite_inscricao' => ['nullable', 'date', 'before_or_equal:data_inicio'],
            'limite_participantes' => ['nullable', 'integer', 'min:1'],
            'responsavel_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
