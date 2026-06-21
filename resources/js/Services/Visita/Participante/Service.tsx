import { router } from '@inertiajs/react'

import { Queries } from '@/Queries/Visita/Participante/Queries'
import { toastErro, toastSucesso } from '@/lib/utils/toast'
import type { TipoParticipacao } from '@/types'

export class Service {
    static async participar(visitaId: number, tipoParticipacao: TipoParticipacao): Promise<boolean> {
        const retorno = await Queries.store({
            visita_id: visitaId,
            tipo_participacao: tipoParticipacao,
        })

        if (retorno.sucesso) {
            toastSucesso('Inscrição realizada com sucesso!')
            router.reload()
            return true
        }

        toastErro(retorno.erros[0] ?? 'Erro ao participar da visita!')
        return false
    }
}
