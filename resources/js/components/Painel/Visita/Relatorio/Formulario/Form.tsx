import { painelInputClass, painelLabelClass } from '@/lib/painelFormFieldClasses'
import type { FC } from 'react'

export type TipoRelatorio = 'artista' | 'paisana' | 'geral'

export interface RelatorioFormValues {
    tipo_relatorio: TipoRelatorio | ''
    resumo: string
    feedback: string
    ala_unidade: string
    quartos_visitados: number | ''
    pessoas_impactadas: number | ''
    observacao_visitantes_externos: string
    observacoes_gerais: string
}

interface Props {
    data: RelatorioFormValues
    errors: Record<string, string | undefined>
    foraDoPrazoAviso: boolean
    onFieldChange: <K extends keyof RelatorioFormValues>(campo: K, valor: RelatorioFormValues[K]) => void
}

const Form: FC<Props> = ({ data, errors, foraDoPrazoAviso, onFieldChange }) => (
    <div className="space-y-6">
        <div className={`rounded-xl border px-4 py-3 text-sm ${foraDoPrazoAviso ? 'border-orange-200 bg-orange-50 text-orange-900' : 'border-amber-200 bg-amber-50 text-amber-900'}`}>
            {foraDoPrazoAviso
                ? 'Atenção: o prazo recomendado para envio do relatório era até 48h após a visita. Mesmo assim, você pode preencher e salvar normalmente.'
                : 'Lembrete: o ideal é enviar o relatório até 48h após a visita.'}
        </div>

        <div>
            <label htmlFor="tipo_relatorio" className={painelLabelClass}>Tipo de relatório *</label>
            <select id="tipo_relatorio" required value={data.tipo_relatorio} onChange={(e) => onFieldChange('tipo_relatorio', e.target.value as TipoRelatorio)} className={painelInputClass}>
                <option value="">Selecione...</option>
                <option value="artista">Artista</option>
                <option value="paisana">Paisana</option>
                <option value="geral">Geral</option>
            </select>
            {errors.tipo_relatorio && <p className="mt-1 text-sm text-red-600">{errors.tipo_relatorio}</p>}
        </div>

        <div>
            <label htmlFor="resumo" className={painelLabelClass}>Resumo *</label>
            <textarea id="resumo" required rows={5} value={data.resumo} onChange={(e) => onFieldChange('resumo', e.target.value)} className={painelInputClass} />
            {errors.resumo && <p className="mt-1 text-sm text-red-600">{errors.resumo}</p>}
        </div>

        <div>
            <label htmlFor="feedback" className={painelLabelClass}>Feedback</label>
            <textarea id="feedback" rows={4} value={data.feedback} onChange={(e) => onFieldChange('feedback', e.target.value)} className={painelInputClass} />
        </div>

        <div>
            <label htmlFor="ala_unidade" className={painelLabelClass}>Ala / unidade</label>
            <input id="ala_unidade" value={data.ala_unidade} onChange={(e) => onFieldChange('ala_unidade', e.target.value)} className={painelInputClass} />
        </div>

        <div className="grid gap-6 sm:grid-cols-2">
            <div>
                <label htmlFor="quartos_visitados" className={painelLabelClass}>Quartos visitados</label>
                <input id="quartos_visitados" type="number" min={0} value={data.quartos_visitados} onChange={(e) => onFieldChange('quartos_visitados', e.target.value === '' ? '' : Number(e.target.value))} className={painelInputClass} />
            </div>
            <div>
                <label htmlFor="pessoas_impactadas" className={painelLabelClass}>Pessoas impactadas</label>
                <input id="pessoas_impactadas" type="number" min={0} value={data.pessoas_impactadas} onChange={(e) => onFieldChange('pessoas_impactadas', e.target.value === '' ? '' : Number(e.target.value))} className={painelInputClass} />
            </div>
        </div>

        <div>
            <label htmlFor="observacao_visitantes_externos" className={painelLabelClass}>Observação sobre visitantes externos</label>
            <textarea id="observacao_visitantes_externos" rows={3} value={data.observacao_visitantes_externos} onChange={(e) => onFieldChange('observacao_visitantes_externos', e.target.value)} className={painelInputClass} />
        </div>

        <div>
            <label htmlFor="observacoes_gerais" className={painelLabelClass}>Observações gerais</label>
            <textarea id="observacoes_gerais" rows={3} value={data.observacoes_gerais} onChange={(e) => onFieldChange('observacoes_gerais', e.target.value)} className={painelInputClass} />
        </div>
    </div>
)

export default Form
