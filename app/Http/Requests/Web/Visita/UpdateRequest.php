<?php

namespace App\Http\Requests\Web\Visita;

use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Ala;
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
            'hospital_id'    => [
                Rule::requiredIf(fn () => in_array($this->input('tipo'), [VisitaTipo::Hospital->value, VisitaTipo::Residencia->value, 'hospital', 'residencia'], true)),
                'nullable',
                'integer',
                Rule::exists('hospitais', 'id')->where(fn ($q) => $q->where('ativo', true)),
            ],
            'ala_unidade_id'       => ['nullable', 'integer', 'exists:alas_hospitais,id'],
            'data'                 => ['required', 'date'],
            'hora_inicio'          => ['required', 'date_format:H:i'],
            'hora_fim'             => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'tipo'                 => ['required', Rule::enum(VisitaTipo::class)],
            'limite_participantes' => ['nullable', 'integer', 'min:1'],
            'lider_id'             => ['required', 'integer', 'exists:users,id'],
            'status'               => ['required', Rule::enum(VisitaStatus::class)],
            'observacoes'          => ['nullable', 'string'],
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

            $hospitalId = $this->input('hospital_id');
            $alaId      = $this->input('ala_unidade_id');

            if ($alaId && ! $hospitalId) {
                $validator->errors()->add('ala_unidade_id', 'A ala não pode ser vinculada a uma visita sem hospital.');
            } elseif ($alaId && $hospitalId) {
                $pertence = Ala::query()
                    ->whereKey($alaId)
                    ->where('hospital_id', $hospitalId)
                    ->exists();

                if (! $pertence) {
                    $validator->errors()->add('ala_unidade_id', 'A ala não pertence ao hospital selecionado.');
                }
            }
        });
    }
}
