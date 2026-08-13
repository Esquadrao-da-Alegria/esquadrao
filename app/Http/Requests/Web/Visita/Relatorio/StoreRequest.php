<?php

namespace App\Http\Requests\Web\Visita\Relatorio;

use App\Enums\TipoRelatorio;
use App\Models\Ala;
use App\Models\Visita;
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
            'tipo_relatorio'                 => ['required', 'string', Rule::enum(TipoRelatorio::class)],
            'ala_unidade_id'                 => ['nullable', 'integer', 'exists:alas_hospitais,id'],
            'unidades_visitadas'             => ['nullable', 'string', 'max:5000'],
            'resumo'                         => ['required', 'string', 'max:5000'],
            'feedback'                       => ['nullable', 'string', 'max:5000'],
            'quartos_visitados'              => ['nullable', 'integer', 'min:0'],
            'pessoas_impactadas'             => ['nullable', 'integer', 'min:0'],
            'observacao_visitantes_externos' => ['nullable', 'string', 'max:5000'],
            'observacoes_gerais'             => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $alaId = $this->input('ala_unidade_id');

            if (! $alaId) {
                return;
            }

            /** @var Visita|null $visita */
            $visita = $this->route('visita');

            if (! $visita) {
                return;
            }

            if (! $visita->hospital_id) {
                $validator->errors()->add('ala_unidade_id', 'A ala não pertence ao hospital da visita.');
                return;
            }

            $pertence = Ala::query()
                ->whereKey($alaId)
                ->where('hospital_id', $visita->hospital_id)
                ->exists();

            if (! $pertence) {
                $validator->errors()->add('ala_unidade_id', 'A ala não pertence ao hospital da visita.');
            }
        });
    }
}
