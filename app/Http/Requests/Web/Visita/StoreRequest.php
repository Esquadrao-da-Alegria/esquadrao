<?php

namespace App\Http\Requests\Web\Visita;

use App\Enums\VisitaTipo;
use App\Models\Ala;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'hospital_id'    => [
                'required',
                'integer',
                Rule::exists('hospitais', 'id')->where(fn ($q) => $q->where('ativo', true)),
            ],
            'ala_unidade_id' => ['nullable', 'integer', 'exists:alas_hospitais,id'],
            'data'           => ['required', 'date'],
            'hora_inicio'    => ['required', 'date_format:H:i'],
            'hora_fim'       => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'tipo'           => ['required', Rule::enum(VisitaTipo::class)],
            'lider_id'       => ['required', 'integer', 'exists:users,id'],
            'observacoes'    => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $liderId = $this->input('lider_id');

            if ($liderId) {
                $ehVoluntario = User::query()->whereKey($liderId)
                    ->whereHas('cargos', fn ($q) => $q->where('slug', 'voluntario'))
                    ->exists();
                $ehAuth = (int) $liderId === (int) $this->user()?->id;

                if (! $ehVoluntario && ! $ehAuth) {
                    $validator->errors()->add('lider_id', 'O líder deve ser um voluntário cadastrado.');
                }
            }

            $hospitalId = $this->input('hospital_id');
            $alaId      = $this->input('ala_unidade_id');

            if ($alaId && $hospitalId) {
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

    public function messages(): array
    {
        return [
            'hospital_id.required' => 'Selecione um hospital.',
            'hospital_id.exists'   => 'Hospital inválido ou inativo.',
            'hora_fim.after'       => 'O horário de fim deve ser posterior ao início.',
        ];
    }
}
