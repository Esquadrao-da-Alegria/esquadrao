<?php

namespace App\Http\Requests\Web\Visita\Agenda\Liberacao;

// HELPERS
use App\Helpers\User as UserHelper;

// HTTP
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        return UserHelper::ehGestor($user);
    }

    public function rules(): array
    {
        return [
            'ano'      => ['required', 'integer', 'min:2000', 'max:2100'],
            'mes'      => ['required', 'integer', 'min:1', 'max:12'],
            'cidade_id' => ['required', 'integer', 'exists:cidades,id'],
            'liberado' => ['required', 'boolean'],
        ];
    }
}
