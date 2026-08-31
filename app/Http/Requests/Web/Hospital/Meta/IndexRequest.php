<?php

namespace App\Http\Requests\Web\Hospital\Meta;

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
            'mes' => $this->input('mes', now()->month),
        ]);
    }

    public function rules(): array
    {
        return [
            'ano' => ['required', 'integer', 'min:2000', 'max:2100'],
            'mes' => ['required', 'integer', 'min:1', 'max:12'],
        ];
    }
}
