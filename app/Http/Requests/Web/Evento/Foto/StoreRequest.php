<?php

namespace App\Http\Requests\Web\Evento\Foto;

use App\Models\Evento;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        /** @var Evento $evento */
        $evento = $this->route('evento');

        return $user->temCargo('administrador')
            || (int) $evento->responsavel_id === (int) $user->id
            || (int) $evento->criado_por_id === (int) $user->id;
    }

    public function rules(): array
    {
        if ($this->isMethod('delete')) {
            return ['foto' => ['prohibited']];
        }

        return [
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }
}
