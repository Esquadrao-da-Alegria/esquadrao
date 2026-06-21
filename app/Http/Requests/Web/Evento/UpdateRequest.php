<?php

namespace App\Http\Requests\Web\Evento;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('data') && $this->filled('hora_inicio')) {
            $this->merge([
                'data_inicio' => sprintf('%s %s:00', $this->data, $this->hora_inicio),
            ]);
        }

        if ($this->filled('data') && $this->filled('hora_fim')) {
            $this->merge([
                'data_fim' => sprintf('%s %s:00', $this->data, $this->hora_fim),
            ]);
        }
    }

    public function rules(): array
    {
        $eventoId = $this->route('evento')?->id;

        return [
            'tipo'               => ['required', 'string', Rule::in(['OFICINA', 'REUNIAO'])],
            'titulo'             => [
                'required',
                'string',
                'max:255',
                Rule::unique('eventos', 'titulo')
                    ->where('status', 'AGENDADO')
                    ->whereNull('deleted_at')
                    ->ignore($eventoId),
            ],
            'descricao'          => ['required', 'string', 'max:2000'],
            'data'               => ['required', 'date'],
            'hora_inicio'        => ['required', 'date_format:H:i'],
            'hora_fim'           => ['required', 'date_format:H:i'],
            'data_inicio'        => ['required', 'date'],
            'data_fim'           => ['required', 'date', 'after:data_inicio'],
            'complemento'        => ['nullable', 'string', 'max:500'],
            'local_latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'local_longitude'    => ['nullable', 'numeric', 'between:-180,180'],
            'cidade_id'          => ['required', 'integer', 'exists:cidades,id'],
            'status'             => ['required', 'string', Rule::in(['AGENDADO', 'FINALIZADO', 'CANCELADO'])],
            'limite_vagas'       => ['nullable', 'integer', 'min:1'],
            'feedback_habilitado' => ['required', 'boolean'],
            'responsaveis'       => ['required', 'array', 'min:1'],
            'responsaveis.*'     => ['integer', 'exists:users,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            if ($this->filled('data') && $this->filled('hora_inicio') && $this->filled('hora_fim')) {
                $start = sprintf('%s %s:00', $this->data, $this->hora_inicio);
                $end   = sprintf('%s %s:00', $this->data, $this->hora_fim);

                if (strtotime($end) <= strtotime($start)) {
                    $validator->errors()->add('hora_fim', 'A hora de término deve ser posterior à hora de início.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'tipo.required'               => 'O tipo do evento é obrigatório.',
            'tipo.in'                     => 'O tipo do evento deve ser OFICINA ou REUNIAO.',
            'titulo.required'             => 'O título do evento é obrigatório.',
            'titulo.max'                  => 'O título deve ter no máximo :max caracteres.',
            'titulo.unique'               => 'Já existe um evento agendado com este nome.',
            'descricao.required'          => 'A descrição do evento é obrigatória.',
            'data.required'               => 'A data do evento é obrigatória.',
            'hora_inicio.required'        => 'A hora de início é obrigatória.',
            'hora_inicio.date_format'     => 'A hora de início deve estar no formato HH:mm.',
            'hora_fim.required'           => 'A hora de término é obrigatória.',
            'hora_fim.date_format'        => 'A hora de término deve estar no formato HH:mm.',
            'data_fim.after'              => 'A data/hora de término deve ser posterior ao início.',
            'complemento.max'             => 'O complemento deve ter no máximo :max caracteres.',
            'local_latitude.between'      => 'A latitude deve estar entre -90 e 90.',
            'local_longitude.between'     => 'A longitude deve estar entre -180 e 180.',
            'cidade_id.required'          => 'A cidade é obrigatória.',
            'cidade_id.exists'            => 'A cidade informada não existe.',
            'status.required'             => 'O status do evento é obrigatório.',
            'status.in'                   => 'O status deve ser AGENDADO, FINALIZADO ou CANCELADO.',
            'limite_vagas.integer'        => 'O limite de vagas deve ser um número inteiro.',
            'limite_vagas.min'            => 'O limite de vagas deve ser pelo menos 1.',
            'feedback_habilitado.boolean' => 'O campo de feedback deve ser verdadeiro ou falso.',
            'responsaveis.required'      => 'É obrigatório informar ao menos um responsável.',
            'responsaveis.min'           => 'É obrigatório informar ao menos um responsável.',
        ];
    }
}
