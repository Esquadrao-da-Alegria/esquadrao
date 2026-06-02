<?php

namespace App\Http\Requests\Web\Patrocinador;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        // Permitir o envio (ajuste conforme políticas de acesso)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
