import type { AlaHospital, Hospital, User } from '@/types'

export type VisitaTipo =
    | 'hospital'
    | 'residencia'
    | 'acao_especial'
    | 'outro'

export type VisitaStatus =
    | 'agendada'
    | 'realizada'
    | 'cancelada'
    | 'pendente_relatorio'
    | 'contabilizada'
    | 'nao_contabilizada'

export type VisitaOrigem = 'sistema' | 'importacao' | 'outro'

export type TipoParticipacao = 'palhaco' | 'paisana'

export type PapelNaVisita = 'participante' | 'relator'

export type StatusParticipacao = 'confirmado' | 'pendente' | 'cancelado' | 'falta'

export type TipoRelatorio = 'palhaco' | 'paisana' | 'geral'

export interface Visita {
    id?: number
    hospital_id: number
    ala_unidade_id?: number | null
    criado_por_id: number
    lider_id?: number | null
    inicio_em: string
    fim_em: string
    tipo: VisitaTipo
    status: VisitaStatus
    origem: VisitaOrigem
    observacoes?: string | null
    created_at?: string
    updated_at?: string
    hospital?: Hospital
    alaUnidade?: AlaHospital | null
    criadoPor?: User
    lider?: User | null
    participantes?: VisitaParticipante[]
}

export interface VisitaParticipante {
    id?: number
    visita_id: number
    voluntario_id: number
    tipo_participacao: TipoParticipacao
    papel_na_visita: PapelNaVisita
    status_participacao: StatusParticipacao
    created_at?: string
    updated_at?: string
    visita?: Visita
    voluntario?: User
}

export interface VisitaRelatorio {
    id?: number
    visita_id: number
    autor_id: number
    tipo_relatorio: TipoRelatorio
    ala_unidade_id?: number | null
    resumo: string
    feedback?: string | null
    quartos_visitados?: number | null
    pessoas_impactadas?: number | null
    observacao_visitantes_externos?: string | null
    observacoes_gerais?: string | null
    enviado_em: string
    fora_do_prazo: boolean
    created_at?: string
    updated_at?: string
    autor?: User
    alaUnidade?: AlaHospital | null
    visita?: Visita
}

export type DadosFormulario = {
    hospital_id: number | ''
    ala_unidade_id: number | '' | null
    data: string
    hora_inicio: string
    hora_fim: string
    tipo: VisitaTipo | ''
    lider_id: number | ''
    status?: VisitaStatus
    observacoes: string
}
