<?php

namespace App\Http\Requests\Web\Evento\Ajuste;

use App\Enums\TipoAjusteEvento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) return false;
        if ($user->temCargo('administrador')) return true;

        $evento = $this->route('evento');
        return $evento && ($evento->responsavel_id === $user->id || $evento->criado_por_id === $user->id);
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
