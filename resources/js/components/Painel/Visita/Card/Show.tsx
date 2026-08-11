import { type FC } from 'react'
import type { Visita } from '@/types/visita'
import { classeCardPorStatus, tituloVisita } from '@/lib/visita'

interface Props {
    visita: Visita
    onClick: () => void
}

const Show: FC<Props> = ({ visita, onClick }) => {
    const hora = new Date(visita.inicio_em).toLocaleTimeString('pt-BR', {
        hour: '2-digit',
        minute: '2-digit',
    })

    return (
        <button
            type="button"
            onClick={onClick}
            className={`w-full rounded border px-1.5 py-0.5 text-left text-xs font-medium transition hover:opacity-80 ${classeCardPorStatus(visita)}`}
        >
            <span className="block truncate">{hora} · {tituloVisita(visita)}</span>
        </button>
    )
}

export default Show
