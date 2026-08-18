import { destroy, store } from '@/routes/visitas/participantes';
import type { TipoParticipacao } from '@/types/visita';
import { obterCsrfHeaders } from '@/utils/form';

type RetornoPadrao = {
    sucesso: boolean;
    dados: unknown;
    erros: string[];
};

type DadosStore = {
    visita_id: number;
    tipo_participacao: TipoParticipacao;
};

export class Queries {
    static async store(dados: DadosStore): Promise<RetornoPadrao> {
        try {
            const url = store({ visita: dados.visita_id }).url;

            const options = {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    ...obterCsrfHeaders(),
                },
                body: JSON.stringify({
                    tipo_participacao: dados.tipo_participacao,
                }),
            };

            const retorno = await fetch(url, options);

            return await this.processarRetorno(
                retorno,
                'Erro ao participar da visita!',
            );
        } catch (error) {
            console.error(error);

            return {
                sucesso: false,
                dados: [],
                erros: ['Erro ao participar da visita!'],
            };
        }
    }

    static async destroy(
        visitaId: number,
        participanteId: number,
    ): Promise<RetornoPadrao> {
        try {
            const url = destroy({
                visita: visitaId,
                participante: participanteId,
            }).url;

            const options = {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    ...obterCsrfHeaders(),
                },
            };

            const retorno = await fetch(url, options);

            return await this.processarRetorno(
                retorno,
                'Erro ao cancelar inscrição!',
            );
        } catch (error) {
            console.error(error);

            return {
                sucesso: false,
                dados: [],
                erros: ['Erro ao cancelar inscrição!'],
            };
        }
    }

    private static async processarRetorno(
        retorno: Response,
        mensagemPadrao: string,
    ): Promise<RetornoPadrao> {
        try {
            const dados = (await retorno.json()) as RetornoPadrao;

            if (
                typeof dados.sucesso === 'boolean' &&
                Array.isArray(dados.erros)
            ) {
                return dados;
            }
        } catch {
            return { sucesso: false, dados: [], erros: [mensagemPadrao] };
        }

        return { sucesso: false, dados: [], erros: [mensagemPadrao] };
    }
}
