<?php

namespace App\Http\Requests\Web\Visita\Ajuste;

use App\Enums\TipoAjusteContabilizacao;
use App\Enums\TipoParticipacao;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->cargos->contains('slug', 'administrador') ?? false;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::enum(TipoAjusteContabilizacao::class)],
            'voluntario_id' => ['required_if:tipo,correcao_participacao', 'nullable', 'integer', 'exists:users,id'],
            'tipo_participacao' => ['required_if:tipo,correcao_participacao', 'nullable', Rule::enum(TipoParticipacao::class)],
            'relatorio_id' => ['required_if:tipo,aceite_relatorio_fora_prazo', 'nullable', 'integer', 'exists:visitas_relatorios,id'],
            'justificativa' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }
}
