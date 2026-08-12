<?php

namespace App\Http\Requests\Web\Evento\Ajuste;

use App\Enums\TipoAjusteEvento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->temCargo('administrador') ?? false;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::enum(TipoAjusteEvento::class)],
            'voluntario_id' => ['required', 'integer', 'exists:users,id'],
            'presenca' => ['required_if:tipo,correcao_presenca', 'nullable', Rule::in(['presente', 'ausente'])],
            'justificativa' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }
}
