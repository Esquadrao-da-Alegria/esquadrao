import { painelInputClass, painelLabelClass } from '@/lib/painelFormFieldClasses'
import { EVENTO_TIPOS, labelTipo } from '@/lib/evento'
import type { Cidade, EventoTipo, User } from '@/types'
import { type FC } from 'react'

const OPCOES_HORARIOS = (() => {
    const lista: string[] = []
    for (let h = 6; h <= 23; h++) {
        const hh = String(h).padStart(2, '0')
        lista.push(`${hh}:00`, `${hh}:15`, `${hh}:30`, `${hh}:45`)
    }
    return lista
})()

export interface EventoFormValues {
    titulo: string
    tipo: EventoTipo | ''
    descricao: string
    local: string
    cidade_id: string
    data: string
    hora_inicio: string
    data_fim: string
    sem_hora_fim: boolean
    hora_fim: string
    limite_inscricao_data: string
    limite_inscricao_hora: string
    limite_participantes: string
    responsavel_id: number | ''
}

interface Props {
    data: EventoFormValues
    errors: Record<string, string | undefined>
    responsaveis: User[]
    cidades: Cidade[]
    onFieldChange: <K extends keyof EventoFormValues>(campo: K, valor: EventoFormValues[K]) => void
}

const Form: FC<Props> = ({ data, errors, responsaveis, cidades, onFieldChange }) => {
    return (
        <div className="space-y-6">
            <div>
                <label htmlFor="titulo" className={painelLabelClass}>Título *</label>
                <input
                    id="titulo"
                    name="titulo"
                    required
                    value={data.titulo}
                    onChange={(e) => onFieldChange('titulo', e.target.value)}
                    className={painelInputClass}
                />
                {errors.titulo ? <p className="mt-1 text-sm text-red-600">{errors.titulo}</p> : null}
            </div>

            <div>
                <label htmlFor="tipo" className={painelLabelClass}>Tipo *</label>
                <select
                    id="tipo"
                    name="tipo"
                    required
                    value={data.tipo}
                    onChange={(e) => onFieldChange('tipo', e.target.value as EventoTipo)}
                    className={painelInputClass}
                >
                    <option value="">Selecione...</option>
                    {EVENTO_TIPOS.map((t) => (
                        <option key={t} value={t}>{labelTipo(t)}</option>
                    ))}
                </select>
                {errors.tipo ? <p className="mt-1 text-sm text-red-600">{errors.tipo}</p> : null}
            </div>

            <div>
                <label htmlFor="descricao" className={painelLabelClass}>Descrição</label>
                <textarea
                    id="descricao"
                    name="descricao"
                    rows={4}
                    value={data.descricao}
                    onChange={(e) => onFieldChange('descricao', e.target.value)}
                    className={`${painelInputClass} resize-none`}
                />
                {errors.descricao ? <p className="mt-1 text-sm text-red-600">{errors.descricao}</p> : null}
            </div>

            <div>
                <label htmlFor="cidade_id" className={painelLabelClass}>Cidade *</label>
                <select
                    id="cidade_id"
                    name="cidade_id"
                    required
                    value={data.cidade_id}
                    onChange={(e) => onFieldChange('cidade_id', e.target.value)}
                    className={painelInputClass}
                >
                    <option value="">Selecione...</option>
                    {cidades.map((c) => (
                        <option key={c.id} value={c.id}>{c.nome}</option>
                    ))}
                </select>
                {errors.cidade_id ? <p className="mt-1 text-sm text-red-600">{errors.cidade_id}</p> : null}
            </div>

            <div>
                <label htmlFor="local" className={painelLabelClass}>Local (complemento)</label>
                <input
                    id="local"
                    name="local"
                    value={data.local}
                    onChange={(e) => onFieldChange('local', e.target.value)}
                    className={painelInputClass}
                />
                {errors.local ? <p className="mt-1 text-sm text-red-600">{errors.local}</p> : null}
            </div>

            <div className="grid gap-6 sm:grid-cols-2">
                <div>
                    <label htmlFor="data" className={painelLabelClass}>Data início *</label>
                    <input
                        type="date"
                        id="data"
                        name="data"
                        required
                        value={data.data}
                        onChange={(e) => onFieldChange('data', e.target.value)}
                        className={painelInputClass}
                    />
                    {errors.data ? <p className="mt-1 text-sm text-red-600">{errors.data}</p> : null}
                    {errors.data_inicio ? <p className="mt-1 text-sm text-red-600">{errors.data_inicio}</p> : null}
                </div>
                <div>
                    <label htmlFor="hora_inicio" className={painelLabelClass}>Horário início *</label>
                    <select
                        id="hora_inicio"
                        name="hora_inicio"
                        required
                        value={data.hora_inicio}
                        onChange={(e) => onFieldChange('hora_inicio', e.target.value)}
                        className={painelInputClass}
                    >
                        <option value="">Selecione...</option>
                        {data.hora_inicio && !OPCOES_HORARIOS.includes(data.hora_inicio) && (
                            <option value={data.hora_inicio}>{data.hora_inicio}</option>
                        )}
                        {OPCOES_HORARIOS.map((h) => (
                            <option key={h} value={h}>{h}</option>
                        ))}
                    </select>
                    {errors.hora_inicio ? <p className="mt-1 text-sm text-red-600">{errors.hora_inicio}</p> : null}
                </div>
            </div>

            <div className="grid gap-6 sm:grid-cols-2">
                <div>
                    <label htmlFor="data_fim" className={painelLabelClass}>Data fim *</label>
                    <input
                        type="date"
                        id="data_fim"
                        name="data_fim"
                        required
                        value={data.data_fim}
                        onChange={(e) => onFieldChange('data_fim', e.target.value)}
                        className={painelInputClass}
                    />
                    {errors.data_fim ? <p className="mt-1 text-sm text-red-600">{errors.data_fim}</p> : null}
                </div>
                <div>
                    {!data.sem_hora_fim && (
                        <>
                            <label htmlFor="hora_fim" className={painelLabelClass}>Horário fim *</label>
                            <select
                                id="hora_fim"
                                name="hora_fim"
                                required
                                value={data.hora_fim}
                                onChange={(e) => onFieldChange('hora_fim', e.target.value)}
                                className={painelInputClass}
                            >
                                <option value="">Selecione...</option>
                                {data.hora_fim && !OPCOES_HORARIOS.includes(data.hora_fim) && (
                                    <option value={data.hora_fim}>{data.hora_fim}</option>
                                )}
                                {OPCOES_HORARIOS.map((h) => (
                                    <option key={h} value={h}>{h}</option>
                                ))}
                            </select>
                            {errors.hora_fim ? <p className="mt-1 text-sm text-red-600">{errors.hora_fim}</p> : null}
                        </>
                    )}
                    <label className="mt-2 flex cursor-pointer items-center gap-2">
                        <input
                            type="checkbox"
                            checked={data.sem_hora_fim}
                            onChange={(e) => {
                                onFieldChange('sem_hora_fim', e.target.checked)
                                if (e.target.checked) onFieldChange('hora_fim', '')
                            }}
                            className="h-4 w-4 cursor-pointer accent-amber-600"
                        />
                        <span className="text-sm text-gray-600">Sem horário final</span>
                    </label>
                </div>
            </div>

            <div className="grid gap-6 sm:grid-cols-2">
                <div>
                    <label htmlFor="limite_inscricao_data" className={painelLabelClass}>Inscrições até</label>
                    <input
                        type="date"
                        id="limite_inscricao_data"
                        name="limite_inscricao_data"
                        value={data.limite_inscricao_data}
                        onChange={(e) => onFieldChange('limite_inscricao_data', e.target.value)}
                        className={painelInputClass}
                    />
                    {errors.limite_inscricao ? <p className="mt-1 text-sm text-red-600">{errors.limite_inscricao}</p> : null}
                </div>
                <div>
                    <label htmlFor="limite_inscricao_hora" className={painelLabelClass}>Horário limite</label>
                    <select
                        id="limite_inscricao_hora"
                        name="limite_inscricao_hora"
                        value={data.limite_inscricao_hora}
                        onChange={(e) => onFieldChange('limite_inscricao_hora', e.target.value)}
                        className={painelInputClass}
                    >
                        <option value="">Selecione (padrão 23:59)...</option>
                        {data.limite_inscricao_hora && !OPCOES_HORARIOS.includes(data.limite_inscricao_hora) && (
                            <option value={data.limite_inscricao_hora}>{data.limite_inscricao_hora}</option>
                        )}
                        {OPCOES_HORARIOS.map((h) => (
                            <option key={h} value={h}>{h}</option>
                        ))}
                    </select>
                    <p className="mt-1 text-xs text-gray-500">Se vazio, será usado 23:59.</p>
                </div>
            </div>

            <div className="grid gap-6 sm:grid-cols-2">
                <div>
                    <label htmlFor="limite_participantes" className={painelLabelClass}>Limite de participantes</label>
                    <input
                        type="number"
                        id="limite_participantes"
                        name="limite_participantes"
                        min={1}
                        value={data.limite_participantes}
                        onChange={(e) => onFieldChange('limite_participantes', e.target.value)}
                        className={painelInputClass}
                    />
                    {errors.limite_participantes ? (
                        <p className="mt-1 text-sm text-red-600">{errors.limite_participantes}</p>
                    ) : null}
                </div>
                <div>
                    <label htmlFor="responsavel_id" className={painelLabelClass}>Responsável</label>
                    <select
                        id="responsavel_id"
                        name="responsavel_id"
                        value={data.responsavel_id}
                        onChange={(e) =>
                            onFieldChange('responsavel_id', e.target.value ? Number(e.target.value) : '')
                        }
                        className={painelInputClass}
                    >
                        <option value="">Selecione...</option>
                        {responsaveis.map((u) => (
                            <option key={u.id} value={u.id}>{u.name}</option>
                        ))}
                    </select>
                    {errors.responsavel_id ? (
                        <p className="mt-1 text-sm text-red-600">{errors.responsavel_id}</p>
                    ) : null}
                </div>
            </div>
        </div>
    )
}

export default Form
