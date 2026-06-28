import Abas from '@/components/Painel/Voluntario/Listagem/Abas';
import ConviteModal from '@/components/Painel/Voluntario/Listagem/ConviteModal';
import Filtros from '@/components/Painel/Voluntario/Listagem/Filtros';
import Header from '@/components/Painel/Voluntario/Listagem/Header';
import Paginacao from '@/components/Painel/Voluntario/Listagem/Paginacao';
import { mapVoluntariosStatus } from '@/components/Painel/Voluntario/Listagem/status';
import StatusCards from '@/components/Painel/Voluntario/Listagem/StatusCards';
import Tabela from '@/components/Painel/Voluntario/Listagem/Tabela';
import {
    AbaKey,
    PaginatedVoluntarios,
    StatusCounters,
    StatusFiltro,
    VoluntarioFiltros,
    VoluntarioListagem,
} from '@/components/Painel/Voluntario/Listagem/types';
import PainelLayout from '@/layouts/PainelLayout';
import { destroy, index } from '@/routes/voluntarios';
import { SharedData } from '@/types';
import { router, useForm, usePage } from '@inertiajs/react';
import React, { useEffect, useMemo, useState } from 'react';

interface Props {
    voluntarios: PaginatedVoluntarios;
    contadores: StatusCounters;
    filtros: VoluntarioFiltros;
}

const Index: React.FC<Props> = ({ voluntarios, contadores, filtros }) => {
    const { props } = usePage<SharedData>();
    const ehAdministrador = props.eh_administrador === true;
    const [modalAberto, setModalAberto] = useState(false);
    const [busca, setBusca] = useState(filtros.busca ?? '');
    const aba = filtros.aba ?? 'voluntarios';
    const statusFiltro = filtros.status ?? 'todos';

    const conviteForm = useForm({
        name: '',
        email: '',
    });

    const listaVoluntarios = useMemo(
        () =>
            Array.isArray(voluntarios?.data) ? voluntarios.data : [],
        [voluntarios],
    );

    const voluntariosComStatus = useMemo(
        () => mapVoluntariosStatus(listaVoluntarios),
        [listaVoluntarios],
    );

    useEffect(() => {
        setBusca(filtros.busca ?? '');
    }, [filtros.busca]);

    useEffect(() => {
        const buscaAtual = filtros.busca ?? '';

        if (busca === buscaAtual) {
            return;
        }

        const timeout = window.setTimeout(() => {
            atualizarFiltros({ busca, status: statusFiltro });
        }, 350);

        return () => window.clearTimeout(timeout);
    }, [busca]);

    const atualizarFiltros = ({
        busca,
        status,
        aba,
    }: {
        busca?: string;
        status?: StatusFiltro;
        aba?: AbaKey;
    }) => {
        const abaAtual = aba ?? filtros.aba ?? 'voluntarios';

        router.get(
            index.url(),
            {
                aba: abaAtual === 'convidados' ? abaAtual : undefined,
                busca: busca || undefined,
                status:
                    abaAtual === 'convidados' && status && status !== 'todos'
                        ? status
                        : undefined,
            },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            },
        );
    };

    const handleStatusChange = (status: StatusFiltro) => {
        atualizarFiltros({ busca, status, aba });
    };

    const handleAbaChange = (aba: AbaKey) => {
        atualizarFiltros({ busca, status: 'todos', aba });
    };

    const handleAbrirModal = () => {
        conviteForm.reset();
        conviteForm.clearErrors();
        setModalAberto(true);
    };

    const handleEnviarConvite = () => {
        conviteForm.post('/voluntarios/convite', {
            preserveScroll: true,
            onSuccess: () => {
                setModalAberto(false);
                conviteForm.reset();
            },
        });
    };

    const handleInativar = (voluntario: VoluntarioListagem) => {
        if (!window.confirm(`Inativar ${voluntario.name}?`)) {
            return;
        }

        router.delete(destroy.url(voluntario.id), {
            preserveScroll: true,
        });
    };

    const handleReenviarConvite = (voluntario: VoluntarioListagem) => {
        router.post(
            `/voluntarios/${voluntario.id}/reenviar-convite`,
            {},
            {
                preserveScroll: true,
            },
        );
    };

    const handleCancelarConvite = (voluntario: VoluntarioListagem) => {
        if (!window.confirm(`Excluir convite de ${voluntario.name}?`)) {
            return;
        }

        router.delete(`/voluntarios/${voluntario.id}/convite`, {
            preserveScroll: true,
        });
    };

    return (
        <PainelLayout>
            <div className="mx-auto max-w-7xl px-5 py-8 sm:px-6 lg:px-8">
                <Header
                    ehAdministrador={ehAdministrador}
                    onCadastrar={handleAbrirModal}
                />
                {props.link_convite ? (
                    <div className="mt-5 mb-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                        <p className="font-medium">
                            Link do convite gerado para ambiente local
                        </p>
                        <a
                            href={props.link_convite}
                            className="mt-1 block break-all text-amber-800 underline underline-offset-2"
                        >
                            {props.link_convite}
                        </a>
                    </div>
                ) : null}
                <Abas
                    aba={aba}
                    contadores={contadores}
                    onAbaChange={handleAbaChange}
                />
                {aba === 'convidados' ? (
                    <StatusCards
                        contadores={contadores}
                        statusFiltro={statusFiltro}
                        onStatusChange={handleStatusChange}
                    />
                ) : null}
                <Filtros
                    aba={aba}
                    busca={busca}
                    statusFiltro={statusFiltro}
                    onBuscaChange={setBusca}
                    onStatusChange={handleStatusChange}
                />
                <Tabela
                    aba={aba}
                    voluntarios={voluntariosComStatus}
                    ehAdministrador={ehAdministrador}
                    onInativar={handleInativar}
                    onReenviarConvite={handleReenviarConvite}
                    onCancelarConvite={handleCancelarConvite}
                />
                <Paginacao paginacao={voluntarios} />
            </div>

            <ConviteModal
                aberto={modalAberto}
                form={conviteForm}
                onOpenChange={setModalAberto}
                onSubmit={handleEnviarConvite}
            />
        </PainelLayout>
    );
};

export default Index;
