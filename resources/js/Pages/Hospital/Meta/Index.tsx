// REACT
import { type FC, type FocusEvent, useEffect, useMemo, useRef, useState } from 'react'
import { router, usePage } from '@inertiajs/react'

// UI
import CardErros from '@/components/Painel/Forms/CardErros/Show'
import BotaoSalvar from '@/components/Painel/Forms/BotaoSalvar/Show'
import FormularioRodape from '@/components/Painel/Forms/FormularioRodape/Show'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import PainelLayout from '@/layouts/PainelLayout'
import {
    painelInputClass,
    painelSelectTriggerClass,
    painelSurfaceClass,
    painelSurfaceHeaderClass,
    painelTableBodyClass,
    painelTableClass,
    painelTableHeadClass,
    painelTableRowClass,
    painelTableTdClass,
    painelTableThClass,
    painelTableWrapperClass,
} from '@/lib/painelFormFieldClasses'
import { toastAviso, toastConfirmacao, toastSucesso } from '@/lib/utils/toast'
import { dashboard } from '@/routes'
import { Building2, Calendar, CalendarDays } from 'lucide-react'

// TIPOS
import type { SharedData } from '@/types'

interface AlaMeta {
    id: number
    nome: string
}

interface MetaSemanal {
    semana: number
    meta: number | null
    realizadas: number
    ala_unidade_id?: number
}

interface HospitalMeta {
    id: number
    nome: string
    alas: AlaMeta[]
    meta_mensal: number | null
    realizadas_mensal: number
    metas_por_ala: boolean
    metas_semanais: MetaSemanal[]
}

interface Props {
    ano: number
    mes: number
    hospitais: HospitalMeta[]
}

const NOMES_MESES = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro',
]

const META_MENSAL_MAXIMA = 10
const META_SEMANAL_MAXIMA = 5

const limitarMetaMensal = (valor: string): number | null => {
    if (valor === '') {
        return null
    }

    const numero = Number(valor)

    if (Number.isNaN(numero)) {
        return null
    }

    return Math.min(Math.max(0, numero), META_MENSAL_MAXIMA)
}

const limitarMetaSemanal = (valor: string): number | null => {
    if (valor === '') {
        return null
    }

    const numero = Number(valor)

    if (Number.isNaN(numero)) {
        return null
    }

    return Math.min(Math.max(0, numero), META_SEMANAL_MAXIMA)
}

const selecionarTextoInputMeta = (event: FocusEvent<HTMLInputElement>) => {
    event.currentTarget.select()
}

const labelSemana = (semana: number): string => {
    const faixas: Record<number, string> = {
        1: '1–7',
        2: '8–14',
        3: '15–21',
        4: '22–28',
        5: '29–fim',
    }

    return `Semana ${semana} (${faixas[semana] ?? ''})`
}

const semanasDoMes = (ano: number, mes: number): number[] => {
    const dias = new Date(ano, mes, 0).getDate()

    return dias >= 29 ? [1, 2, 3, 4, 5] : [1, 2, 3, 4]
}

const montarMetasSemanaisVazias = (
    hospital: HospitalMeta,
    metasPorAla: boolean,
    semanas: number[],
): MetaSemanal[] => {
    if (metasPorAla) {
        return hospital.alas.flatMap((ala) =>
            semanas.map((semana) => ({
                semana,
                ala_unidade_id: ala.id,
                meta: null,
                realizadas: 0,
            })),
        )
    }

    return semanas.map((semana) => ({
        semana,
        meta: null,
        realizadas: 0,
    }))
}

const Index: FC<Props> = ({ ano, mes, hospitais: hospitaisIniciais }) => {
    const { errors } = usePage<SharedData>().props
    const errosRef = useRef<HTMLDivElement>(null)
    const [hospitais, setHospitais] = useState<HospitalMeta[]>(hospitaisIniciais)
    const [salvando, setSalvando] = useState(false)

    const semanas = useMemo(() => semanasDoMes(ano, mes), [ano, mes])

    useEffect(() => {
        setHospitais(hospitaisIniciais)
    }, [hospitaisIniciais])

    const handleFiltroChange = (campo: 'ano' | 'mes', valor: number) => {
        router.get('/hospitais/metas', {
            ano: campo === 'ano' ? valor : ano,
            mes: campo === 'mes' ? valor : mes,
        }, { preserveScroll: true })
    }

    const atualizarHospital = (hospitalId: number, atualizacao: Partial<HospitalMeta>) => {
        setHospitais((lista) =>
            lista.map((h) => (h.id === hospitalId ? { ...h, ...atualizacao } : h)),
        )
    }

    const handleMetaMensalChange = (hospitalId: number, valor: string) => {
        const metaMensal = limitarMetaMensal(valor)

        setHospitais((lista) =>
            lista.map((hospital) => {
                if (hospital.id !== hospitalId) {
                    return hospital
                }

                if (metaMensal === null) {
                    return { ...hospital, meta_mensal: null, metas_semanais: [] }
                }

                const metasSemanais = hospital.metas_semanais.length > 0
                    ? hospital.metas_semanais
                    : montarMetasSemanaisVazias(hospital, hospital.metas_por_ala, semanas)

                return { ...hospital, meta_mensal: metaMensal, metas_semanais: metasSemanais }
            }),
        )
    }

    const handleMetasPorAlaChange = async (hospital: HospitalMeta, metasPorAla: boolean) => {
        const possuiMetasSemanais = hospital.metas_semanais.some((item) => item.meta !== null && item.meta > 0)

        if (possuiMetasSemanais && hospital.metas_por_ala !== metasPorAla) {
            const confirmado = await toastConfirmacao(
                'Trocar o modo de metas semanais remove as metas do modo atual. Deseja continuar?',
            )

            if (!confirmado) {
                return
            }
        }

        atualizarHospital(hospital.id, {
            metas_por_ala: metasPorAla,
            metas_semanais: montarMetasSemanaisVazias(hospital, metasPorAla, semanas),
        })
    }

    const handleMetaSemanalChange = (
        hospitalId: number,
        semana: number,
        valor: string,
        alaUnidadeId?: number,
    ) => {
        const quantidade = limitarMetaSemanal(valor)

        setHospitais((lista) =>
            lista.map((hospital) => {
                if (hospital.id !== hospitalId) {
                    return hospital
                }

                const metasSemanais = hospital.metas_semanais.map((item) => {
                    const mesmaSemana = item.semana === semana
                    const mesmaAla = hospital.metas_por_ala
                        ? item.ala_unidade_id === alaUnidadeId
                        : true

                    if (!mesmaSemana || !mesmaAla) {
                        return item
                    }

                    return { ...item, meta: quantidade }
                })

                return { ...hospital, metas_semanais: metasSemanais }
            }),
        )
    }

    const montarPayload = () =>
        hospitais.map((hospital) => ({
            hospital_id: hospital.id,
            meta_mensal: hospital.meta_mensal,
            metas_por_ala: hospital.metas_por_ala,
            metas_semanais: hospital.metas_semanais
                .filter((item) => item.meta !== null && item.meta !== undefined)
                .map((item) => ({
                    semana: item.semana,
                    quantidade: Number(item.meta),
                    ...(hospital.metas_por_ala && item.ala_unidade_id
                        ? { ala_unidade_id: item.ala_unidade_id }
                        : {}),
                })),
        }))

    const handleSalvar = () => {
        for (const hospital of hospitais) {
            const metasPreenchidas = hospital.metas_semanais.filter(
                (item) => item.meta !== null && item.meta !== undefined,
            )

            if (metasPreenchidas.length === 0) {
                continue
            }

            if (hospital.meta_mensal === null) {
                toastAviso(`Informe a meta mensal de ${hospital.nome} antes das metas semanais.`)
                return
            }

            const soma = metasPreenchidas.reduce((total, item) => total + Number(item.meta), 0)

            if (soma !== hospital.meta_mensal) {
                toastAviso(
                    `A soma das metas semanais de ${hospital.nome} (${soma}) deve ser igual à meta mensal (${hospital.meta_mensal}).`,
                )
                return
            }
        }

        setSalvando(true)

        router.put('/hospitais/metas', {
            ano,
            mes,
            hospitais: montarPayload(),
        }, {
            preserveScroll: true,
            onSuccess: () => toastSucesso('Metas salvas com sucesso!'),
            onError: () => {
                errosRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' })
            },
            onFinish: () => setSalvando(false),
        })
    }

    return (
        <PainelLayout>
            <div className="mx-auto max-w-7xl px-5 py-6 sm:px-6 lg:px-8">
                <header className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight text-amber-950 sm:text-3xl">
                            Metas hospitalares
                        </h1>
                        <p className="mt-1 text-sm text-amber-900/55">
                            Configure metas mensais e semanais por hospital da sua cidade-base.
                        </p>
                    </div>
                    <BotaoSalvar
                        tamanho="grande"
                        onClick={handleSalvar}
                        disabled={salvando}
                        salvando={salvando}
                        rotulo="Salvar metas"
                    />
                </header>

                <div ref={errosRef}>
                    <CardErros erros={errors} />
                </div>

                <section className="mb-5 flex flex-col gap-3 sm:flex-row">
                    <div className="sm:w-40">
                        <Select
                            value={String(ano)}
                            onValueChange={(valor) => handleFiltroChange('ano', Number(valor))}
                        >
                            <SelectTrigger className={painelSelectTriggerClass}>
                                <div className="flex items-center gap-2 truncate">
                                    <Calendar className="size-4 shrink-0 text-muted-foreground" aria-hidden />
                                    <SelectValue placeholder="Ano" />
                                </div>
                            </SelectTrigger>
                            <SelectContent>
                                {[ano - 1, ano, ano + 1].map((opcao) => (
                                    <SelectItem key={opcao} value={String(opcao)}>
                                        {opcao}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="sm:w-52">
                        <Select
                            value={String(mes)}
                            onValueChange={(valor) => handleFiltroChange('mes', Number(valor))}
                        >
                            <SelectTrigger className={painelSelectTriggerClass}>
                                <div className="flex items-center gap-2 truncate">
                                    <CalendarDays className="size-4 shrink-0 text-muted-foreground" aria-hidden />
                                    <SelectValue placeholder="Mês" />
                                </div>
                            </SelectTrigger>
                            <SelectContent>
                                {NOMES_MESES.map((nome, indice) => (
                                    <SelectItem key={nome} value={String(indice + 1)}>
                                        {nome}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </section>

                {hospitais.length === 0 ? (
                    <div className="rounded-2xl border border-dashed border-amber-300 bg-white px-5 py-12 text-center text-sm text-amber-900/50">
                        Nenhum hospital ativo encontrado na sua cidade-base.
                    </div>
                ) : (
                    <div className="space-y-5">
                        {hospitais.map((hospital) => {
                            const metaMensalPreenchida = hospital.meta_mensal !== null

                            return (
                                <section
                                    key={hospital.id}
                                    className={painelSurfaceClass}
                                >
                                    <div className={painelSurfaceHeaderClass}>
                                        <h2 className="flex items-center gap-2 text-base font-semibold text-foreground">
                                            <Building2 className="size-4 shrink-0 text-muted-foreground" aria-hidden />
                                            {hospital.nome}
                                        </h2>
                                    </div>

                                    <div className="space-y-4 p-4">
                                        <div className="space-y-2">
                                            <h3 className="flex items-center gap-2 text-sm font-semibold text-foreground">
                                                <CalendarDays className="size-4 shrink-0 text-muted-foreground" aria-hidden />
                                                Meta mensal
                                            </h3>
                                            <div className={painelTableWrapperClass}>
                                            <table className={painelTableClass}>
                                                <thead className={painelTableHeadClass}>
                                                    <tr>
                                                        <th className={painelTableThClass}>Meta</th>
                                                        <th className={painelTableThClass}>Realizadas no mês</th>
                                                    </tr>
                                                </thead>
                                                <tbody className={painelTableBodyClass}>
                                                    <tr className={painelTableRowClass}>
                                                        <td className={painelTableTdClass}>
                                                            <input
                                                                type="number"
                                                                min={0}
                                                                max={META_MENSAL_MAXIMA}
                                                                value={hospital.meta_mensal ?? ''}
                                                                onFocus={selecionarTextoInputMeta}
                                                                onChange={(e) =>
                                                                    handleMetaMensalChange(hospital.id, e.target.value)
                                                                }
                                                                className={`${painelInputClass} max-w-[7rem] py-1.5 px-3`}
                                                                placeholder="Opcional"
                                                            />
                                                        </td>
                                                        <td className={`${painelTableTdClass} font-medium text-foreground`}>
                                                            {hospital.realizadas_mensal}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            </div>
                                        </div>

                                        <div className="space-y-2">
                                            <div className="flex items-center justify-between gap-4">
                                                <h3 className="flex items-center gap-2 text-sm font-semibold text-foreground">
                                                    <Calendar className="size-4 shrink-0 text-muted-foreground" aria-hidden />
                                                    Metas semanais
                                                </h3>
                                            <label className="flex items-center gap-2 text-sm text-muted-foreground">
                                                <input
                                                    type="checkbox"
                                                    checked={hospital.metas_por_ala}
                                                    onChange={(e) =>
                                                        handleMetasPorAlaChange(hospital, e.target.checked)
                                                    }
                                                    className="size-4 rounded border-input text-primary focus:ring-ring"
                                                />
                                                Metas por ala
                                            </label>
                                            </div>

                                            <div className={painelTableWrapperClass}>
                                            <table className={painelTableClass}>
                                                <thead className={painelTableHeadClass}>
                                                    <tr>
                                                        {hospital.metas_por_ala ? (
                                                            <th className={painelTableThClass}>Ala</th>
                                                        ) : null}
                                                        <th className={painelTableThClass}>Semana</th>
                                                        <th className={painelTableThClass}>Meta</th>
                                                        <th className={painelTableThClass}>Realizadas</th>
                                                    </tr>
                                                </thead>
                                                <tbody className={painelTableBodyClass}>
                                                    {hospital.metas_semanais.map((item) => {
                                                        const chave = hospital.metas_por_ala
                                                            ? `${item.semana}-${item.ala_unidade_id}`
                                                            : String(item.semana)
                                                        const alaNome = hospital.alas.find(
                                                            (ala) => ala.id === item.ala_unidade_id,
                                                        )?.nome

                                                        return (
                                                            <tr key={chave} className={painelTableRowClass}>
                                                                {hospital.metas_por_ala ? (
                                                                    <td className={`${painelTableTdClass} font-medium text-foreground`}>
                                                                        {alaNome ?? '—'}
                                                                    </td>
                                                                ) : null}
                                                                <td className={`${painelTableTdClass} text-muted-foreground`}>
                                                                    {labelSemana(item.semana)}
                                                                </td>
                                                                <td className={painelTableTdClass}>
                                                                    <input
                                                                        type="number"
                                                                        min={0}
                                                                        max={META_SEMANAL_MAXIMA}
                                                                        disabled={!metaMensalPreenchida}
                                                                        value={item.meta ?? ''}
                                                                        onFocus={selecionarTextoInputMeta}
                                                                        onChange={(e) =>
                                                                            handleMetaSemanalChange(
                                                                                hospital.id,
                                                                                item.semana,
                                                                                e.target.value,
                                                                                item.ala_unidade_id,
                                                                            )
                                                                        }
                                                                        className={`${painelInputClass} max-w-[5.5rem] py-1.5 px-3 disabled:cursor-not-allowed disabled:opacity-50`}
                                                                        placeholder="—"
                                                                    />
                                                                </td>
                                                                <td className={`${painelTableTdClass} font-medium text-foreground`}>
                                                                    {item.realizadas}
                                                                </td>
                                                            </tr>
                                                        )
                                                    })}
                                                </tbody>
                                            </table>
                                            </div>

                                            {!metaMensalPreenchida ? (
                                                <p className="text-xs text-muted-foreground">
                                                    Preencha a meta mensal para habilitar as metas semanais.
                                                </p>
                                            ) : null}
                                        </div>
                                    </div>
                                </section>
                            )
                        })}
                    </div>
                )}

                <FormularioRodape
                    variante="pagina"
                    voltarHref={dashboard().url}
                    salvar={(
                        <BotaoSalvar
                            onClick={handleSalvar}
                            disabled={salvando}
                            salvando={salvando}
                        />
                    )}
                />
            </div>
        </PainelLayout>
    )
}

export default Index
