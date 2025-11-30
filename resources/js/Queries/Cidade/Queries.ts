import { index } from "@/routes/json/json/cidades";
import { DiamondMinusIcon } from "lucide-react";

type FiltrosBusca = {
    estado_id?: number;
}

export class Queries {
    static async index(filtros: FiltrosBusca): Promise<[]> {
        try {

            const url = index().url;

            const options = {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            }

            const retorno = await fetch(url, options);

            const dados = await retorno.json();

            if (retorno.ok) {

                console.error(dados.erros);

                return [];
            }

            return dados.dados;
        } catch (error) {

            console.error(error)

            return [];
        }
    }
}