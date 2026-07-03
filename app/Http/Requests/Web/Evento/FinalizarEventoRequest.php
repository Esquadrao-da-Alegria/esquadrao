<?php

namespace App\Http\Requests\Web\Evento;

use App\Models\Evento;
use Illuminate\Foundation\Http\FormRequest;

class FinalizarEventoRequest extends FormRequest
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
            'observacoes_finalizacao' => ['nullable', 'string'],
        ];
    }
}