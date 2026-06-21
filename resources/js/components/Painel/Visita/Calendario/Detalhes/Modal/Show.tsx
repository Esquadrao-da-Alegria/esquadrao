import { type FC } from 'react'

import Modal from '@/components/Modal/Show'
import { contarParticipantes, labelStatus } from '@/lib/visita'
import type { Visita } from '@/types'

interface Props {
    visita: Visita | null
    onFechar: () => void
}

const Show: FC<Props> = ({ visita, onFechar }) => {
    const participantes = visita ? contarParticipantes(visita) : null

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
        <Modal isOpen={visita !== null} onClose={onFechar} className="max-w-md">
            {visita && (
                <div className="p-6">
                    <div className="mb-4 flex items-start justify-between gap-4">
                        <h2 className="text-lg font-semibold text-gray-900">
                            Detalhes da visita
                        </h2>
                        <button
                            type="button"
                            onClick={onFechar}
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
                                {participantes && participantes.palhaco > 0 && (
                                    <span className="mr-2">🎪 {participantes.palhaco} palhaço{participantes.palhaco !== 1 ? 's' : ''}</span>
                                )}
                                {participantes && participantes.paisana > 0 && (
                                    <span>👔 {participantes.paisana} paisana{participantes.paisana !== 1 ? 's' : ''}</span>
                                )}
                                {participantes && participantes.palhaco === 0 && participantes.paisana === 0 && '—'}
                            </dd>
                        </div>

                        {visita.observacoes && (
                            <div>
                                <dt className="text-xs font-medium uppercase text-gray-400">Observações</dt>
                                <dd className="mt-0.5 text-gray-700">{visita.observacoes}</dd>
                            </div>
                        )}
                    </dl>
                </div>
            )}
        </Modal>
    )
}

export default Show
