// REACT
import { type FC } from 'react';

// UI
import ContextoVisita from '@/components/Painel/Visita/Relatorio/Contexto/Show';
import {
    painelInputClass,
    painelLabelClass,
} from '@/lib/painelFormFieldClasses';
import { labelTipoRelatorio, TIPOS_RELATORIO } from '@/lib/visita';

// TIPOS
import type { DadosFormulario } from '@/types/relatorio';
import type { TipoRelatorio, Visita } from '@/types/visita';

export type RelatorioFormErrors = Record<string, string | undefined>;

interface Props {
    visita: Visita;
    data: DadosFormulario;
    errors: RelatorioFormErrors;
    foraDoPrazoAviso: boolean;
    onFieldChange: <K extends keyof DadosFormulario>(
        campo: K,
        valor: DadosFormulario[K],
    ) => void;
}

const Form: FC<Props> = ({
    visita,
    data,
    errors,
    foraDoPrazoAviso,
    onFieldChange,
}) => {
    return (
        <div className="space-y-6">
            <ContextoVisita visita={visita} />

            <div
                className={`rounded-xl border px-4 py-3 text-sm ${
                    foraDoPrazoAviso
                        ? 'border-orange-200 bg-orange-50 text-orange-900'
                        : 'border-amber-200 bg-amber-50 text-amber-900'
                }`}
            >
                {foraDoPrazoAviso
                    ? 'Atenção: o prazo recomendado de 48h após o fim da visita já passou. Mesmo assim você pode salvar o relatório.'
                    : 'Lembrete: o ideal é enviar o relatório até 48h após o fim da visita.'}
            </div>

            <div>
                <label htmlFor="tipo_relatorio" className={painelLabelClass}>
                    Tipo de relatório *
                </label>
                <select
                    id="tipo_relatorio"
                    name="tipo_relatorio"
                    required
                    value={data.tipo_relatorio}
                    onChange={(e) =>
                        onFieldChange(
                            'tipo_relatorio',
                            e.target.value as TipoRelatorio,
                        )
                    }
                    className={painelInputClass}
                >
                    <option value="">Selecione...</option>
                    {TIPOS_RELATORIO.map((tipo) => (
                        <option key={tipo} value={tipo}>
                            {labelTipoRelatorio(tipo)}
                        </option>
                    ))}
                </select>
                {errors.tipo_relatorio ? (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.tipo_relatorio}
                    </p>
                ) : null}
            </div>

            <div>
                <label htmlFor="ala_unidade_id" className={painelLabelClass}>
                    Ala / Unidade
                </label>
                <select
                    id="ala_unidade_id"
                    name="ala_unidade_id"
                    value={data.ala_unidade_id ?? ''}
                    onChange={(e) =>
                        onFieldChange(
                            'ala_unidade_id',
                            e.target.value ? Number(e.target.value) : null,
                        )
                    }
                    className={painelInputClass}
                >
                    <option value="">Nenhuma</option>
                    {(visita.hospital?.alas ?? []).map((ala) => (
                        <option key={ala.id} value={ala.id}>
                            {ala.nome}
                        </option>
                    ))}
                </select>
                {errors.ala_unidade_id ? (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.ala_unidade_id}
                    </p>
                ) : null}
            </div>

            <div>
                <label
                    htmlFor="unidades_visitadas"
                    className={painelLabelClass}
                >
                    Unidades / Alas visitadas (opcional)
                </label>
                <input
                    id="unidades_visitadas"
                    name="unidades_visitadas"
                    type="text"
                    placeholder="Ex: Pediatria, UTI, 3º andar"
                    value={data.unidades_visitadas}
                    onChange={(e) =>
                        onFieldChange('unidades_visitadas', e.target.value)
                    }
                    className={painelInputClass}
                />
                {errors.unidades_visitadas ? (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.unidades_visitadas}
                    </p>
                ) : null}
            </div>

            <div>
                <label htmlFor="resumo" className={painelLabelClass}>
                    Resumo *
                </label>
                <textarea
                    id="resumo"
                    name="resumo"
                    required
                    rows={5}
                    value={data.resumo}
                    onChange={(e) => onFieldChange('resumo', e.target.value)}
                    className={painelInputClass}
                />
                {errors.resumo ? (
                    <p className="mt-1 text-sm text-red-600">{errors.resumo}</p>
                ) : null}
            </div>

            <div>
                <label htmlFor="feedback" className={painelLabelClass}>
                    Feedback (3 pontos)
                </label>
                <textarea
                    id="feedback"
                    name="feedback"
                    rows={4}
                    value={data.feedback}
                    onChange={(e) => onFieldChange('feedback', e.target.value)}
                    className={painelInputClass}
                />
                {errors.feedback ? (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.feedback}
                    </p>
                ) : null}
            </div>

            <div className="grid gap-6 sm:grid-cols-2">
                <div>
                    <label
                        htmlFor="quartos_visitados"
                        className={painelLabelClass}
                    >
                        Quartos visitados
                    </label>
                    <input
                        id="quartos_visitados"
                        name="quartos_visitados"
                        type="number"
                        min={0}
                        value={data.quartos_visitados}
                        onChange={(e) =>
                            onFieldChange(
                                'quartos_visitados',
                                e.target.value === ''
                                    ? ''
                                    : Number(e.target.value),
                            )
                        }
                        className={painelInputClass}
                    />
                    {errors.quartos_visitados ? (
                        <p className="mt-1 text-sm text-red-600">
                            {errors.quartos_visitados}
                        </p>
                    ) : null}
                </div>
                <div>
                    <label
                        htmlFor="pessoas_impactadas"
                        className={painelLabelClass}
                    >
                        Pessoas impactadas
                    </label>
                    <input
                        id="pessoas_impactadas"
                        name="pessoas_impactadas"
                        type="number"
                        min={0}
                        value={data.pessoas_impactadas}
                        onChange={(e) =>
                            onFieldChange(
                                'pessoas_impactadas',
                                e.target.value === ''
                                    ? ''
                                    : Number(e.target.value),
                            )
                        }
                        className={painelInputClass}
                    />
                    {errors.pessoas_impactadas ? (
                        <p className="mt-1 text-sm text-red-600">
                            {errors.pessoas_impactadas}
                        </p>
                    ) : null}
                </div>
            </div>

            <div>
                <label
                    htmlFor="observacao_visitantes_externos"
                    className={painelLabelClass}
                >
                    Observação sobre visitantes externos
                </label>
                <textarea
                    id="observacao_visitantes_externos"
                    name="observacao_visitantes_externos"
                    rows={3}
                    value={data.observacao_visitantes_externos}
                    onChange={(e) =>
                        onFieldChange(
                            'observacao_visitantes_externos',
                            e.target.value,
                        )
                    }
                    className={painelInputClass}
                />
                {errors.observacao_visitantes_externos ? (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.observacao_visitantes_externos}
                    </p>
                ) : null}
            </div>

            <div>
                <label
                    htmlFor="observacoes_gerais"
                    className={painelLabelClass}
                >
                    Observações gerais
                </label>
                <textarea
                    id="observacoes_gerais"
                    name="observacoes_gerais"
                    rows={3}
                    value={data.observacoes_gerais}
                    onChange={(e) =>
                        onFieldChange('observacoes_gerais', e.target.value)
                    }
                    className={painelInputClass}
                />
                {errors.observacoes_gerais ? (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.observacoes_gerais}
                    </p>
                ) : null}
            </div>
        </div>
    );
};

export default Form;
