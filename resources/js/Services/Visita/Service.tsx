import type { DadosFormulario } from '@/types/visita';

type AcaoFormulario = 'criar' | 'editar';

export class Service {
    static montarPayload(data: DadosFormulario, acao: AcaoFormulario) {
        const observacoes = data.observacoes || null;
        const limite_participantes =
            data.limite_participantes !== '' &&
            data.limite_participantes !== null &&
            data.limite_participantes !== undefined
                ? Number(data.limite_participantes)
                : null;

        const exigeHospital =
            data.tipo === 'hospital' || data.tipo === 'residencia';
        const hospital_id = exigeHospital
            ? data.hospital_id || null
            : data.hospital_id || null;
        const ala_unidade_id = hospital_id ? data.ala_unidade_id || null : null;

        if (acao === 'editar') {
            return {
                hospital_id,
                ala_unidade_id,
                data: data.data,
                hora_inicio: data.hora_inicio,
                hora_fim: data.hora_fim,
                tipo: data.tipo,
                limite_participantes,
                lider_id: data.lider_id,
                status: data.status,
                observacoes,
            };
        }

        return {
            hospital_id,
            ala_unidade_id,
            data: data.data,
            hora_inicio: data.hora_inicio,
            hora_fim: data.hora_fim,
            tipo: data.tipo,
            limite_participantes,
            lider_id: data.lider_id,
            observacoes,
        };
    }
}
