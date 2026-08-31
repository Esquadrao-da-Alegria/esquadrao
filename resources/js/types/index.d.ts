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
    eh_gestor?: boolean;
    permissoes_dashboards: Record<PermissaoDashboard, boolean>;
    mensagem_sucesso?: string | null;
    mensagem_erro?: string | null;
    mensagem_alerta?: string | null;
    link_convite?: string | null;
    [key: string]: unknown;
}

export type PermissaoDashboard =
    | 'dashboard.meu'
    | 'dashboard.visao_geral'
    | 'dashboard.visitas_por_hospital'
    | 'dashboard.visitas_por_participante';

export type MotivoAfastamento =
    'atestado_medico' | 'licenca_pessoal' | 'estudos' | 'outro';

export type StatusAfastamento =
    'ativo' | 'encerrado' | 'prorrogado' | 'cancelado';

export interface VoluntarioAfastamento {
    id: number;
    voluntario_id: number;
    registrado_por_id: number | null;
    registrado_por?: { id: number; name: string } | null;
    data_inicio: string;
    data_fim: string;
    motivo: MotivoAfastamento;
    observacoes: string | null;
    status: StatusAfastamento;
    created_at: string;
    updated_at: string;
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
        'PENDENTE' | 'ENVIADO' | 'UTILIZADO' | 'EXPIRADO' | 'CANCELADO' | null;
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
        nome_completo?: string;
        foto_perfil?: string | null;
        url_foto?: string | null;
    };
    esta_afastado?: boolean;
    afastamento_atual?: VoluntarioAfastamento | null;
    afastamentos?: VoluntarioAfastamento[];
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

export interface Voluntario {
    id: number;
    nome_completo: string;
    nome_doutor: string | null;
    email: string;
    telefone: string | null;
    data_nascimento: string | null;
    cpf: string | null;
    cidade_base_id: number | null;
    cidade_base?: Cidade | null;
    data_entrada_ong: string | null;
    status: string;
    observacoes: string | null;
    foto_perfil: string | null;
    url_foto: string | null;
    cargos?: Cargo[];
    created_at: string;
    updated_at: string;
}

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
export type EventoStatus = 'agendado' | 'cancelado' | 'finalizado';
export type PresencaStatus = 'presente' | 'ausente';

export interface EventoParticipante {
    id: number;
    name: string;
    email: string;
    pivot?: {
        status: 'inscrito' | 'cancelado';
        inscrito_em: string | null;
        cancelado_em: string | null;
        presenca: PresencaStatus | null;
        presenca_registrada_em: string | null;
        presenca_registrada_por_id: number | null;
        observacao_presenca: string | null;
    };
}

export interface Evento {
    id: number;
    titulo: string;
    tipo: EventoTipo;
    descricao: string | null;
    local: string | null;
    cidade_id: number | null;
    cidade?: Cidade | null;
    data_inicio: string;
    data_fim: string | null;
    limite_inscricao: string | null;
    limite_participantes: number | null;
    status: EventoStatus;
    responsavel_id: number | null;
    criado_por_id: number;
    motivo_cancelamento: string | null;
    cancelado_em: string | null;
    cancelado_por_id: number | null;
    finalizado_em: string | null;
    finalizado_por_id: number | null;
    observacoes_finalizacao: string | null;
    participantes_ativos_count?: number;
    responsavel?: User | null;
    participantes_ativos?: EventoParticipante[];
    created_at: string;
    updated_at: string;
}

export interface MeuEvento {
    id: number;
    titulo: string;
    tipo: EventoTipo;
    data_inicio: string;
    data_fim: string | null;
    local: string | null;
    status: EventoStatus;
    responsavel?: User | null;
    inscricao_status: 'inscrito' | 'cancelado';
    presenca: PresencaStatus | null;
    presenca_registrada_em: string | null;
}
