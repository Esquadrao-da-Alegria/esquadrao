export interface MedidaPresenca {
    oferecidos: number;
    presencas: number;
    percentual: number | null;
    dados_incompletos: boolean;
}

export interface CompensacaoMensal {
    mes: string;
    meta: number;
    visitas: number;
    saldo: number;
    credito_anterior_utilizado: number;
    debito_anterior_compensado: number;
    credito_transferido: number;
    debito_transferido: number;
    debito_expirado: number;
    situacao: string;
}

export interface AcompanhamentoParticipante {
    id: number;
    nome: string;
    cidade: string;
    cidade_id: number | null;
    cargos: string[];
    tipo_atuacao: string;
    visitas_validas: number;
    meta_mensal: number | null;
    saldo_atual: number | null;
    compensacao_atual: string | null;
    compensacoes: CompensacaoMensal[];
    reunioes: MedidaPresenca;
    oficinas: MedidaPresenca;
    ultima_atividade: string | null;
    dias_sem_atividade: number | null;
    relatorios_pendentes: number;
    relatorios_fora_prazo: number;
    situacao: string;
}

export interface FiltrosParticipante {
    periodo_tipo: 'mes' | 'semestre' | 'ano';
    ano: number;
    mes?: number | null;
    semestre?: number | null;
    cidade_id?: number | null;
    visao_global?: boolean;
    busca?: string | null;
    participante_id?: number | null;
    cargo_id?: number | null;
    tipo_atuacao?: string | null;
    situacao?: string | null;
    atividade?: string | null;
}
