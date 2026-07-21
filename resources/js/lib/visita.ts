import type { User, Visita, VisitaParticipante, VisitaStatus, VisitaTipo } from '@/types'
import { temCargo } from '@/lib/utils/user'

export const LIMITE_PARTICIPANTES = 5

const STATUS_ATIVOS = ['confirmado', 'pendente']

export function listarParticipantesAtivos(visita: Visita): VisitaParticipante[] {
    return visita.participantes?.filter(
        (p) => p.papel_na_visita === 'participante' && STATUS_ATIVOS.includes(p.status_participacao),
    ) ?? []
}

export function contarParticipantesAtivos(visita: Visita): number {
    return listarParticipantesAtivos(visita).length
}

export function visitaAtingiuLimite(visita: Visita): boolean {
    return contarParticipantesAtivos(visita) >= LIMITE_PARTICIPANTES
}

export function usuarioJaInscrito(visita: Visita, usuarioId: number): boolean {
    return listarParticipantesAtivos(visita).some((p) => p.voluntario_id === usuarioId)
}

export function participacaoAtivaDoUsuario(visita: Visita, usuarioId: number): VisitaParticipante | null {
    return listarParticipantesAtivos(visita).find((p) => p.voluntario_id === usuarioId) ?? null
}

export function usuarioEhLiderDaVisita(visita: Visita, usuarioId: number): boolean {
    return visita.lider_id !== null && visita.lider_id !== undefined && visita.lider_id === usuarioId
}

export function contarParticipantes(visita: Visita) {
    const lista = listarParticipantesAtivos(visita)

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

export function podeEditarVisita(user: User, visita: Visita): boolean {
    if (visita.lider_id !== null && visita.lider_id !== undefined && user.id === visita.lider_id) {
        return true
    }

    if (temCargo(user, 'administrador') || temCargo(user, 'diretor') || temCargo(user, 'coordenador_geral')) {
        return true
    }

    if (temCargo(user, 'coordenador_local')) {
        const cidadeUsuario = user.voluntario?.cidade_base_id
        const cidadeHospital = visita.hospital?.cidade_id

        return cidadeUsuario != null
            && cidadeHospital != null
            && cidadeUsuario === cidadeHospital
    }

    return false
}

export function labelTipo(tipo: VisitaTipo): string {
    const labels: Record<VisitaTipo, string> = {
        hospital: 'Hospital',
        residencia: 'Residência',
        acao_especial: 'Ação especial',
        outro: 'Outro',
    }
    return labels[tipo] ?? tipo
}

export function extrairData(iso: string): string {
    return iso.slice(0, 10)
}

export function hojeLocal(): string {
    const d = new Date()
    const mes = String(d.getMonth() + 1).padStart(2, '0')
    const dia = String(d.getDate()).padStart(2, '0')
    return `${d.getFullYear()}-${mes}-${dia}`
}

export function extrairHora(iso: string): string {
    return iso.slice(11, 16)
}

export const VISITA_TIPOS: VisitaTipo[] = [
    'hospital', 'residencia', 'acao_especial', 'outro',
]

export const VISITA_STATUS: VisitaStatus[] = [
    'agendada', 'realizada', 'cancelada', 'pendente_relatorio', 'contabilizada', 'nao_contabilizada',
]
