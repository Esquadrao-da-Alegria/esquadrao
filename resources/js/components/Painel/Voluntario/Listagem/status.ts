import { User } from '@/types';
import { StatusKey, StatusOption, VoluntarioListagem } from './types';

export const statusOptions: StatusOption[] = [
    {
        key: 'pendente',
        label: 'Pendente',
        className: 'bg-gray-100 text-gray-700',
    },
    {
        key: 'aceito',
        label: 'Aceito',
        className: 'bg-emerald-50 text-emerald-700',
    },
    {
        key: 'expirado',
        label: 'Expirado',
        className: 'bg-red-50 text-red-700',
    },
    {
        key: 'cancelado',
        label: 'Cancelado',
        className: 'bg-gray-100 text-gray-500',
    },
];

export const statusMap = new Map(
    statusOptions.map((status) => [status.key, status]),
);

export const getStatusKey = (voluntario: User): StatusKey => {
    if (
        voluntario.convite_status === 'UTILIZADO' ||
        voluntario.convite_utilizado_em
    ) {
        return 'aceito';
    }

    if (voluntario.convite_status === 'CANCELADO') {
        return 'cancelado';
    }

    const conviteExpirado =
        ['PENDENTE', 'ENVIADO', undefined, null].includes(
            voluntario.convite_status,
        ) &&
        voluntario.convite_expira_em &&
        new Date(voluntario.convite_expira_em).getTime() < Date.now();

    if (voluntario.convite_status === 'EXPIRADO' || conviteExpirado) {
        return 'expirado';
    }

    return 'pendente';
};

export const mapVoluntariosStatus = (
    voluntarios: User[] | null | undefined,
): VoluntarioListagem[] =>
    (Array.isArray(voluntarios) ? voluntarios : []).map((voluntario) => ({
        ...voluntario,
        statusKey: getStatusKey(voluntario),
    }));

export const podeReenviarConvite = (statusKey: StatusKey) => {
    return ['pendente', 'expirado'].includes(statusKey);
};

export const podeCancelarConvite = (statusKey: StatusKey) =>
    statusKey === 'pendente';

export const formatarData = (data?: string) => {
    if (!data) {
        return '-';
    }

    return new Intl.DateTimeFormat('pt-BR').format(new Date(data));
};

export const getIniciais = (nome: string) => {
    const partes = nome.trim().split(/\s+/).slice(0, 2);

    return partes.map((parte) => parte[0]?.toUpperCase()).join('');
};

export const getEquipe = (voluntario: User) => {
    const cargos = (voluntario.cargos ?? [])
        .filter(
            (cargo) => !['voluntario', 'administrador'].includes(cargo.slug),
        )
        .map((cargo) => cargo.nome);

    return cargos.length > 0 ? cargos.join(', ') : '-';
};

export const getCidade = (voluntario: User) => {
    const cidadeBase = voluntario.cidade_base as
        { nome?: string | null } | null | undefined;

    return cidadeBase?.nome ?? '-';
};

export const getStatusVoluntario = (voluntario: User) => {
    if (voluntario.status === 'inativo') {
        return {
            label: 'Inativo',
            className: 'bg-gray-100 text-gray-400',
        };
    }

    return {
        label: voluntario.email_verified_at ? 'Cadastro Completo' : 'Ativo',
        className: voluntario.email_verified_at
            ? 'bg-orange-50 text-orange-700'
            : 'bg-emerald-50 text-emerald-700',
    };
};

export const getMotivoLabel = (motivo?: string | null): string => {
    switch (motivo) {
        case 'atestado_medico':
            return 'Atestado Médico';
        case 'licenca_pessoal':
            return 'Licença Pessoal';
        case 'estudos':
            return 'Estudos';
        case 'outro':
            return 'Outro';
        default:
            return 'Atestado / Licença';
    }
};

export const getAfastamentoBadge = (voluntario: User) => {
    if (!voluntario.esta_afastado && !voluntario.afastamento_atual) {
        return null;
    }

    const dataFim = voluntario.afastamento_atual?.data_fim;
    const dataFormatada = formatarData(dataFim);

    return {
        label: `Afastado (Atestado até ${dataFormatada})`,
        className: 'bg-rose-50 text-rose-700 border-rose-200',
    };
};
