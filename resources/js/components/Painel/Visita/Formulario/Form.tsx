// REACT
import { type FC, useEffect, useMemo, useState } from 'react'
import { usePage } from '@inertiajs/react'

// UI
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { painelInputClass, painelLabelClass, painelSelectTriggerClass } from '@/lib/painelFormFieldClasses'
import {
    dataPermitidaVisitaHospital,
    diasDoMes,
    extrairDia,
    labelMes,
    labelStatus,
    labelTipo,
    mesDaData,
    mesesLiberadosParaSelecao,
    montarData,
    VISITA_STATUS,
    VISITA_TIPOS,
} from '@/lib/visita'
import { toast } from 'react-toastify'

// TIPOS
import type { Cidade, Hospital, SharedData, User } from '@/types'
import type { DadosFormulario, VisitaStatus, VisitaTipo } from '@/types/visita'

interface Props {
    data: DadosFormulario
    errors: Record<string, string | undefined>
    mode: 'create' | 'edit'
    hospitais: Hospital[]
    cidades?: Cidade[]
    lideres: User[]
    meses_liberados?: string[]
    meses_liberados_por_cidade?: Record<number, string[]>
    cidadeInicialId?: number
    statusOriginal?: VisitaStatus
    onCampoChange: <K extends keyof DadosFormulario>(campo: K, valor: DadosFormulario[K]) => void
}

const VALOR_VAZIO = '_vazio'
const CIDADE_TODAS = 'todas'

const Form: FC<Props> = ({
    data,
    errors,
    mode,
    hospitais,
    cidades = [],
    lideres,
    meses_liberados = [],
    meses_liberados_por_cidade = {},
    cidadeInicialId,
    statusOriginal,
    onCampoChange,
}) => {
    const { auth } = usePage<SharedData>().props
    const userCidadeId = (auth?.user?.cidade_base_id ?? auth?.user?.voluntario?.cidade_base_id ?? '') as number | ''

    const hospitalSelecionado = useMemo(() => {
        if (!data.hospital_id) return null
        return hospitais.find((h) => h.id === Number(data.hospital_id)) ?? null
    }, [data.hospital_id, hospitais])

    const [cidadeFiltroId, setCidadeFiltroId] = useState<number | ''>(
        hospitalSelecionado?.cidade_id ?? cidadeInicialId ?? userCidadeId ?? '',
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

    const exigeHospital = data.tipo === 'hospital' || data.tipo === 'residencia'
    const restringeAgendaHospital = data.tipo === 'hospital'
    const podeAlterarEstrutura = mode === 'create' || statusOriginal === 'agendada'
    const mesesLiberadosCidade = cidadeFiltroId
        ? (meses_liberados_por_cidade[Number(cidadeFiltroId)] ?? meses_liberados)
        : meses_liberados

    const mesesParaSelecao = useMemo(
        () => (restringeAgendaHospital
            ? mesesLiberadosParaSelecao(
                mesesLiberadosCidade,
                mode === 'edit' ? data.data : undefined,
            )
            : []),
        [data.data, mesesLiberadosCidade, mode, restringeAgendaHospital],
    )

    const mesSelecionado = data.data ? mesDaData(data.data) : ''
    const diasDisponiveis = useMemo(
        () => (mesSelecionado ? diasDoMes(mesSelecionado) : []),
        [mesSelecionado],
    )
    const diaSelecionado = data.data ? extrairDia(data.data) : ''

    useEffect(() => {
        if (!restringeAgendaHospital || mesesParaSelecao.length === 0) {
            return
        }

        if (!data.data || !dataPermitidaVisitaHospital(data.data, mesesParaSelecao)) {
            onCampoChange('data', montarData(mesesParaSelecao[0], 1))
        }
    }, [data.data, mesesParaSelecao, onCampoChange, restringeAgendaHospital])

    const handleDataChange = (valor: string) => {
        if (restringeAgendaHospital && valor && !dataPermitidaVisitaHospital(valor, mesesLiberadosCidade)) {
            toast.error('Este mês não está liberado para visitas hospitalares.')
            return
        }

        onCampoChange('data', valor)
    }

    const handleMesChange = (anoMes: string) => {
        if (!anoMes) {
            return
        }

        const diaAtual = diaSelecionado || 1
        const ultimoDia = diasDoMes(anoMes).length
        const dia = Math.min(diaAtual, ultimoDia)

        onCampoChange('data', montarData(anoMes, dia))
    }

    const handleDiaChange = (dia: number) => {
        const anoMes = mesSelecionado || mesesParaSelecao[0]

        if (!anoMes) {
            return
        }

        onCampoChange('data', montarData(anoMes, dia))
    }

    return (
        <div className="space-y-6">
            <div>
                <label htmlFor="tipo" className={painelLabelClass}>Tipo *</label>
                <Select
                    value={data.tipo}
                    onValueChange={(valor) => onCampoChange('tipo', valor as VisitaTipo)}
                >
                    <SelectTrigger id="tipo" className={painelSelectTriggerClass}>
                        <SelectValue placeholder="Selecione..." />
                    </SelectTrigger>
                    <SelectContent>
                        {VISITA_TIPOS.map((t) => (
                            <SelectItem key={t} value={t}>
                                {labelTipo(t)}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {errors.tipo ? <p className="mt-1 text-sm text-red-600">{errors.tipo}</p> : null}
            </div>

            {cidades.length > 0 && (
                <div>
                    <label htmlFor="cidade_filtro_id" className={painelLabelClass}>Filtrar por Cidade</label>
                    <Select
                        disabled={!podeAlterarEstrutura}
                        value={cidadeFiltroId === '' ? CIDADE_TODAS : String(cidadeFiltroId)}
                        onValueChange={(valor) =>
                            handleCidadeChange(valor === CIDADE_TODAS ? '' : Number(valor))
                        }
                    >
                        <SelectTrigger id="cidade_filtro_id" className={painelSelectTriggerClass}>
                            <SelectValue placeholder="Todas as Cidades" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={CIDADE_TODAS}>Todas as Cidades</SelectItem>
                            {cidades.map((c) => (
                                <SelectItem key={c.id} value={String(c.id)}>
                                    {c.nome}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
            )}

            <div>
                <label htmlFor="hospital_id" className={painelLabelClass}>
                    {exigeHospital ? 'Hospital *' : 'Hospital (Opcional)'}
                </label>
                <Select
                    disabled={!podeAlterarEstrutura}
                    value={data.hospital_id === '' ? VALOR_VAZIO : String(data.hospital_id)}
                    onValueChange={(valor) =>
                        handleHospitalChange(valor === VALOR_VAZIO ? '' : Number(valor))
                    }
                >
                    <SelectTrigger id="hospital_id" className={painelSelectTriggerClass}>
                        <SelectValue
                            placeholder={
                                !exigeHospital
                                    ? 'Nenhum hospital (Opcional)'
                                    : cidadeFiltroId
                                      ? 'Selecione um hospital da cidade...'
                                      : 'Selecione...'
                            }
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value={VALOR_VAZIO}>
                            {!exigeHospital
                                ? 'Nenhum hospital (Opcional)'
                                : cidadeFiltroId
                                  ? 'Selecione um hospital da cidade...'
                                  : 'Selecione...'}
                        </SelectItem>
                        {hospitaisFiltrados.map((h) => (
                            <SelectItem key={h.id} value={String(h.id)}>
                                {h.nome}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {errors.hospital_id ? <p className="mt-1 text-sm text-red-600">{errors.hospital_id}</p> : null}
            </div>

            <div>
                <label htmlFor="ala_unidade_id" className={painelLabelClass}>Ala / Unidade</label>
                <Select
                    disabled={!data.hospital_id || !podeAlterarEstrutura}
                    value={data.ala_unidade_id ? String(data.ala_unidade_id) : VALOR_VAZIO}
                    onValueChange={(valor) =>
                        onCampoChange('ala_unidade_id', valor === VALOR_VAZIO ? null : Number(valor))
                    }
                >
                    <SelectTrigger id="ala_unidade_id" className={painelSelectTriggerClass}>
                        <SelectValue placeholder="Nenhuma" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value={VALOR_VAZIO}>Nenhuma</SelectItem>
                        {alasDisponiveis.map((a) => (
                            <SelectItem key={a.id} value={String(a.id)}>
                                {a.nome}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {errors.ala_unidade_id ? <p className="mt-1 text-sm text-red-600">{errors.ala_unidade_id}</p> : null}
            </div>

            <div
                className={
                    restringeAgendaHospital
                        ? 'grid gap-6 sm:grid-cols-2 lg:grid-cols-4'
                        : 'grid gap-6 sm:grid-cols-2 lg:grid-cols-3'
                }
            >
                {restringeAgendaHospital ? (
                    <>
                        <div>
                            <label htmlFor="data_mes" className={painelLabelClass}>Mês *</label>
                            <Select
                                disabled={mesesParaSelecao.length === 0}
                                value={mesSelecionado || VALOR_VAZIO}
                                onValueChange={(valor) => {
                                    if (valor !== VALOR_VAZIO) {
                                        handleMesChange(valor)
                                    }
                                }}
                            >
                                <SelectTrigger id="data_mes" className={painelSelectTriggerClass}>
                                    <SelectValue placeholder="Nenhum mês liberado" />
                                </SelectTrigger>
                                <SelectContent>
                                    {mesesParaSelecao.length === 0 ? (
                                        <SelectItem value={VALOR_VAZIO}>Nenhum mês liberado</SelectItem>
                                    ) : (
                                        mesesParaSelecao.map((anoMes) => (
                                            <SelectItem key={anoMes} value={anoMes}>
                                                {labelMes(anoMes)}
                                            </SelectItem>
                                        ))
                                    )}
                                </SelectContent>
                            </Select>
                            {mesesLiberadosCidade.length === 0 ? (
                                <p className="mt-1 text-xs text-gray-500">
                                    Nenhum mês liberado para visitas hospitalares.
                                </p>
                            ) : (
                                <p className="mt-1 text-xs text-gray-500">
                                    Somente meses liberados na sua cidade-base podem ser selecionados.
                                </p>
                            )}
                            {errors.data ? <p className="mt-1 text-sm text-red-600">{errors.data}</p> : null}
                        </div>
                        <div>
                            <label htmlFor="data_dia" className={painelLabelClass}>Dia *</label>
                            <Select
                                disabled={!mesSelecionado || diasDisponiveis.length === 0}
                                value={diaSelecionado ? String(diaSelecionado) : VALOR_VAZIO}
                                onValueChange={(valor) => {
                                    if (valor !== VALOR_VAZIO) {
                                        handleDiaChange(Number(valor))
                                    }
                                }}
                            >
                                <SelectTrigger id="data_dia" className={painelSelectTriggerClass}>
                                    <SelectValue placeholder="Selecione o dia" />
                                </SelectTrigger>
                                <SelectContent>
                                    {diasDisponiveis.length === 0 ? (
                                        <SelectItem value={VALOR_VAZIO}>—</SelectItem>
                                    ) : (
                                        diasDisponiveis.map((dia) => (
                                            <SelectItem key={dia} value={String(dia)}>
                                                {dia}
                                            </SelectItem>
                                        ))
                                    )}
                                </SelectContent>
                            </Select>
                        </div>
                    </>
                ) : (
                    <div>
                        <label htmlFor="data" className={painelLabelClass}>Data *</label>
                        <input
                            type="date"
                            id="data"
                            required
                            value={data.data}
                            onChange={(e) => handleDataChange(e.target.value)}
                            className={painelInputClass}
                        />
                        {errors.data ? <p className="mt-1 text-sm text-red-600">{errors.data}</p> : null}
                    </div>
                )}
                <div>
                    <label htmlFor="hora_inicio" className={painelLabelClass}>Início *</label>
                    <input
                        type="time"
                        id="hora_inicio"
                        name="hora_inicio"
                        required
                        value={data.hora_inicio}
                        onChange={(e) => onCampoChange('hora_inicio', e.target.value)}
                        className={painelInputClass}
                    />
                    {errors.hora_inicio ? <p className="mt-1 text-sm text-red-600">{errors.hora_inicio}</p> : null}
                </div>
                <div>
                    <label htmlFor="hora_fim" className={painelLabelClass}>Fim *</label>
                    <input
                        type="time"
                        id="hora_fim"
                        name="hora_fim"
                        required
                        value={data.hora_fim}
                        onChange={(e) => onCampoChange('hora_fim', e.target.value)}
                        className={painelInputClass}
                    />
                    {errors.hora_fim ? <p className="mt-1 text-sm text-red-600">{errors.hora_fim}</p> : null}
                </div>
            </div>

            <div>
                <label htmlFor="limite_participantes" className={painelLabelClass}>Limite de Participantes</label>
                <input
                    type="number"
                    id="limite_participantes"
                    name="limite_participantes"
                    min={1}
                    placeholder="Deixe em branco para ilimitado (padrão: 5)"
                    value={data.limite_participantes ?? ''}
                    onChange={(e) =>
                        onCampoChange(
                            'limite_participantes',
                            e.target.value !== '' ? Number(e.target.value) : '',
                        )
                    }
                    className={painelInputClass}
                />
                <p className="mt-1 text-xs text-gray-500">Deixe em branco para ilimitado ou informe um número de vagas.</p>
                {errors.limite_participantes ? <p className="mt-1 text-sm text-red-600">{errors.limite_participantes}</p> : null}
            </div>

            <div>
                <label htmlFor="lider_id" className={painelLabelClass}>Líder *</label>
                <Select
                    value={data.lider_id ? String(data.lider_id) : VALOR_VAZIO}
                    onValueChange={(valor) =>
                        onCampoChange('lider_id', valor === VALOR_VAZIO ? '' : Number(valor))
                    }
                >
                    <SelectTrigger id="lider_id" className={painelSelectTriggerClass}>
                        <SelectValue placeholder="Selecione..." />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value={VALOR_VAZIO}>Selecione...</SelectItem>
                        {lideres.map((l) => (
                            <SelectItem key={l.id} value={String(l.id)}>
                                {l.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {errors.lider_id ? <p className="mt-1 text-sm text-red-600">{errors.lider_id}</p> : null}
            </div>

            {mode === 'edit' && data.status ? (
                <div>
                    <label htmlFor="status" className={painelLabelClass}>Status *</label>
                    <Select
                        value={data.status}
                        onValueChange={(valor) => onCampoChange('status', valor as VisitaStatus)}
                    >
                        <SelectTrigger id="status" className={painelSelectTriggerClass}>
                            <SelectValue placeholder="Selecione..." />
                        </SelectTrigger>
                        <SelectContent>
                            {VISITA_STATUS.map((s) => (
                                <SelectItem key={s} value={s}>
                                    {labelStatus(s)}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
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
