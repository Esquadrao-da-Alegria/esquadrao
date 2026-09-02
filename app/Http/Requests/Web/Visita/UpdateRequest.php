<?php

namespace App\Http\Requests\Web\Visita;

use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Ala;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use App\Services\Visita\Service;
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
        /** @var Visita $visita */
        $visita = $this->route('visita');

        if (! app(Service::class)->podeEditarVisita($this->user(), $visita)) {
            return [];
        }

        return [
            'hospital_id'    => [
                Rule::requiredIf(fn () => in_array($this->input('tipo'), [VisitaTipo::Hospital->value, VisitaTipo::Residencia->value, 'hospital', 'residencia'], true)
                    && ($this->has('hospital_id')
                        ? ! $this->filled('hospital_id')
                        : ! $this->route('visita')->hospital_id)),
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
        /** @var Visita $visita */
        $visita = $this->route('visita');

        if (! app(Service::class)->podeEditarVisita($this->user(), $visita)) {
            return;
        }

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

            /** @var Visita $visita */
            $visita = $this->route('visita');
            $hospitalId = $this->has('hospital_id')
                ? $this->input('hospital_id')
                : $visita->hospital_id;
            $alaId      = $this->input('ala_unidade_id');

            $user = $this->user();
            resolverUsuario($user);

            $ehCoordenadorLocal = $user->cargos->contains('slug', 'coordenador_local');
            $possuiEscopoAmplo = $user->cargos->contains(
                fn ($cargo) => in_array($cargo->slug, ['administrador', 'diretor', 'coordenador_geral'], true)
            );

            if ($hospitalId && $ehCoordenadorLocal && ! $possuiEscopoAmplo) {
                $cidadeHospital = Hospital::query()
                    ->whereKey($hospitalId)
                    ->value('cidade_id');

                if (! $user->voluntario?->cidade_base_id
                    || (int) $user->voluntario->cidade_base_id !== (int) $cidadeHospital) {
                    $validator->errors()->add('hospital_id', 'O hospital deve pertencer à sua cidade-base.');
                }
            }

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
