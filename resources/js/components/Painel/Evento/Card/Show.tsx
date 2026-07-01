import { type FC } from 'react'

import { classeCardPorStatus } from '@/lib/evento'
import type { Evento } from '@/types'

interface Props {
    evento: Evento
    onClick: () => void
}

const Show: FC<Props> = ({ evento, onClick }) => {
    const hora = new Date(evento.data_inicio).toLocaleTimeString('pt-BR', {
        hour: '2-digit',
        minute: '2-digit',
    })

    return (
        <button
            type="button"
            onClick={onClick}
            className={`w-full rounded border px-1.5 py-0.5 text-left text-xs font-medium transition hover:opacity-80 ${classeCardPorStatus(evento.status)}`}
        >
            <span className="block truncate">{hora} · {evento.titulo}</span>
        </button>
    )
}

export default Show
