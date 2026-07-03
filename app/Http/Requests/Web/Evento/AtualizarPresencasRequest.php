<?php

namespace App\Http\Requests\Web\Evento;

use App\Models\Evento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AtualizarPresencasRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Evento $evento */
        $evento = $this->route('evento');
        $user = $this->user();

        return $user->temCargo('administrador') || $evento->responsavel_id === $user->id;
    }

    public function rules(): array
    {
        return [
            'participantes' => ['required', 'array'],
            'participantes.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'participantes.*.presenca' => ['nullable', Rule::in(['presente', 'ausente'])],
            'participantes.*.observacao_presenca' => ['nullable', 'string'],
        ];
    }
}