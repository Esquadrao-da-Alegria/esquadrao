// REACT
import { type FC, useMemo, useState } from 'react'
import { Link, useForm, usePage } from '@inertiajs/react'

// UI
import VisitaForm from '@/components/Painel/Visita/Formulario/Form'
import AjusteContabilizacao from '@/components/Painel/Visita/AjusteContabilizacao'
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
import { painelLabelClass, painelSelectTriggerClass } from '@/lib/painelFormFieldClasses'
import { extrairData, extrairHora, podeCriarRelatorio } from '@/lib/visita'
import { Trash2, UserPlus } from 'lucide-react'
import { toast } from 'react-toastify'
import { obterCsrfHeaders } from '@/utils/form'

// TIPOS
import type { Cidade, Hospital, SharedData, User } from '@/types'
import type { DadosFormulario, TipoParticipacao, Visita, VisitaParticipante } from '@/types/visita'

// ROTAS
import { index, update } from '@/routes/visitas'
import { create, index as relatoriosIndex } from '@/routes/visitas/relatorios'

// SERVICES
import { Service } from '@/Services/Visita/Service'

const VALOR_VAZIO = '_vazio'

interface Props {
    hospitais: Hospital[]
    cidades?: Cidade[]
    lideres: User[]
    meses_liberados?: string[]
    visita: Visita
    ajustes_contabilizacao?: {
        ajustes: Array<{ id: number; tipo: string; justificativa: string; created_at: string; voluntario: { id: number; name: string }; administrador: { id: number; name: string } }>
        voluntarios: Array<{ id: number; name: string }>
        relatorios_atrasados: Array<{ id: number; enviado_em: string; autor: { id: number; name: string } }>
    }
}

const labelTipoParticipacao = (tipo: VisitaParticipante['tipo_participacao']) =>
    tipo === 'palhaco' ? 'Palhaço' : 'Paisana'

const labelStatusParticipacao = (status: VisitaParticipante['status_participacao']) => {
    const labels: Record<VisitaParticipante['status_participacao'], string> = {
        confirmado: 'Confirmado',
        pendente: 'Pendente',
        cancelado: 'Cancelado',
        falta: 'Falta',
    }
    return labels[status] ?? status
}

const Edit: FC<Props> = ({ hospitais, cidades = [], lideres, meses_liberados = [], visita, ajustes_contabilizacao }) => {
    const { auth, eh_administrador } = usePage<SharedData>().props
    const { data, setData, transform, put, processing, errors } = useForm<DadosFormulario>({
        hospital_id: visita.hospital_id ?? '',
        ala_unidade_id: visita.ala_unidade_id ?? null,
        data: extrairData(visita.inicio_em),
        hora_inicio: extrairHora(visita.inicio_em),
        hora_fim: extrairHora(visita.fim_em),
        tipo: visita.tipo,
        limite_participantes: visita.limite_participantes ?? '',
        lider_id: visita.lider_id ?? '',
        status: visita.status,
        observacoes: visita.observacoes ?? '',
    })

    const [participacoes, setParticipacoes] = useState<VisitaParticipante[]>(visita.participantes ?? [])
    const [novoVoluntarioId, setNovoVoluntarioId] = useState<string>('')
    const [novoTipo, setNovoTipo] = useState<TipoParticipacao>('palhaco')
    const [adicionando, setAdicionando] = useState(false)
    const [removendoId, setRemovendoId] = useState<number | null>(null)

    const handleCampoChange = <K extends keyof DadosFormulario>(campo: K, valor: DadosFormulario[K]) => {
        setData((prev) => ({ ...prev, [campo]: valor }))
    }

    const participantes = useMemo(
        () =>
            participacoes
                .filter((p) => p.status_participacao !== 'cancelado')
                .sort((a, b) => (a.voluntario?.name ?? '').localeCompare(b.voluntario?.name ?? '')),
        [participacoes],
    )

    const voluntariosDisponiveis = useMemo(() => {
        const idsInscritos = new Set(participantes.map((p) => p.voluntario_id))
        return lideres.filter((l) => !idsInscritos.has(l.id))
    }, [lideres, participantes])

    const handleAdicionarParticipante = async () => {
        if (!novoVoluntarioId) {
            toast.error('Selecione um voluntário para adicionar.')
            return
        }

        setAdicionando(true)
        try {
            const response = await fetch(`/visitas/${visita.id}/participantes`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    ...obterCsrfHeaders(),
                },
                body: JSON.stringify({
                    voluntario_id: Number(novoVoluntarioId),
                    tipo_participacao: novoTipo,
                }),
            })

            const res = await response.json()
            if (response.ok && res.sucesso) {
                setParticipacoes(res.dados?.participantes ?? participacoes)
                toast.success('Participante adicionado com sucesso!')
                setNovoVoluntarioId('')
            } else {
                toast.error(res.erros?.[0] ?? 'Erro ao adicionar participante.')
            }
        } catch {
            toast.error('Erro de conexão ao adicionar participante.')
        } finally {
            setAdicionando(false)
        }
    }

    const handleRemoverParticipante = async (participante: VisitaParticipante) => {
        if (!participante.id) return
        if (!window.confirm(`Deseja remover ${participante.voluntario?.name ?? 'este participante'} da visita?`)) {
            return
        }

        setRemovendoId(participante.id)
        try {
            const response = await fetch(`/visitas/${visita.id}/participantes/${participante.id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    ...obterCsrfHeaders(),
                },
            })

            const res = await response.json()
            if (response.ok && res.sucesso) {
                setParticipacoes(res.dados?.participantes ?? participacoes)
                toast.success('Participante removido com sucesso!')
            } else {
                toast.error(res.erros?.[0] ?? 'Erro ao remover participante.')
            }
        } catch {
            toast.error('Erro de conexão ao remover participante.')
        } finally {
            setRemovendoId(null)
        }
    }

    const handleSubmit = () => {
        if (novoVoluntarioId) {
            toast.warning('Clique em “Adicionar” para incluir o participante selecionado antes de salvar os dados gerais da visita.')
            return
        }

        if (!data.data || !data.hora_inicio || !data.hora_fim || !data.tipo || !data.lider_id || !data.status) {
            toast.error('Preencha todos os campos obrigatórios.')
            return
        }

        transform(() => Service.montarPayload(data, 'editar'))
        put(update({ visita: visita.id! }).url)
    }

    return (
        <PainelLayout>
            <section className="mx-auto w-full max-w-8xl px-4 py-16">
                <div className="flex justify-center">
                    <div className="w-full max-w-7xl">
                        <div className="overflow-hidden rounded-3xl border bg-white">
                            <div className="p-8 md:p-12">
                                <h2 className="mb-8 text-3xl font-bold text-amber-800 md:text-4xl">Alterar visita</h2>

                                {errors && Object.keys(errors).length > 0 && (
                                    <div className="mb-4 rounded-lg border border-amber-200 bg-white p-4 text-amber-800">
                                        <ul>
                                            {Object.entries(errors).map(([campo, mensagem]) => <li key={campo}>{mensagem}</li>)}
                                        </ul>
                                    </div>
                                )}

                                <form id="visita-form" onSubmit={(e) => { e.preventDefault(); handleSubmit() }} className="space-y-6">
                                    <VisitaForm
                                        data={data}
                                        errors={errors}
                                        mode="edit"
                                        hospitais={hospitais}
                                        cidades={cidades}
                                        lideres={lideres}
                                        meses_liberados={meses_liberados}
                                        onCampoChange={handleCampoChange}
                                    />

                                    <div>
                                        <h3 className={painelLabelClass}>Participantes</h3>
                                        {participantes.length === 0 ? (
                                            <p className="mb-3 text-sm text-gray-400">Nenhum participante inscrito.</p>
                                        ) : (
                                            <ul className="mb-4 divide-y divide-gray-100 rounded-xl border border-gray-200 shadow-sm">
                                                {participantes.map((p) => (
                                                    <li key={p.id ?? p.voluntario_id} className="flex items-center justify-between px-4 py-3 text-sm">
                                                        <div>
                                                            <span className="font-medium text-gray-900">{p.voluntario?.name ?? '—'}</span>
                                                            <span className="ml-2 text-xs text-gray-500">({labelTipoParticipacao(p.tipo_participacao)}{' · '}{labelStatusParticipacao(p.status_participacao)})</span>
                                                        </div>
                                                        {p.id && (
                                                            <button type="button" onClick={() => handleRemoverParticipante(p)} disabled={removendoId === p.id} className="inline-flex items-center gap-1 rounded-lg border border-red-200 px-2.5 py-1 text-xs font-semibold text-red-600 transition hover:bg-red-50 disabled:opacity-50" title="Remover participante">
                                                                <Trash2 className="size-3.5" aria-hidden />
                                                                {removendoId === p.id ? 'Removendo...' : 'Remover'}
                                                            </button>
                                                        )}
                                                    </li>
                                                ))}
                                            </ul>
                                        )}

                                        <div className="rounded-2xl border border-gray-200 bg-gray-50/60 p-4">
                                            <h4 className="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-700">Adicionar participante</h4>
                                            <p className="mb-3 text-xs text-gray-500">Selecione a pessoa e clique em <strong>Adicionar</strong>. A inclusão é feita imediatamente e não depende do botão Salvar.</p>
                                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                                                <Select
                                                    value={novoVoluntarioId || VALOR_VAZIO}
                                                    onValueChange={(valor) =>
                                                        setNovoVoluntarioId(valor === VALOR_VAZIO ? '' : valor)
                                                    }
                                                >
                                                    <SelectTrigger className={`${painelSelectTriggerClass} sm:w-64`}>
                                                        <SelectValue placeholder="Selecione um voluntário..." />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value={VALOR_VAZIO}>
                                                            Selecione um voluntário...
                                                        </SelectItem>
                                                        {voluntariosDisponiveis.map((v) => (
                                                            <SelectItem key={v.id} value={String(v.id)}>
                                                                {v.name}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                                <Select
                                                    value={novoTipo}
                                                    onValueChange={(valor) =>
                                                        setNovoTipo(valor as TipoParticipacao)
                                                    }
                                                >
                                                    <SelectTrigger className={`${painelSelectTriggerClass} sm:w-36`}>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="palhaco">🎪 Palhaço</SelectItem>
                                                        <SelectItem value="paisana">👔 Paisana</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                                <button type="button" onClick={handleAdicionarParticipante} disabled={adicionando || !novoVoluntarioId} className="inline-flex items-center justify-center gap-1.5 rounded-xl border border-amber-600 bg-white px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-50 disabled:opacity-50">
                                                    <UserPlus className="size-4" aria-hidden />
                                                    {adicionando ? 'Adicionando...' : 'Adicionar participante'}
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="rounded-2xl border border-amber-100 bg-amber-50/40 p-5">
                                        <h3 className="mb-3 text-sm font-semibold uppercase tracking-wide text-amber-900/70">Relatórios</h3>
                                        <div className="flex flex-col gap-2 sm:flex-row">
                                            <Link href={relatoriosIndex.url({ visita: visita.id! })} className="inline-flex flex-1 items-center justify-center rounded-full border border-amber-200 bg-white px-4 py-2.5 text-sm font-semibold text-amber-800 transition hover:bg-amber-50">Ver relatórios</Link>
                                            {podeCriarRelatorio(auth.user, visita) && (
                                                <Link href={create.url({ visita: visita.id! })} className="inline-flex flex-1 items-center justify-center rounded-full border-2 border-amber-600 bg-white px-4 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-50">Criar relatório</Link>
                                            )}
                                        </div>
                                    </div>

                                </form>

                                {eh_administrador && ajustes_contabilizacao && (
                                    <div className="mt-6"><AjusteContabilizacao visitaId={visita.id!} voluntarios={ajustes_contabilizacao.voluntarios} relatorios={ajustes_contabilizacao.relatorios_atrasados} ajustes={ajustes_contabilizacao.ajustes} /></div>
                                )}
                            </div>

                            <FormularioRodape
                                voltarHref={index().url}
                                salvar={(
                                    <BotaoSalvar
                                        type="submit"
                                        form="visita-form"
                                        disabled={processing}
                                        salvando={processing}
                                        rotulo="Salvar dados gerais"
                                    />
                                )}
                            />
                        </div>
                    </div>
                </div>
            </section>
        </PainelLayout>
    )
}

export default Edit
