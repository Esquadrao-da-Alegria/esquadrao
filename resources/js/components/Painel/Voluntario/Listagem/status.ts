import { User } from '@/types';
import { StatusKey, StatusOption, VoluntarioListagem } from './types';

export const statusOptions: StatusOption[] = [
    {
        key: 'pendente',
        label: 'Pendente',
        className: 'bg-gray-100 text-gray-700',
    },
    {
        key: 'convite_enviado',
        label: 'Convite Enviado',
        className: 'bg-indigo-50 text-indigo-700',
    },
    {
        key: 'convite_expirado',
        label: 'Convite Expirado',
        className: 'bg-red-50 text-red-700',
    },
];

export const statusMap = new Map(
    statusOptions.map((status) => [status.key, status]),
);

export const getStatusKey = (voluntario: User): StatusKey => {
    const conviteExpirado =
        voluntario.status === 'convite_enviado' &&
        voluntario.convite_expira_em &&
        new Date(voluntario.convite_expira_em).getTime() < Date.now();

    if (voluntario.status === 'inativo') {
        return 'pendente';
    }

    if (conviteExpirado) {
        return 'convite_expirado';
    }

    if (voluntario.status === 'convite_enviado') {
        return 'convite_enviado';
    }

    if (voluntario.email_verified_at) {
        return 'pendente';
    }

    if (voluntario.status === 'ativo') {
        return 'pendente';
    }

    return 'pendente';
};

export const mapVoluntariosStatus = (
    voluntarios: User[],
): VoluntarioListagem[] =>
    voluntarios.map((voluntario) => ({
        ...voluntario,
        statusKey: getStatusKey(voluntario),
    }));

export const podeReenviarConvite = (statusKey: StatusKey) => {
    return ['pendente', 'convite_enviado', 'convite_expirado'].includes(
        statusKey,
    );
};

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
        | { nome?: string | null }
        | null
        | undefined;

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
