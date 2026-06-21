import { type FC } from 'react'
import type { Visita } from '@/types'
import { classeCardPorStatus, labelStatus } from '@/lib/visita'

interface Props {
    dia: Date | null
    visitas: Visita[]
    onFechar: () => void
    onSelecionarVisita: (visita: Visita) => void
}

const Show: FC<Props> = ({ dia, visitas, onFechar, onSelecionarVisita }) => {
    if (!dia) return null

    const formatarDia = (d: Date) =>
        d.toLocaleDateString('pt-BR', {
            weekday: 'long',
            day: '2-digit',
            month: 'long',
        })

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            onClick={onFechar}
        >
            <div
                className="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="mb-4 flex items-center justify-between gap-4">
                    <h2 className="text-base font-semibold capitalize text-gray-900">
                        {formatarDia(dia)}
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

                <ul className="space-y-2">
                    {visitas.map((visita) => {
                        const hora = new Date(visita.inicio_em).toLocaleTimeString('pt-BR', {
                            hour: '2-digit',
                            minute: '2-digit',
                        })

                        return (
                            <li key={visita.id}>
                                <button
                                    type="button"
                                    onClick={() => onSelecionarVisita(visita)}
                                    className={`w-full rounded-lg border px-3 py-2 text-left text-sm transition hover:opacity-80 ${classeCardPorStatus(visita.status)}`}
                                >
                                    <span className="block font-medium">
                                        {hora} · {visita.hospital?.nome ?? '—'}
                                    </span>
                                    <span className="block text-xs opacity-75">
                                        {labelStatus(visita.status)}
                                    </span>
                                </button>
                            </li>
                        )
                    })}
                </ul>
            </div>
        </div>
    )
}

export default Show
