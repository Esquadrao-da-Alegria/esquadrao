import type { Visita, VisitaStatus } from '@/types'

export function contarParticipantes(visita: Visita) {
    const lista = visita.participantes?.filter((p) => p.papel_na_visita === 'participante') ?? []

    return {
        palhaco: lista.filter((p) => p.tipo_participacao === 'palhaco').length,
        paisana: lista.filter((p) => p.tipo_participacao === 'paisana').length,
    }
}

export function classeCardPorStatus(status: VisitaStatus): string {
    switch (status) {
        case 'agendada':
            return 'bg-amber-500 text-white border-amber-600'
        case 'realizada':
        case 'contabilizada':
            return 'bg-green-100 text-green-800 border-green-200'
        case 'pendente_relatorio':
        case 'nao_contabilizada':
            return 'bg-orange-100 text-orange-800 border-orange-200'
        case 'cancelada':
            return 'bg-gray-100 text-gray-500 border-gray-200'
        default:
            return 'bg-gray-100 text-gray-500 border-gray-200'
    }
}

export function labelStatus(status: VisitaStatus): string {
    const labels: Record<VisitaStatus, string> = {
        agendada: 'Agendada',
        realizada: 'Realizada',
        cancelada: 'Cancelada',
        pendente_relatorio: 'Pend. relatório',
        contabilizada: 'Contabilizada',
        nao_contabilizada: 'Não contabilizada',
    }
    return labels[status] ?? status
}
