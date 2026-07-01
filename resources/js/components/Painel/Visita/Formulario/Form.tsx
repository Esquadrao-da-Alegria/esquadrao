import { painelInputClass, painelLabelClass } from '@/lib/painelFormFieldClasses'
import { labelStatus, labelTipo, VISITA_STATUS, VISITA_TIPOS } from '@/lib/visita'
import type { AlaHospital, Hospital, User, VisitaStatus, VisitaTipo } from '@/types'
import { type FC, useMemo } from 'react'

export interface VisitaFormValues {
    hospital_id: number | ''
    ala_unidade_id: number | '' | null
    data: string
    hora_inicio: string
    hora_fim: string
    tipo: VisitaTipo | ''
    lider_id: number | ''
    status?: VisitaStatus
    observacoes: string
}

interface Props {
    data: VisitaFormValues
    errors: Record<string, string | undefined>
    mode: 'create' | 'edit'
    hospitais: Hospital[]
    lideres: User[]
    onFieldChange: <K extends keyof VisitaFormValues>(campo: K, valor: VisitaFormValues[K]) => void
}

const Form: FC<Props> = ({ data, errors, mode, hospitais, lideres, onFieldChange }) => {
    const alasDisponiveis = useMemo(() => {
        if (!data.hospital_id) return [] as AlaHospital[]
        const hospital = hospitais.find((h) => h.id === Number(data.hospital_id))
        return hospital?.alas ?? []
    }, [data.hospital_id, hospitais])

    const handleHospitalChange = (valor: number | '') => {
        onFieldChange('hospital_id', valor)
        onFieldChange('ala_unidade_id', null)
    }

    return (
        <div className="space-y-6">
            <div>
                <label htmlFor="hospital_id" className={painelLabelClass}>Hospital *</label>
                <select
                    id="hospital_id"
                    name="hospital_id"
                    required
                    disabled={mode === 'edit'}
                    value={data.hospital_id}
                    onChange={(e) => handleHospitalChange(e.target.value ? Number(e.target.value) : '')}
                    className={painelInputClass}
                >
                    <option value="">Selecione...</option>
                    {hospitais.map((h) => (
                        <option key={h.id} value={h.id}>{h.nome}</option>
                    ))}
                </select>
                {errors.hospital_id ? <p className="mt-1 text-sm text-red-600">{errors.hospital_id}</p> : null}
            </div>

            <div>
                <label htmlFor="ala_unidade_id" className={painelLabelClass}>Ala / Unidade</label>
                <select
                    id="ala_unidade_id"
                    name="ala_unidade_id"
                    disabled={mode === 'edit' || !data.hospital_id}
                    value={data.ala_unidade_id ?? ''}
                    onChange={(e) => onFieldChange('ala_unidade_id', e.target.value ? Number(e.target.value) : null)}
                    className={painelInputClass}
                >
                    <option value="">Nenhuma</option>
                    {alasDisponiveis.map((a) => (
                        <option key={a.id} value={a.id}>{a.nome}</option>
                    ))}
                </select>
                {errors.ala_unidade_id ? <p className="mt-1 text-sm text-red-600">{errors.ala_unidade_id}</p> : null}
            </div>

            <div className="grid gap-6 sm:grid-cols-3">
                <div>
                    <label htmlFor="data" className={painelLabelClass}>Data *</label>
                    <input type="date" id="data" required value={data.data}
                        onChange={(e) => onFieldChange('data', e.target.value)} className={painelInputClass} />
                    {errors.data ? <p className="mt-1 text-sm text-red-600">{errors.data}</p> : null}
                </div>
                <div>
                    <label htmlFor="hora_inicio" className={painelLabelClass}>Início *</label>
                    <input type="time" id="hora_inicio" required value={data.hora_inicio}
                        onChange={(e) => onFieldChange('hora_inicio', e.target.value)} className={painelInputClass} />
                    {errors.hora_inicio ? <p className="mt-1 text-sm text-red-600">{errors.hora_inicio}</p> : null}
                </div>
                <div>
                    <label htmlFor="hora_fim" className={painelLabelClass}>Fim *</label>
                    <input type="time" id="hora_fim" required value={data.hora_fim}
                        onChange={(e) => onFieldChange('hora_fim', e.target.value)} className={painelInputClass} />
                    {errors.hora_fim ? <p className="mt-1 text-sm text-red-600">{errors.hora_fim}</p> : null}
                </div>
            </div>

            <div>
                <label htmlFor="tipo" className={painelLabelClass}>Tipo *</label>
                <select id="tipo" required value={data.tipo}
                    onChange={(e) => onFieldChange('tipo', e.target.value as VisitaTipo)} className={painelInputClass}>
                    <option value="">Selecione...</option>
                    {VISITA_TIPOS.map((t) => (
                        <option key={t} value={t}>{labelTipo(t)}</option>
                    ))}
                </select>
                {errors.tipo ? <p className="mt-1 text-sm text-red-600">{errors.tipo}</p> : null}
            </div>

            <div>
                <label htmlFor="lider_id" className={painelLabelClass}>Líder *</label>
                <select id="lider_id" required value={data.lider_id}
                    onChange={(e) => onFieldChange('lider_id', e.target.value ? Number(e.target.value) : '')}
                    className={painelInputClass}>
                    <option value="">Selecione...</option>
                    {lideres.map((l) => (
                        <option key={l.id} value={l.id}>{l.name}</option>
                    ))}
                </select>
                {errors.lider_id ? <p className="mt-1 text-sm text-red-600">{errors.lider_id}</p> : null}
            </div>

            {mode === 'edit' && data.status ? (
                <div>
                    <label htmlFor="status" className={painelLabelClass}>Status *</label>
                    <select id="status" required value={data.status}
                        onChange={(e) => onFieldChange('status', e.target.value as VisitaStatus)}
                        className={painelInputClass}>
                        {VISITA_STATUS.map((s) => (
                            <option key={s} value={s}>{labelStatus(s)}</option>
                        ))}
                    </select>
                    {errors.status ? <p className="mt-1 text-sm text-red-600">{errors.status}</p> : null}
                </div>
            ) : null}

            <div>
                <label htmlFor="observacoes" className={painelLabelClass}>Observações</label>
                <textarea id="observacoes" rows={4} value={data.observacoes}
                    onChange={(e) => onFieldChange('observacoes', e.target.value)}
                    className={`${painelInputClass} resize-none`} />
            </div>
        </div>
    )
}

export default Form
