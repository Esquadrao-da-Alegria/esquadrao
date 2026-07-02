import { destroy, store } from '@/routes/visitas/participantes'
import { obterCsrfToken } from '@/utils/form'
import type { TipoParticipacao } from '@/types'

type RetornoPadrao = {
    sucesso: boolean
    dados: unknown
    erros: string[]
}

type DadosStore = {
    visita_id: number
    tipo_participacao: TipoParticipacao
}

export class Queries {
    static async store(dados: DadosStore): Promise<RetornoPadrao> {
        try {
            const url = store({ visita: dados.visita_id }).url

            const options = {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': obterCsrfToken(),
                },
                body: JSON.stringify({
                    tipo_participacao: dados.tipo_participacao,
                }),
            }

            const retorno = await fetch(url, options)

            return await retorno.json()
        } catch (error) {
            console.error(error)

            return { sucesso: false, dados: [], erros: ['Erro ao participar da visita!'] }
        }
    }

    static async destroy(visitaId: number, participanteId: number): Promise<RetornoPadrao> {
        try {
            const url = destroy({ visita: visitaId, participante: participanteId }).url

            const options = {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': obterCsrfToken(),
                },
            }

            const retorno = await fetch(url, options)

            return await retorno.json()
        } catch (error) {
            console.error(error)

            return { sucesso: false, dados: [], erros: ['Erro ao cancelar inscrição!'] }
        }
    }
}
