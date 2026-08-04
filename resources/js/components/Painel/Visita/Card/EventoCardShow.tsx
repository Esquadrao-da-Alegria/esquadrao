import { type FC } from 'react'
import type { Evento } from '@/types'
import { Calendar } from 'lucide-react'

interface Props {
    evento: Evento
    onClick: () => void
}

const EventoCardShow: FC<Props> = ({ evento, onClick }) => {
    const hora = new Date(evento.data_inicio).toLocaleTimeString('pt-BR', {
        hour: '2-digit',
        minute: '2-digit',
    })

    return (
        <button
            type="button"
            onClick={onClick}
            className="w-full rounded border border-indigo-600 bg-indigo-600 px-1.5 py-0.5 text-left text-xs font-medium text-white transition hover:opacity-85"
        >
            <span className="flex items-center gap-1 truncate">
                <Calendar className="size-3 shrink-0 opacity-80" />
                <span className="truncate">{hora} · {evento.titulo}</span>
            </span>
        </button>
    )
}

export default EventoCardShow
