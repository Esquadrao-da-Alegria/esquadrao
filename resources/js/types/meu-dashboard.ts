export interface MedidaMeuDashboard {
    oferecidos: number;
    presencas: number;
    percentual: number | null;
    dados_incompletos: boolean;
}

export interface HistoricoMeuDashboard {
    id: number;
    tipo: 'visita' | 'reuniao' | 'oficina';
    titulo: string;
    data: string;
    local: string;
    cidade: string;
    ala: string | null;
    tipo_participacao: string | null;
    situacao: string;
    motivo: string;
    relatorio: 'pendente' | 'enviado' | 'fora_do_prazo' | null;
    impacto_estimado: number | null;
}

export interface FiltrosMeuDashboard {
    periodo_tipo: 'mes' | 'semestre' | 'ano' | 'personalizado';
    ano?: number | null;
    mes?: number | null;
    semestre?: number | null;
    data_inicio?: string | null;
    data_fim?: string | null;
    cidade_id?: number | null;
    atividade?: string | null;
}

export interface PaginacaoMeuDashboard<T> {
    data: T[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}
