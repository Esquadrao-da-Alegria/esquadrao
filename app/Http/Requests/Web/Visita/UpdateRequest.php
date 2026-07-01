<?php

namespace App\Http\Requests\Web\Visita;

use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'data'        => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fim'    => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'tipo'        => ['required', Rule::enum(VisitaTipo::class)],
            'lider_id'    => ['required', 'integer', 'exists:users,id'],
            'status'      => ['required', Rule::enum(VisitaStatus::class)],
            'observacoes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $liderId = $this->input('lider_id');

            if ($liderId) {
                $liderValido = User::query()
                    ->whereKey($liderId)
                    ->where('status', User::STATUS_ATIVO)
                    ->whereNotNull('voluntario_id')
                    ->exists();

                if (! $liderValido) {
                    $validator->errors()->add('lider_id', 'O líder deve ser um voluntário cadastrado.');
                }
            }
        });
    }
}
