export interface FiltrosDashboardHospital {
    mes_inicio: string;
    mes_fim: string;
    cidade_id: number | null;
    hospital_id: number | null;
    ala_id: number | null;
    visao_global?: boolean;
}

export interface OpcaoDashboard {
    id: number;
    nome: string;
}

export interface IndicadoresDashboardHospital {
    total_visitas: number;
    hospitais_visitados: number;
    total_participacoes: number;
    media_participantes: number;
    impacto_estimado: number;
    visitas_com_impacto: number;
    visitas_sem_impacto: number;
}

export interface EvolucaoDashboardHospital {
    mes: string;
    rotulo: string;
    total: number;
}

export interface ResumoDashboardHospital {
    id: number;
    nome: string;
    cidade: string;
    total_visitas: number;
    total_participacoes: number;
    media_participantes: number;
    impacto_estimado: number;
    possui_alas: boolean;
}

export interface AlaDashboardHospital {
    id: number | null;
    nome: string;
    total_visitas: number;
}

export interface VisitaDashboardHospital {
    id: number;
    inicio_em: string;
    status: string;
    ala: string;
    participantes: number;
    impacto_estimado: number | null;
}

export interface PaginacaoDashboard<T> {
    data: T[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}
