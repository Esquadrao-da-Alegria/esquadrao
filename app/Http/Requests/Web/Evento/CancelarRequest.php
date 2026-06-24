<?php

namespace App\Http\Requests\Web\Evento;

use Illuminate\Foundation\Http\FormRequest;

class CancelarRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->temCargo('administrador') === true; }

    public function rules(): array
    {
        return ['motivo_cancelamento' => ['required', 'string']];
    }
}
