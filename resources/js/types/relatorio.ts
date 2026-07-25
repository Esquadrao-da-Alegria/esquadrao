import type { TipoRelatorio } from '@/types'

export type DadosFormulario = {
    tipo_relatorio: TipoRelatorio | ''
    ala_unidade_id: number | '' | null
    resumo: string
    feedback: string
    quartos_visitados: number | ''
    pessoas_impactadas: number | ''
    observacao_visitantes_externos: string
    observacoes_gerais: string
}
