import type { EventoStatus, EventoTipo } from '@/types'

export const EVENTO_TIPOS: EventoTipo[] = ['oficina', 'reuniao', 'evento']

export function classeCardPorStatus(status: EventoStatus): string {
    switch (status) {
        case 'agendado':
            return 'bg-amber-500 text-white border-amber-600'
        case 'cancelado':
            return 'bg-gray-100 text-gray-500 border-gray-200'
        default:
            return 'bg-gray-100 text-gray-500 border-gray-200'
    }
}

export function labelStatus(status: EventoStatus): string {
    const labels: Record<EventoStatus, string> = {
        agendado: 'Agendado',
        cancelado: 'Cancelado',
    }
    return labels[status] ?? status
}

export function labelTipo(tipo: EventoTipo): string {
    const labels: Record<EventoTipo, string> = {
        oficina: 'Oficina',
        reuniao: 'Reunião',
        evento: 'Evento',
    }
    return labels[tipo] ?? tipo
}

export function extrairData(iso: string): string {
    return iso.slice(0, 10)
}

export function extrairHora(iso: string): string {
    return iso.slice(11, 16)
}

export function montarDatetime(data: string, hora: string): string {
    return `${data} ${hora}:00`
}
