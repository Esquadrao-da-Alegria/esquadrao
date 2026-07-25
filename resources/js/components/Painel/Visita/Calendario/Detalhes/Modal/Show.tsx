import { Link, usePage } from '@inertiajs/react'
import { type FC, useEffect, useState } from 'react'

import Modal from '@/components/Modal/Show'
import { Button } from '@/components/ui/button'
import {
    contarParticipantes,
    labelStatus,
    listarParticipantesAtivos,
    participacaoAtivaDoUsuario,
    podeEditarVisita,
    usuarioEhLiderDaVisita,
    usuarioJaInscrito,
    visitaAtingiuLimite,
} from '@/lib/visita'
import { toastInfo } from '@/lib/utils/toast'
import { edit } from '@/routes/visitas'
import { Service } from '@/Services/Visita/Participante/Service'
import type { SharedData, TipoParticipacao, Visita } from '@/types'

interface Props {
    visita: Visita | null
    onFechar: () => void
}

type Passo = 'detalhes' | 'inscricao'

const Show: FC<Props> = ({ visita, onFechar }) => {
    const { auth } = usePage<SharedData>().props
    const [passo, setPasso] = useState<Passo>('detalhes')
    const [tipo, setTipo] = useState<TipoParticipacao | null>(null)
    const [enviando, setEnviando] = useState(false)
    const [cancelando, setCancelando] = useState(false)

    const participantes = visita ? contarParticipantes(visita) : null
    const participantesAtivos = visita ? listarParticipantesAtivos(visita) : []
    const jaInscrito = visita ? usuarioJaInscrito(visita, auth.user.id) : false
    const participacaoAtiva = visita ? participacaoAtivaDoUsuario(visita, auth.user.id) : null
    const ehLider = visita ? usuarioEhLiderDaVisita(visita, auth.user.id) : false
    const visitaAgendada = visita?.status === 'agendada'

    useEffect(() => {
        if (visita === null) {
            setPasso('detalhes')
            setTipo(null)
            setEnviando(false)
            setCancelando(false)
        }
    }, [visita])

    const fecharModal = () => {
        setPasso('detalhes')
        setTipo(null)
        setEnviando(false)
        setCancelando(false)
        onFechar()
    }

    const handleParticipar = () => {
        if (!visita) {
            return
        }

        if (visitaAtingiuLimite(visita)) {
            toastInfo('Visita atingiu limite de participantes')
            return
        }

        setPasso('inscricao')
    }

    const handleConfirmar = async () => {
        if (!visita || !tipo || enviando) {
            return
        }

        setEnviando(true)

        const sucesso = await Service.participar(visita.id!, tipo)

        setEnviando(false)

        if (sucesso) {
            fecharModal()
        }
    }

    const handleCancelarInscricao = async () => {
        if (!visita || !participacaoAtiva?.id || cancelando) {
            return
        }

        setCancelando(true)

        const sucesso = await Service.cancelar(visita.id!, participacaoAtiva.id)

        setCancelando(false)

        if (sucesso) {
            fecharModal()
        }
    }

    const formatarData = (d: Date) =>
        d.toLocaleDateString('pt-BR', {
            weekday: 'long',
            day: '2-digit',
            month: 'long',
            year: 'numeric',
        })

    const formatarHora = (d: Date) =>
        d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })

    return (
        <Modal isOpen={visita !== null} onClose={fecharModal} className="max-w-md">
            {visita && passo === 'detalhes' && (
                <div className="p-6">
                    <div className="mb-4 flex items-start justify-between gap-4">
                        <h2 className="text-lg font-semibold text-gray-900">
                            Detalhes da visita
                        </h2>
                        <button
                            type="button"
                            onClick={fecharModal}
                            className="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700"
                            aria-label="Fechar"
                        >
                            ✕
                        </button>
                    </div>

                    <dl className="space-y-3 text-sm">
                        <div>
                            <dt className="text-xs font-medium uppercase text-gray-400">Hospital</dt>
                            <dd className="mt-0.5 font-medium text-gray-900">{visita.hospital?.nome ?? '—'}</dd>
                        </div>

                        {visita.alaUnidade && (
                            <div>
                                <dt className="text-xs font-medium uppercase text-gray-400">Ala / Unidade</dt>
                                <dd className="mt-0.5 text-gray-700">{visita.alaUnidade.nome}</dd>
                            </div>
                        )}

                        <div>
                            <dt className="text-xs font-medium uppercase text-gray-400">Data</dt>
                            <dd className="mt-0.5 text-gray-700 capitalize">
                                {formatarData(new Date(visita.inicio_em))}
                            </dd>
                        </div>

                        <div>
                            <dt className="text-xs font-medium uppercase text-gray-400">Horário</dt>
                            <dd className="mt-0.5 text-gray-700">
                                {formatarHora(new Date(visita.inicio_em))} – {formatarHora(new Date(visita.fim_em))}
                            </dd>
                        </div>

                        <div>
                            <dt className="text-xs font-medium uppercase text-gray-400">Status</dt>
                            <dd className="mt-0.5">
                                <span className="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                    {labelStatus(visita.status)}
                                </span>
                            </dd>
                        </div>

                        {visita.lider && (
                            <div>
                                <dt className="text-xs font-medium uppercase text-gray-400">Líder</dt>
                                <dd className="mt-0.5 text-gray-700">{visita.lider.name}</dd>
                            </div>
                        )}

                        <div>
                            <dt className="text-xs font-medium uppercase text-gray-400">Participantes</dt>
                            <dd className="mt-0.5 text-gray-700">
                                {participantesAtivos.length > 0 ? (
                                    <ul className="space-y-1">
                                        {participantesAtivos.map((participante) => (
                                            <li key={participante.id ?? participante.voluntario_id}>
                                                {participante.tipo_participacao === 'palhaco' ? '🎪' : '👔'}{' '}
                                                {participante.voluntario?.name ?? '—'}
                                            </li>
                                        ))}
                                    </ul>
                                ) : '—'}
                                {participantes && participantesAtivos.length > 0 && (
                                    <span className="mt-1 block text-xs text-gray-400">
                                        {participantes.palhaco} palhaço{participantes.palhaco !== 1 ? 's' : ''}
                                        {' · '}
                                        {participantes.paisana} paisana{participantes.paisana !== 1 ? 's' : ''}
                                    </span>
                                )}
                            </dd>
                        </div>

                        {visita.observacoes && (
                            <div>
                                <dt className="text-xs font-medium uppercase text-gray-400">Observações</dt>
                                <dd className="mt-0.5 text-gray-700">{visita.observacoes}</dd>
                            </div>
                        )}
                    </dl>

                    {podeEditarVisita(auth.user, visita) && (
                        <div className="mt-6">
                            <Link
                                href={edit({ visita: visita.id! }).url}
                                onClick={fecharModal}
                                className="inline-flex w-full items-center justify-center rounded-lg border border-amber-600 bg-white px-4 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-50"
                            >
                                Visualizar Detalhes
                            </Link>
                        </div>
                    )}

                    {visitaAgendada && (
                        <div className="mt-6">
                            {jaInscrito ? (
                                ehLider ? (
                                    <Button className="w-full" disabled>
                                        Altere o líder antes de cancelar
                                    </Button>
                                ) : (
                                    <Button
                                        className="w-full"
                                        variant="outline"
                                        onClick={handleCancelarInscricao}
                                        disabled={cancelando}
                                    >
                                        {cancelando ? 'Cancelando...' : 'Cancelar inscrição'}
                                    </Button>
                                )
                            ) : (
                                <Button className="w-full" onClick={handleParticipar}>
                                    Participar
                                </Button>
                            )}
                        </div>
                    )}
                </div>
            )}

            {visita && passo === 'inscricao' && (
                <div className="p-6">
                    <div className="mb-4 flex items-start justify-between gap-4">
                        <h2 className="text-lg font-semibold text-gray-900">
                            Escolha o tipo de participação
                        </h2>
                        <button
                            type="button"
                            onClick={fecharModal}
                            className="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700"
                            aria-label="Fechar"
                        >
                            ✕
                        </button>
                    </div>

                    <div className="space-y-3">
                        <button
                            type="button"
                            onClick={() => setTipo('palhaco')}
                            className={`w-full rounded-lg border p-4 text-left transition-colors ${
                                tipo === 'palhaco'
                                    ? 'border-amber-500 bg-amber-50'
                                    : 'border-gray-200 hover:border-gray-300'
                            }`}
                        >
                            <span className="mr-2">🎪</span>
                            Palhaço
                        </button>

                        <button
                            type="button"
                            onClick={() => setTipo('paisana')}
                            className={`w-full rounded-lg border p-4 text-left transition-colors ${
                                tipo === 'paisana'
                                    ? 'border-amber-500 bg-amber-50'
                                    : 'border-gray-200 hover:border-gray-300'
                            }`}
                        >
                            <span className="mr-2">👔</span>
                            Paisana
                        </button>
                    </div>

                    <div className="mt-6 flex gap-3">
                        <Button
                            variant="outline"
                            className="flex-1"
                            onClick={() => setPasso('detalhes')}
                            disabled={enviando}
                        >
                            Voltar
                        </Button>
                        <Button
                            className="flex-1"
                            onClick={handleConfirmar}
                            disabled={!tipo || enviando}
                        >
                            {enviando ? 'Enviando...' : 'Confirmar'}
                        </Button>
                    </div>
                </div>
            )}
        </Modal>
    )
}

export default Show
