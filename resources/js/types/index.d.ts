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
    mensagem_alerta?: string | null;
    link_convite?: string | null;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    status?: 'ativo' | 'convite_enviado' | 'inativo';
    convite_enviado_em?: string | null;
    convite_expira_em?: string | null;
    convite_status?:
        | 'PENDENTE'
        | 'ENVIADO'
        | 'UTILIZADO'
        | 'EXPIRADO'
        | 'CANCELADO'
        | null;
    convite_utilizado_em?: string | null;
    inativado_em?: string | null;
    cidade_base_id?: number | null;
    cidade_base?: Cidade | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    cargos?: Cargo[];
    voluntario?: {
        id: number;
        cidade_base_id?: number | null;
    };
    [key: string]: unknown; // This allows for additional properties...
}

export interface Cargo {
    id: number;
    nome: string;
    slug:
        | 'administrador'
        | 'diretor'
        | 'coordenador_geral'
        | 'coordenador_local'
        | 'artista'
        | 'psicologia'
        | 'apoio'
        | 'voluntario';
    created_at?: string;
    updated_at?: string;
}

// RECURSOS

export interface Estado {
    id: number;
    nome: string;
    sigla: number;
}

export interface Cidade {
    id: number;
    nome: string;
    estado_id: number;
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
    id?: number;
    cidade_id: number;
    nome: string;
    cnpj: string;
    endereco: string;
    telefone: string;
    email: string;
    ativo: boolean;
    url_foto: string | null;
    observacoes?: string;
    alas?: AlaHospital[];
    created_at?: string;
    updated_at?: string;
}

export interface AlaHospital {
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

// VISITAS

export type VisitaTipo =
    | 'hospital'
    | 'residencia'
    | 'acao_especial'
    | 'outro';

export type VisitaStatus =
    | 'agendada'
    | 'realizada'
    | 'cancelada'
    | 'pendente_relatorio'
    | 'contabilizada'
    | 'nao_contabilizada';

export type VisitaOrigem = 'sistema' | 'importacao' | 'outro';

export type TipoParticipacao = 'palhaco' | 'paisana';

export type PapelNaVisita = 'participante' | 'relator';

export type StatusParticipacao = 'confirmado' | 'pendente' | 'cancelado' | 'falta';

export interface Visita {
    id?: number;
    hospital_id: number;
    ala_unidade_id?: number | null;
    criado_por_id: number;
    lider_id?: number | null;
    inicio_em: string;
    fim_em: string;
    tipo: VisitaTipo;
    status: VisitaStatus;
    origem: VisitaOrigem;
    observacoes?: string | null;
    created_at?: string;
    updated_at?: string;
    hospital?: Hospital;
    alaUnidade?: AlaHospital | null;
    criadoPor?: User;
    lider?: User | null;
    participantes?: VisitaParticipante[];
}

export interface VisitaParticipante {
    id?: number;
    visita_id: number;
    voluntario_id: number;
    tipo_participacao: TipoParticipacao;
    papel_na_visita: PapelNaVisita;
    status_participacao: StatusParticipacao;
    created_at?: string;
    updated_at?: string;
    visita?: Visita;
    voluntario?: User;
}

export type TipoRelatorio = 'palhaco' | 'paisana' | 'geral';

export interface VisitaRelatorio {
    id?: number;
    visita_id: number;
    autor_id: number;
    tipo_relatorio: TipoRelatorio;
    resumo: string;
    feedback?: string | null;
    quartos_visitados?: number | null;
    pessoas_impactadas?: number | null;
    observacao_visitantes_externos?: string | null;
    observacoes_gerais?: string | null;
    enviado_em: string;
    fora_do_prazo: boolean;
    created_at?: string;
    updated_at?: string;
    autor?: User;
    visita?: Visita;
}
