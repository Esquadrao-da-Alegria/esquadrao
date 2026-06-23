import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    eh_administrador?: boolean;
    mensagem_sucesso?: string | null;
    mensagem_erro?: string | null;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    cargos?: Cargo[];
    [key: string]: unknown; // This allows for additional properties...
}

export interface Cargo {
    id: number;
    nome: string;
    slug: 'administrador' | 'diretor' | 'coordenador_geral' | 'coordenador_local' | 'artista' | 'psicologia' | 'apoio' | 'voluntario';
    created_at?: string;
    updated_at?: string;
}

// RECURSOS

export interface Estado {
    id: number;
    nome: string,
    sigla: number,
}

export interface Cidade {
    id: number;
    nome: string,
    estado_id: number,
}

export interface Endereco {
    id: number;

    // Relacionamento polimórfico
    recurso_id: number;
    recurso_tipo: string;

    // Relacionamento com cidade
    cidade_id: number;

    // Dados do endereço
    logradouro?: string | null;
    numero?: string | null;
    complemento?: string | null;
    bairro?: string | null;
    cep?: string | null;

    // Timestamps
    created_at: string;
    updated_at: string;
}

export interface Hospital {
    id?: number
    cidade_id: number
    nome: string
    cnpj: string
    endereco: string
    telefone: string
    email: string
    ativo: boolean
    url_foto: string | null
    observacoes?: string
    alas?: AlaHospital[]
    created_at?: string
    updated_at?: string
}

interface AlaHospital {
    id?: number;
    hospital_id: number;
    nome: string;
    observacoes?: string;
    created_at?: string;
    updated_at?: string;
}
export type EventoTipo = 'oficina' | 'reuniao' | 'evento';
export type EventoStatus = 'agendado' | 'cancelado';

export interface EventoParticipante {
    id: number;
    name: string;
    email: string;
    pivot?: {
        status: 'inscrito' | 'cancelado';
        inscrito_em: string | null;
        cancelado_em: string | null;
    };
}

export interface Evento {
    id: number;
    titulo: string;
    tipo: EventoTipo;
    descricao: string | null;
    local: string | null;
    data_inicio: string;
    data_fim: string | null;
    limite_participantes: number | null;
    status: EventoStatus;
    responsavel_id: number | null;
    criado_por_id: number;
    motivo_cancelamento: string | null;
    cancelado_em: string | null;
    cancelado_por_id: number | null;
    participantes_ativos_count?: number;
    responsavel?: User | null;
    participantes_ativos?: EventoParticipante[];
    created_at: string;
    updated_at: string;
}
