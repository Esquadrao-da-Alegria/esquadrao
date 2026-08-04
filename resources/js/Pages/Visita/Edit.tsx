// REACT
import { type FC, useMemo, useState } from 'react'
import { Link, router, useForm, usePage } from '@inertiajs/react'

// UI
import VisitaForm from '@/components/Painel/Visita/Formulario/Form'
import PainelLayout from '@/layouts/PainelLayout'
import { painelLabelClass } from '@/lib/painelFormFieldClasses'
import { extrairData, extrairHora, podeCriarRelatorio } from '@/lib/visita'
import { ArrowLeft, Check, Trash2, UserPlus } from 'lucide-react'
import { toast } from 'react-toastify'
import { obterCsrfToken } from '@/utils/form'

// TIPOS
import type { Cidade, Hospital, SharedData, User } from '@/types'
import type { DadosFormulario, TipoParticipacao, Visita, VisitaParticipante } from '@/types/visita'

// ROTAS
import { index, update } from '@/routes/visitas'
import { create, index as relatoriosIndex } from '@/routes/visitas/relatorios'

// SERVICES
import { Service } from '@/Services/Visita/Service'

interface Props {
    hospitais: Hospital[]
    cidades?: Cidade[]
    lideres: User[]
    visita: Visita
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

const Edit: FC<Props> = ({ hospitais, cidades = [], lideres, visita }) => {
    const { auth } = usePage<SharedData>().props
    const { data, setData, transform, put, processing, errors } = useForm<DadosFormulario>({
        hospital_id: visita.hospital_id,
        ala_unidade_id: visita.ala_unidade_id ?? null,
        data: extrairData(visita.inicio_em),
        hora_inicio: extrairHora(visita.inicio_em),
        hora_fim: extrairHora(visita.fim_em),
        tipo: visita.tipo,
        lider_id: visita.lider_id ?? '',
        status: visita.status,
        observacoes: visita.observacoes ?? '',
    })

    const [novoVoluntarioId, setNovoVoluntarioId] = useState<string>('')
    const [novoTipo, setNovoTipo] = useState<TipoParticipacao>('palhaco')
    const [adicionando, setAdicionando] = useState(false)
    const [removendoId, setRemovendoId] = useState<number | null>(null)

    const handleCampoChange = <K extends keyof DadosFormulario>(campo: K, valor: DadosFormulario[K]) => {
        setData((prev) => ({ ...prev, [campo]: valor }))
    }

    const participantes = useMemo(
        () =>
            (visita.participantes ?? [])
                .filter((p) => p.status_participacao !== 'cancelado')
                .sort((a, b) => (a.voluntario?.name ?? '').localeCompare(b.voluntario?.name ?? '')),
        [visita.participantes],
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
            const token = obterCsrfToken()
            const response = await fetch(`/visitas/${visita.id}/participantes`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({
                    voluntario_id: Number(novoVoluntarioId),
                    tipo_participacao: novoTipo,
                }),
            })

            const res = await response.json()
            if (response.ok && res.sucesso) {
                toast.success('Participante adicionado com sucesso!')
                setNovoVoluntarioId('')
                router.reload({ only: ['visita'] })
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
            const token = obterCsrfToken()
            const response = await fetch(`/visitas/${visita.id}/participantes/${participante.id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                },
            })

            const res = await response.json()
            if (response.ok && res.sucesso) {
                toast.success('Participante removido com sucesso!')
                router.reload({ only: ['visita'] })
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
                                <h2 className="mb-8 text-3xl font-bold text-amber-800 md:text-4xl">
                                    Alterar visita
                                </h2>

                                {errors && Object.keys(errors).length > 0 && (
                                    <div className="mb-4 rounded-lg border border-amber-200 bg-white p-4 text-amber-800">
                                        <ul>
                                            {Object.entries(errors).map(([campo, mensagem]) => (
                                                <li key={campo}>{mensagem}</li>
                                            ))}
                                        </ul>
                                    </div>
                                )}

                                <form
                                    id="visita-form"
                                    onSubmit={(e) => {
                                        e.preventDefault()
                                        handleSubmit()
                                    }}
                                    className="space-y-6"
                                >
                                    <VisitaForm
                                        data={data}
                                        errors={errors}
                                        mode="edit"
                                        hospitais={hospitais}
                                        cidades={cidades}
                                        lideres={lideres}
                                        onCampoChange={handleCampoChange}
                                    />

                                    <div>
                                        <h3 className={painelLabelClass}>Participantes</h3>
                                        {participantes.length === 0 ? (
                                            <p className="mb-3 text-sm text-gray-400">Nenhum participante inscrito.</p>
                                        ) : (
                                            <ul className="mb-4 divide-y divide-gray-100 rounded-xl border border-gray-200 shadow-sm">
                                                {participantes.map((p) => (
                                                    <li
                                                        key={p.id ?? p.voluntario_id}
                                                        className="flex items-center justify-between px-4 py-3 text-sm"
                                                    >
                                                        <div>
                                                            <span className="font-medium text-gray-900">
                                                                {p.voluntario?.name ?? '—'}
                                                            </span>
                                                            <span className="ml-2 text-xs text-gray-500">
                                                                ({labelTipoParticipacao(p.tipo_participacao)}
                                                                {' · '}
                                                                {labelStatusParticipacao(p.status_participacao)})
                                                            </span>
                                                        </div>
                                                        {p.id && (
                                                            <button
                                                                type="button"
                                                                onClick={() => handleRemoverParticipante(p)}
                                                                disabled={removendoId === p.id}
                                                                className="inline-flex items-center gap-1 rounded-lg border border-red-200 px-2.5 py-1 text-xs font-semibold text-red-600 transition hover:bg-red-50 disabled:opacity-50"
                                                                title="Remover participante"
                                                            >
                                                                <Trash2 className="size-3.5" aria-hidden />
                                                                {removendoId === p.id ? 'Removendo...' : 'Remover'}
                                                            </button>
                                                        )}
                                                    </li>
                                                ))}
                                            </ul>
                                        )}

                                        <div className="rounded-2xl border border-gray-200 bg-gray-50/60 p-4">
                                            <h4 className="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-700">
                                                Adicionar participante
                                            </h4>
                                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                                                <select
                                                    value={novoVoluntarioId}
                                                    onChange={(e) => setNovoVoluntarioId(e.target.value)}
                                                    className="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-amber-500 focus:outline-none sm:w-64"
                                                >
                                                    <option value="">Selecione um voluntário...</option>
                                                    {voluntariosDisponiveis.map((v) => (
                                                        <option key={v.id} value={v.id}>
                                                            {v.name}
                                                        </option>
                                                    ))}
                                                </select>

                                                <select
                                                    value={novoTipo}
                                                    onChange={(e) => setNovoTipo(e.target.value as TipoParticipacao)}
                                                    className="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-amber-500 focus:outline-none sm:w-36"
                                                >
                                                    <option value="palhaco">🎪 Palhaço</option>
                                                    <option value="paisana">👔 Paisana</option>
                                                </select>

                                                <button
                                                    type="button"
                                                    onClick={handleAdicionarParticipante}
                                                    disabled={adicionando || !novoVoluntarioId}
                                                    className="inline-flex items-center justify-center gap-1.5 rounded-xl border border-amber-600 bg-white px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-50 disabled:opacity-50"
                                                >
                                                    <UserPlus className="size-4" aria-hidden />
                                                    {adicionando ? 'Adicionando...' : 'Adicionar'}
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="rounded-2xl border border-amber-100 bg-amber-50/40 p-5">
                                        <h3 className="mb-3 text-sm font-semibold uppercase tracking-wide text-amber-900/70">
                                            Relatórios
                                        </h3>
                                        <div className="flex flex-col gap-2 sm:flex-row">
                                            <Link
                                                href={relatoriosIndex.url({ visita: visita.id! })}
                                                className="inline-flex flex-1 items-center justify-center rounded-full border border-amber-200 bg-white px-4 py-2.5 text-sm font-semibold text-amber-800 transition hover:bg-amber-50"
                                            >
                                                Ver relatórios
                                            </Link>
                                            {podeCriarRelatorio(auth.user, visita) && (
                                                <Link
                                                    href={create.url({ visita: visita.id! })}
                                                    className="inline-flex flex-1 items-center justify-center rounded-full border-2 border-amber-600 bg-white px-4 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-50"
                                                >
                                                    Criar relatório
                                                </Link>
                                            )}
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div className="flex flex-col gap-3 border-t bg-white px-8 py-6 sm:flex-row sm:items-center sm:justify-between md:px-12">
                                <Link
                                    href={index().url}
                                    className="inline-flex items-center justify-center gap-2 rounded-full border px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                                >
                                    <ArrowLeft className="size-4" aria-hidden />
                                    Voltar
                                </Link>

                                <button
                                    type="submit"
                                    form="visita-form"
                                    disabled={processing}
                                    className="inline-flex items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 font-semibold text-amber-700 transition hover:bg-amber-50 disabled:opacity-70"
                                >
                                    <Check className="size-4" aria-hidden />
                                    {processing ? 'Salvando...' : 'Salvar'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </PainelLayout>
    )
}

export default Edit
