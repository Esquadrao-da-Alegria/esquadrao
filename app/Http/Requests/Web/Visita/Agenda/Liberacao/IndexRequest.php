<?php

namespace App\Http\Requests\Web\Visita\Agenda\Liberacao;

// HELPERS
use App\Helpers\User as UserHelper;

// HTTP
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        return UserHelper::ehGestor($user);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ano' => $this->input('ano', now()->year),
        ]);
    }

    public function rules(): array
    {
        return [
            'ano' => ['required', 'integer', 'min:2000', 'max:2100'],
        ];
    }
}
