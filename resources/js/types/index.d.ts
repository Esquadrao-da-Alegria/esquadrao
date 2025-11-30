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
    [key: string]: unknown; // This allows for additional properties...
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
    created_at?: string
    updated_at?: string
}