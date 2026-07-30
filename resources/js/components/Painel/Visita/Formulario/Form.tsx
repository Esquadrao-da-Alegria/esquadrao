// REACT
import { type FC, useMemo, useState } from 'react'

// UI
import { painelInputClass, painelLabelClass } from '@/lib/painelFormFieldClasses'
import { labelStatus, labelTipo, VISITA_STATUS, VISITA_TIPOS } from '@/lib/visita'

// TIPOS
import type { AlaHospital, Cidade, Hospital, User } from '@/types'
import type { DadosFormulario, VisitaStatus, VisitaTipo } from '@/types/visita'

interface Props {
    data: DadosFormulario
    errors: Record<string, string | undefined>
    mode: 'create' | 'edit'
    hospitais: Hospital[]
    cidades?: Cidade[]
    lideres: User[]
    onCampoChange: <K extends keyof DadosFormulario>(campo: K, valor: DadosFormulario[K]) => void
}

const Form: FC<Props> = ({ data, errors, mode, hospitais, cidades = [], lideres, onCampoChange }) => {
    const hospitalSelecionado = useMemo(() => {
        if (!data.hospital_id) return null
        return hospitais.find((h) => h.id === Number(data.hospital_id)) ?? null
    }, [data.hospital_id, hospitais])

    const [cidadeFiltroId, setCidadeFiltroId] = useState<number | ''>(
        hospitalSelecionado?.cidade_id ?? '',
    )

    const hospitaisFiltrados = useMemo(() => {
        if (!cidadeFiltroId) return hospitais
        return hospitais.filter((h) => Number(h.cidade_id) === Number(cidadeFiltroId))
    }, [cidadeFiltroId, hospitais])

    const alasDisponiveis = useMemo(() => {
        return hospitalSelecionado?.alas ?? []
    }, [hospitalSelecionado])

    const handleCidadeChange = (valor: number | '') => {
        setCidadeFiltroId(valor)
        onCampoChange('hospital_id', '')
        onCampoChange('ala_unidade_id', null)
    }

    const handleHospitalChange = (valor: number | '') => {
        onCampoChange('hospital_id', valor)
        onCampoChange('ala_unidade_id', null)
    }

    return (
        <div className="space-y-6">
            {cidades.length > 0 && (
                <div>
                    <label htmlFor="cidade_filtro_id" className={painelLabelClass}>Filtrar por Cidade</label>
                    <select
                        id="cidade_filtro_id"
                        name="cidade_filtro_id"
                        disabled={mode === 'edit'}
                        value={cidadeFiltroId}
                        onChange={(e) => handleCidadeChange(e.target.value ? Number(e.target.value) : '')}
                        className={painelInputClass}
                    >
                        <option value="">Todas as Cidades</option>
                        {cidades.map((c) => (
                            <option key={c.id} value={c.id}>{c.nome}</option>
                        ))}
                    </select>
                </div>
            )}

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
                    <option value="">
                        {cidadeFiltroId
                            ? 'Selecione um hospital da cidade...'
                            : 'Selecione...'}
                    </option>
                    {hospitaisFiltrados.map((h) => (
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
                    onChange={(e) => onCampoChange('ala_unidade_id', e.target.value ? Number(e.target.value) : null)}
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
                        onChange={(e) => onCampoChange('data', e.target.value)} className={painelInputClass} />
                    {errors.data ? <p className="mt-1 text-sm text-red-600">{errors.data}</p> : null}
                </div>
                <div>
                    <label htmlFor="hora_inicio" className={painelLabelClass}>Início *</label>
                    <input type="time" id="hora_inicio" required value={data.hora_inicio}
                        onChange={(e) => onCampoChange('hora_inicio', e.target.value)} className={painelInputClass} />
                    {errors.hora_inicio ? <p className="mt-1 text-sm text-red-600">{errors.hora_inicio}</p> : null}
                </div>
                <div>
                    <label htmlFor="hora_fim" className={painelLabelClass}>Fim *</label>
                    <input type="time" id="hora_fim" required value={data.hora_fim}
                        onChange={(e) => onCampoChange('hora_fim', e.target.value)} className={painelInputClass} />
                    {errors.hora_fim ? <p className="mt-1 text-sm text-red-600">{errors.hora_fim}</p> : null}
                </div>
            </div>

            <div>
                <label htmlFor="tipo" className={painelLabelClass}>Tipo *</label>
                <select id="tipo" required value={data.tipo}
                    onChange={(e) => onCampoChange('tipo', e.target.value as VisitaTipo)} className={painelInputClass}>
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
                    onChange={(e) => onCampoChange('lider_id', e.target.value ? Number(e.target.value) : '')}
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
                        onChange={(e) => onCampoChange('status', e.target.value as VisitaStatus)}
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
                    onChange={(e) => onCampoChange('observacoes', e.target.value)}
                    className={`${painelInputClass} resize-none`} />
            </div>
        </div>
    )
}

export default Form
