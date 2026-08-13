import type { DadosFormulario } from '@/types/relatorio'

export class Service {
    static montarPayload(data: DadosFormulario) {
        return {
            ...data,
            ala_unidade_id: data.ala_unidade_id || null,
            unidades_visitadas: data.unidades_visitadas || null,
            quartos_visitados: data.quartos_visitados === '' ? null : data.quartos_visitados,
            pessoas_impactadas: data.pessoas_impactadas === '' ? null : data.pessoas_impactadas,
            feedback: data.feedback || null,
            observacao_visitantes_externos: data.observacao_visitantes_externos || null,
            observacoes_gerais: data.observacoes_gerais || null,
        }
    }
}
