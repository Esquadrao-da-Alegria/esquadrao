import type { Visita, VisitaStatus } from '@/types'

export const LIMITE_PARTICIPANTES = 5

const STATUS_ATIVOS = ['confirmado', 'pendente']

function listaAtivos(visita: Visita) {
    return visita.participantes?.filter(
        (p) => p.papel_na_visita === 'participante' && STATUS_ATIVOS.includes(p.status_participacao),
    ) ?? []
}

export function contarParticipantesAtivos(visita: Visita): number {
    return listaAtivos(visita).length
}

export function visitaAtingiuLimite(visita: Visita): boolean {
    return contarParticipantesAtivos(visita) >= LIMITE_PARTICIPANTES
}

export function usuarioJaInscrito(visita: Visita, usuarioId: number): boolean {
    return listaAtivos(visita).some((p) => p.voluntario_id === usuarioId)
}

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
