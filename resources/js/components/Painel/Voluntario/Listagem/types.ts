import { User } from '@/types';

export type StatusKey = 'pendente' | 'aceito' | 'expirado' | 'cancelado';

export type StatusFiltro =
    StatusKey | 'afastados' | 'ativos' | 'todos' | string;

export type AbaKey = 'voluntarios' | 'convidados';

export interface StatusOption {
    key: StatusKey;
    label: string;
    className: string;
}

export interface VoluntarioListagem extends User {
    statusKey: StatusKey;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedVoluntarios {
    data: User[];
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
    current_page: number;
    last_page: number;
}

export interface CidadeOption {
    id: number;
    nome: string;
}

export interface VoluntarioFiltros {
    aba?: AbaKey;
    busca?: string;
    status?: StatusFiltro;
    cidade_id?: number | 'todas';
}

export type StatusCounters = Partial<Record<StatusKey | AbaKey, number>>;
