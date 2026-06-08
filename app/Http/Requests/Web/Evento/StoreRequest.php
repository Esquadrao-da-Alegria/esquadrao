<?php

namespace App\Http\Requests\Web\Evento;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
        return [
            'tipo' => ['required', 'string', 'max:255', Rule::in(['OFICINA', 'REUNIAO'])],
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['required', 'string', 'max:255'],
            'data' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fim' => ['required', 'date_format:H:i'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
            'local' => ['required', 'string', 'max:255'],
            'cidade_id' => ['required', 'integer', 'exists:cidades,id'],
            'limite_vagas' => ['nullable', 'integer', 'min:0'],
            'feedback_habilitado' => ['required', 'boolean'],
            'evento_origem_id' => ['nullable', 'integer', 'exists:eventos,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('data') && $this->filled('hora_inicio') && $this->filled('hora_fim')) {
                $start = sprintf('%s %s:00', $this->data, $this->hora_inicio);
                $end = sprintf('%s %s:00', $this->data, $this->hora_fim);

                if (strtotime($end) < strtotime($start)) {
                    $validator->errors()->add('hora_fim', 'A hora de término deve ser igual ou posterior à hora de início.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'tipo.required' => 'O tipo do evento é obrigatório.',
            'tipo.in' => 'O tipo do evento deve ser OFICINA ou REUNIAO.',
            'titulo.required' => 'O título do evento é obrigatório.',
            'titulo.max' => 'O título deve ter no máximo :max caracteres.',
            'descricao.required' => 'A descrição do evento é obrigatória.',
            'descricao.max' => 'A descrição deve ter no máximo :max caracteres.',
            'data.required' => 'A data do evento é obrigatória.',
            'data.date' => 'A data do evento deve ser uma data válida.',
            'hora_inicio.required' => 'A hora de início é obrigatória.',
            'hora_inicio.date_format' => 'A hora de início deve estar no formato HH:mm.',
            'hora_fim.required' => 'A hora de término é obrigatória.',
            'hora_fim.date_format' => 'A hora de término deve estar no formato HH:mm.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_inicio.date' => 'A data de início deve ser uma data válida.',
            'data_fim.required' => 'A data de término é obrigatória.',
            'data_fim.date' => 'A data de término deve ser uma data válida.',
            'data_fim.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',
            'local.required' => 'O local do evento é obrigatório.',
            'local.max' => 'O local deve ter no máximo :max caracteres.',
            'cidade_id.required' => 'A cidade é obrigatória.',
            'cidade_id.integer' => 'A cidade deve ser um identificador válido.',
            'cidade_id.exists' => 'A cidade informada não existe.',
            'status.required' => 'O status do evento é obrigatório.',
            'status.in' => 'O status deve ser AGENDADO, FINALIZADO, CANCELADO ou TRANSFERIDO.',
            'limite_vagas.integer' => 'O limite de vagas deve ser um número inteiro.',
            'limite_vagas.min' => 'O limite de vagas não pode ser negativo.',
            'feedback_habilitado.required' => 'O campo de feedback é obrigatório.',
            'feedback_habilitado.boolean' => 'O campo de feedback deve ser verdadeiro ou falso.',
            'evento_origem_id.integer' => 'O evento de origem deve ser um identificador válido.',
            'evento_origem_id.exists' => 'O evento de origem informado não existe.',
        ];
    }
}
