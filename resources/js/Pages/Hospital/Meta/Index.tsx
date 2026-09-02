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
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible'
import PainelLayout from '@/layouts/PainelLayout'
import AbasHospital from '@/components/Painel/Hospital/Abas/Show'
import {
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
import { Building2, Calendar, CalendarDays, ChevronDown, Minus, Plus } from 'lucide-react'

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

interface SemanaMes {
    semana: number
    dia_inicio: number
    dia_fim: number
    nome_dia_inicio: string
    nome_dia_fim: string
}

interface Props {
    ano: number
    mes: number
    semanas: SemanaMes[]
    hospitais: HospitalMeta[]
    pode_editar_dados: boolean
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

const abreviarDia = (nome: string): string => nome.split('-')[0].slice(0, 3)

const formatarPeriodoSemana = (faixa: SemanaMes): string => {
    const diaInicio = abreviarDia(faixa.nome_dia_inicio)
    const diaFim = abreviarDia(faixa.nome_dia_fim)

    if (faixa.dia_inicio === faixa.dia_fim) {
        return `Dia ${faixa.dia_inicio} · ${diaInicio}`
    }

    return `${faixa.dia_inicio}–${faixa.dia_fim} · ${diaInicio}–${diaFim}`
}

const CelulaSemana: FC<{ semana: number; semanas: SemanaMes[] }> = ({ semana, semanas }) => {
    const faixa = semanas.find((item) => item.semana === semana)

    return (
        <div>
            <span className="block text-sm font-medium text-foreground">
                Semana {semana}
            </span>
            {faixa ? (
                <span className="block text-xs text-muted-foreground">
                    {formatarPeriodoSemana(faixa)}
                </span>
            ) : null}
        </div>
    )
}

interface ControleQuantidadeProps {
    valor: number | null
    maximo: number
    desabilitado?: boolean
    rotulo: string
    onChange: (valor: string) => void
}

const ControleQuantidade: FC<ControleQuantidadeProps> = ({
    valor,
    maximo,
    desabilitado = false,
    rotulo,
    onChange,
}) => (
    <div className="flex w-full max-w-40 items-center overflow-hidden rounded-lg border border-input bg-background shadow-xs">
        <button
            type="button"
            disabled={desabilitado || valor === null || valor <= 0}
            onClick={() => onChange(String(Math.max(0, (valor ?? 0) - 1)))}
            className="flex size-10 shrink-0 items-center justify-center border-r border-input text-muted-foreground transition hover:bg-muted disabled:cursor-not-allowed disabled:opacity-35"
            aria-label={`Diminuir ${rotulo}`}
        >
            <Minus className="size-4" aria-hidden />
        </button>
        <input
            type="number"
            min={0}
            max={maximo}
            disabled={desabilitado}
            value={valor ?? ''}
            onFocus={selecionarTextoInputMeta}
            onChange={(e) => onChange(e.target.value)}
            className="h-10 min-w-0 flex-1 appearance-none bg-transparent px-1 text-center text-sm font-medium outline-none disabled:cursor-not-allowed disabled:opacity-50 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
            placeholder="—"
            aria-label={rotulo}
        />
        <button
            type="button"
            disabled={desabilitado || (valor ?? 0) >= maximo}
            onClick={() => onChange(String(Math.min(maximo, (valor ?? 0) + 1)))}
            className="flex size-10 shrink-0 items-center justify-center border-l border-input text-muted-foreground transition hover:bg-muted disabled:cursor-not-allowed disabled:opacity-35"
            aria-label={`Aumentar ${rotulo}`}
        >
            <Plus className="size-4" aria-hidden />
        </button>
    </div>
)

const numerosSemanas = (semanas: SemanaMes[]): number[] => semanas.map((item) => item.semana)

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

const Index: FC<Props> = ({ ano, mes, semanas: semanasMes, hospitais: hospitaisIniciais, pode_editar_dados }) => {
    const { errors } = usePage<SharedData>().props
    const errosRef = useRef<HTMLDivElement>(null)
    const [hospitais, setHospitais] = useState<HospitalMeta[]>(hospitaisIniciais)
    const [salvando, setSalvando] = useState(false)

    const semanas = useMemo(() => numerosSemanas(semanasMes), [semanasMes])

    useEffect(() => {
        setHospitais(hospitaisIniciais)
    }, [hospitaisIniciais])

    const handleFiltroChange = (campo: 'ano' | 'mes', valor: number) => {
        const hospitalId = hospitais[0]?.id

        if (!hospitalId) return

        router.get(`/hospitais/${hospitalId}/metas`, {
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

    const montarPayload = (hospital: HospitalMeta) => ({
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
        })

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

        const hospital = hospitais[0]

        if (!hospital) return

        router.put(`/hospitais/${hospital.id}/metas`, {
            ano,
            mes,
            ...montarPayload(hospital),
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
            <div className="mx-auto max-w-7xl px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
                <header className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight text-amber-950 sm:text-3xl">
                            Metas hospitalares
                        </h1>
                        <p className="mt-1 text-sm text-amber-900/55">
                            Configure metas mensais e semanais do hospital selecionado.
                        </p>
                    </div>
                    <div className="w-full sm:w-auto [&>button]:w-full">
                        <BotaoSalvar
                            tamanho="grande"
                            onClick={handleSalvar}
                            disabled={salvando}
                            salvando={salvando}
                            rotulo="Salvar metas"
                        />
                    </div>
                </header>

                {hospitais[0] ? (
                    <div className="mb-5">
                        <AbasHospital hospitalId={hospitais[0].id} abaAtiva="metas" podeEditarDados={pode_editar_dados} />
                    </div>
                ) : null}

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
                        Hospital indisponível para configuração de metas.
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

                                    <div className="space-y-4 p-3 sm:p-4">
                                        <div className="space-y-2">
                                            <h3 className="flex items-center gap-2 text-sm font-semibold text-foreground">
                                                <CalendarDays className="size-4 shrink-0 text-muted-foreground" aria-hidden />
                                                Meta mensal
                                            </h3>
                                            <div className="grid grid-cols-[minmax(0,1fr)_auto] items-end gap-3 rounded-xl border border-border bg-muted/15 p-3 sm:max-w-md sm:gap-5 sm:p-4">
                                                <div className="min-w-0">
                                                    <span className="mb-1.5 block text-xs font-medium text-muted-foreground">
                                                        Meta do mês
                                                    </span>
                                                    <ControleQuantidade
                                                        valor={hospital.meta_mensal}
                                                        maximo={META_MENSAL_MAXIMA}
                                                        rotulo="meta mensal"
                                                        onChange={(valor) => handleMetaMensalChange(hospital.id, valor)}
                                                    />
                                                </div>
                                                <div className="pb-1 text-right">
                                                    <span className="block text-xs text-muted-foreground">Realizadas</span>
                                                    <span className="text-lg font-semibold text-foreground">
                                                        {hospital.realizadas_mensal}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="space-y-2">
                                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                                                <h3 className="flex items-center gap-2 text-sm font-semibold text-foreground">
                                                    <Calendar className="size-4 shrink-0 text-muted-foreground" aria-hidden />
                                                    Metas semanais
                                                </h3>
                                            <label className="flex min-h-10 items-center gap-2 rounded-xl bg-muted/40 px-3 text-sm text-muted-foreground sm:min-h-0 sm:bg-transparent sm:px-0">
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

                                            {hospital.metas_por_ala ? (
                                                <div className="space-y-2">
                                                    {hospital.alas.map((ala, indiceAla) => {
                                                        const metasDaAla = hospital.metas_semanais.filter(
                                                            (item) => item.ala_unidade_id === ala.id,
                                                        )
                                                        const totalDistribuido = metasDaAla.reduce(
                                                            (total, item) => total + (item.meta ?? 0),
                                                            0,
                                                        )

                                                        return (
                                                            <Collapsible key={ala.id} defaultOpen={indiceAla === 0} className="overflow-hidden rounded-xl border border-border bg-muted/15">
                                                                <CollapsibleTrigger className="group flex w-full min-w-0 items-center justify-between gap-3 px-3 py-3 text-left transition hover:bg-muted/40 sm:px-4">
                                                                    <span className="min-w-0">
                                                                        <span className="block truncate text-sm font-semibold text-foreground">
                                                                            {ala.nome}
                                                                        </span>
                                                                        <span className="block text-xs text-muted-foreground">
                                                                            {totalDistribuido} {totalDistribuido === 1 ? 'visita distribuída' : 'visitas distribuídas'}
                                                                        </span>
                                                                    </span>
                                                                    <ChevronDown className="size-4 shrink-0 text-muted-foreground transition-transform group-data-[state=open]:rotate-180" aria-hidden />
                                                                </CollapsibleTrigger>
                                                                <CollapsibleContent className="border-t border-border px-3 py-3 sm:px-4">
                                                                    <div className="space-y-2 sm:hidden">
                                                                        {metasDaAla.map((item) => (
                                                                            <div key={`${item.semana}-${ala.id}`} className="rounded-lg border border-border bg-background p-3">
                                                                                <div className="mb-3 flex min-w-0 items-start justify-between gap-3">
                                                                                    <CelulaSemana
                                                                                        semana={item.semana}
                                                                                        semanas={semanasMes}
                                                                                    />
                                                                                    <div className="shrink-0 text-right">
                                                                                        <span className="block text-[11px] text-muted-foreground">Realizadas</span>
                                                                                        <span className="text-base font-semibold text-foreground">{item.realizadas}</span>
                                                                                    </div>
                                                                                </div>
                                                                                <span className="mb-1.5 block text-xs font-medium text-muted-foreground">Meta</span>
                                                                                <ControleQuantidade
                                                                                    valor={item.meta}
                                                                                    maximo={META_SEMANAL_MAXIMA}
                                                                                    desabilitado={!metaMensalPreenchida}
                                                                                    rotulo={`meta da semana ${item.semana} para ${ala.nome}`}
                                                                                    onChange={(valor) =>
                                                                                        handleMetaSemanalChange(
                                                                                            hospital.id,
                                                                                            item.semana,
                                                                                            valor,
                                                                                            item.ala_unidade_id,
                                                                                        )
                                                                                    }
                                                                                />
                                                                            </div>
                                                                        ))}
                                                                    </div>

                                                                    <div className={`hidden sm:block ${painelTableWrapperClass}`}>
                                                                        <table className={painelTableClass}>
                                                                            <thead className={painelTableHeadClass}>
                                                                                <tr>
                                                                                    <th className={painelTableThClass}>Semana</th>
                                                                                    <th className={painelTableThClass}>Meta</th>
                                                                                    <th className={painelTableThClass}>Realizadas</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody className={painelTableBodyClass}>
                                                                                {metasDaAla.map((item) => (
                                                                                    <tr key={`${item.semana}-${ala.id}`} className={painelTableRowClass}>
                                                                                        <td className={painelTableTdClass}>
                                                                                            <CelulaSemana
                                                                                                semana={item.semana}
                                                                                                semanas={semanasMes}
                                                                                            />
                                                                                        </td>
                                                                                        <td className={painelTableTdClass}>
                                                                                            <ControleQuantidade
                                                                                                valor={item.meta}
                                                                                                maximo={META_SEMANAL_MAXIMA}
                                                                                                desabilitado={!metaMensalPreenchida}
                                                                                                rotulo={`meta da semana ${item.semana} para ${ala.nome}`}
                                                                                                onChange={(valor) =>
                                                                                                    handleMetaSemanalChange(
                                                                                                        hospital.id,
                                                                                                        item.semana,
                                                                                                        valor,
                                                                                                        item.ala_unidade_id,
                                                                                                    )
                                                                                                }
                                                                                            />
                                                                                        </td>
                                                                                        <td className={`${painelTableTdClass} font-medium text-foreground`}>
                                                                                            {item.realizadas}
                                                                                        </td>
                                                                                    </tr>
                                                                                ))}
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </CollapsibleContent>
                                                            </Collapsible>
                                                        )
                                                    })}
                                                </div>
                                            ) : (
                                                <>
                                                    <div className="space-y-2 sm:hidden">
                                                        {hospital.metas_semanais.map((item) => (
                                                            <div key={item.semana} className="rounded-xl border border-border bg-muted/15 p-3">
                                                                <div className="mb-3 flex min-w-0 items-start justify-between gap-3">
                                                                    <CelulaSemana
                                                                        semana={item.semana}
                                                                        semanas={semanasMes}
                                                                    />
                                                                    <div className="shrink-0 text-right">
                                                                        <span className="block text-[11px] text-muted-foreground">Realizadas</span>
                                                                        <span className="text-base font-semibold text-foreground">{item.realizadas}</span>
                                                                    </div>
                                                                </div>
                                                                <span className="mb-1.5 block text-xs font-medium text-muted-foreground">Meta</span>
                                                                <ControleQuantidade
                                                                    valor={item.meta}
                                                                    maximo={META_SEMANAL_MAXIMA}
                                                                    desabilitado={!metaMensalPreenchida}
                                                                    rotulo={`meta da semana ${item.semana}`}
                                                                    onChange={(valor) =>
                                                                        handleMetaSemanalChange(
                                                                            hospital.id,
                                                                            item.semana,
                                                                            valor,
                                                                        )
                                                                    }
                                                                />
                                                            </div>
                                                        ))}
                                                    </div>

                                                    <div className={`hidden sm:block ${painelTableWrapperClass}`}>
                                                        <table className={painelTableClass}>
                                                            <thead className={painelTableHeadClass}>
                                                                <tr>
                                                                    <th className={painelTableThClass}>Semana</th>
                                                                    <th className={painelTableThClass}>Meta</th>
                                                                    <th className={painelTableThClass}>Realizadas</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody className={painelTableBodyClass}>
                                                                {hospital.metas_semanais.map((item) => (
                                                                    <tr key={item.semana} className={painelTableRowClass}>
                                                                        <td className={painelTableTdClass}>
                                                                            <CelulaSemana
                                                                                semana={item.semana}
                                                                                semanas={semanasMes}
                                                                            />
                                                                        </td>
                                                                        <td className={painelTableTdClass}>
                                                                            <ControleQuantidade
                                                                                valor={item.meta}
                                                                                maximo={META_SEMANAL_MAXIMA}
                                                                                desabilitado={!metaMensalPreenchida}
                                                                                rotulo={`meta da semana ${item.semana}`}
                                                                                onChange={(valor) =>
                                                                                    handleMetaSemanalChange(
                                                                                        hospital.id,
                                                                                        item.semana,
                                                                                        valor,
                                                                                    )
                                                                                }
                                                                            />
                                                                        </td>
                                                                        <td className={`${painelTableTdClass} font-medium text-foreground`}>
                                                                            {item.realizadas}
                                                                        </td>
                                                                    </tr>
                                                                ))}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </>
                                            )}

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
                    voltarHref="/hospitais"
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
