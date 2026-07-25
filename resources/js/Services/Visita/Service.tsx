import type { DadosFormulario } from '@/types/visita'

type AcaoFormulario = 'criar' | 'editar'

export class Service {
    static montarPayload(data: DadosFormulario, acao: AcaoFormulario) {
        const observacoes = data.observacoes || null

        if (acao === 'editar') {
            return {
                data: data.data,
                hora_inicio: data.hora_inicio,
                hora_fim: data.hora_fim,
                tipo: data.tipo,
                lider_id: data.lider_id,
                status: data.status,
                observacoes,
            }
        }

        return {
            hospital_id: data.hospital_id,
            ala_unidade_id: data.ala_unidade_id || null,
            data: data.data,
            hora_inicio: data.hora_inicio,
            hora_fim: data.hora_fim,
            tipo: data.tipo,
            lider_id: data.lider_id,
            observacoes,
        }
    }
}
