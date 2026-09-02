// REACT/INERTIA
import { Link, router, usePage } from '@inertiajs/react';
import {
    ArrowUpRight,
    FileText,
    TriangleAlert,
    UserPlus,
    Zap,
} from 'lucide-react';
import { type FC, useEffect, useState } from 'react';

import Modal from '@/components/Modal/Show';
import { Button } from '@/components/ui/button';
import { toastInfo } from '@/lib/utils/toast';
import {
    contarParticipantes,
    labelStatus,
    listarParticipantesAtivos,
    participacaoAtivaDoUsuario,
    podeCriarRelatorio,
    podeEditarVisita,
    usuarioEhLiderDaVisita,
    usuarioJaInscrito,
    visitaAtingiuLimite,
} from '@/lib/visita';
import { edit } from '@/routes/visitas';
import { create, index as relatoriosIndex } from '@/routes/visitas/relatorios';
import { Service } from '@/Services/Visita/Participante/Service';
import type { SharedData } from '@/types';
import type { TipoParticipacao, Visita } from '@/types/visita';

interface Props {
    visita: Visita | null;
    onFechar: () => void;
}
type Passo = 'detalhes' | 'inscricao';

const botaoAcaoClass =
    'inline-flex min-h-9 w-full items-center justify-center gap-1.5 rounded-lg border border-amber-600 bg-white px-2.5 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-50 disabled:opacity-50 sm:text-sm';
const botaoPerigoClass =
    'inline-flex min-h-9 w-full items-center justify-center gap-1.5 rounded-lg border border-red-600 bg-white px-2.5 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-50 disabled:opacity-50 sm:text-sm';

const Show: FC<Props> = ({ visita, onFechar }) => {
    const { auth, eh_administrador } = usePage<SharedData>().props;
    const [passo, setPasso] = useState<Passo>('detalhes');
    const [tipo, setTipo] = useState<TipoParticipacao | null>(null);
    const [enviando, setEnviando] = useState(false);
    const [cancelando, setCancelando] = useState(false);
    const [cancelandoVisita, setCancelandoVisita] = useState(false);

    const participantes = visita ? contarParticipantes(visita) : null;
    const participantesAtivos = visita ? listarParticipantesAtivos(visita) : [];
    const jaInscrito = visita ? usuarioJaInscrito(visita, auth.user.id) : false;
    const participacaoAtiva = visita
        ? participacaoAtivaDoUsuario(visita, auth.user.id)
        : null;
    const ehLider = visita
        ? usuarioEhLiderDaVisita(visita, auth.user.id)
        : false;
    const visitaAgendada = visita?.status === 'agendada';
    const dataFimPassou = visita ? new Date(visita.fim_em) < new Date() : false;
    const podeCancelarVisita = Boolean(eh_administrador || ehLider);

    useEffect(() => {
        if (visita === null) {
            setPasso('detalhes');
            setTipo(null);
            setEnviando(false);
            setCancelando(false);
            setCancelandoVisita(false);
        }
    }, [visita]);

    const fecharModal = () => {
        setPasso('detalhes');
        setTipo(null);
        setEnviando(false);
        setCancelando(false);
        setCancelandoVisita(false);
        onFechar();
    };

    const handleParticipar = () => {
        if (!visita) return;
        if (visitaAtingiuLimite(visita)) {
            toastInfo('Visita atingiu limite de participantes');
            return;
        }
        setPasso('inscricao');
    };

    const handleConfirmar = async () => {
        if (!visita || !tipo || enviando) return;
        setEnviando(true);
        const sucesso = await Service.participar(visita.id!, tipo);
        setEnviando(false);
        if (sucesso) fecharModal();
    };

    const handleCancelarInscricao = async () => {
        if (!visita || !participacaoAtiva?.id || cancelando) return;
        setCancelando(true);
        const sucesso = await Service.cancelar(
            visita.id!,
            participacaoAtiva.id,
        );
        setCancelando(false);
        if (sucesso) fecharModal();
    };

    const handleCancelarVisita = () => {
        if (!visita?.id || cancelandoVisita) return;
        if (
            !window.confirm(
                'Deseja realmente cancelar esta visita? Ela continuará disponível no histórico.',
            )
        )
            return;
        setCancelandoVisita(true);
        router.post(
            `/visitas/${visita.id}/cancelar`,
            {},
            {
                preserveScroll: true,
                onSuccess: fecharModal,
                onFinish: () => setCancelandoVisita(false),
            },
        );
    };

    const formatarData = (d: Date) =>
        d.toLocaleDateString('pt-BR', {
            weekday: 'long',
            day: '2-digit',
            month: 'long',
            year: 'numeric',
        });
    const formatarHora = (d: Date) =>
        d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });

    return (
        <Modal
            isOpen={visita !== null}
            onClose={fecharModal}
            className="max-w-md"
        >
            {visita && passo === 'detalhes' && (
                <div className="p-4 sm:p-6">
                    <div className="mb-4 flex items-start justify-between gap-4">
                        <h2 className="text-lg font-semibold text-gray-900">
                            Detalhes da visita
                        </h2>
                        <button
                            type="button"
                            onClick={fecharModal}
                            className="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700"
                            aria-label="Fechar"
                        >
                            ✕
                        </button>
                    </div>
                    <dl className="space-y-3 text-sm">
                        <div>
                            <dt className="text-xs font-medium text-gray-400 uppercase">
                                {visita.tipo === 'acao_especial'
                                    ? 'Tipo / Local'
                                    : 'Hospital'}
                            </dt>
                            <dd className="mt-0.5 font-medium text-gray-900">
                                {visita.tipo === 'acao_especial'
                                    ? visita.hospital?.nome
                                        ? `Ação Especial — ${visita.hospital.nome}`
                                        : 'Ação Especial'
                                    : (visita.hospital?.nome ?? '—')}
                            </dd>
                        </div>
                        {visita.alaUnidade && (
                            <div>
                                <dt className="text-xs font-medium text-gray-400 uppercase">
                                    Ala / Unidade
                                </dt>
                                <dd className="mt-0.5 text-gray-700">
                                    {visita.alaUnidade.nome}
                                </dd>
                            </div>
                        )}
                        <div>
                            <dt className="text-xs font-medium text-gray-400 uppercase">
                                Data
                            </dt>
                            <dd className="mt-0.5 text-gray-700 capitalize">
                                {formatarData(new Date(visita.inicio_em))}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-xs font-medium text-gray-400 uppercase">
                                Horário
                            </dt>
                            <dd className="mt-0.5 text-gray-700">
                                {formatarHora(new Date(visita.inicio_em))} –{' '}
                                {formatarHora(new Date(visita.fim_em))}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-xs font-medium text-gray-400 uppercase">
                                Status
                            </dt>
                            <dd className="mt-0.5">
                                <span className="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                    {labelStatus(visita.status)}
                                </span>
                            </dd>
                        </div>
                        {visita.lider && (
                            <div>
                                <dt className="text-xs font-medium text-gray-400 uppercase">
                                    Líder
                                </dt>
                                <dd className="mt-0.5 text-gray-700">
                                    {visita.lider.name}
                                </dd>
                            </div>
                        )}
                        <div>
                            <dt className="text-xs font-medium text-gray-400 uppercase">
                                Participantes
                            </dt>
                            <dd className="mt-0.5 text-gray-700">
                                {participantesAtivos.length > 0 ? (
                                    <ul className="space-y-1">
                                        {participantesAtivos.map((p) => (
                                            <li key={p.id ?? p.voluntario_id}>
                                                {p.tipo_participacao ===
                                                'palhaco'
                                                    ? '🎪'
                                                    : '👔'}{' '}
                                                {p.voluntario?.name ?? '—'}
                                            </li>
                                        ))}
                                    </ul>
                                ) : (
                                    '—'
                                )}
                                {participantes &&
                                    participantesAtivos.length > 0 && (
                                        <span className="mt-1 block text-xs text-gray-400">
                                            {participantes.palhaco} palhaço
                                            {participantes.palhaco !== 1
                                                ? 's'
                                                : ''}{' '}
                                            · {participantes.paisana} paisana
                                            {participantes.paisana !== 1
                                                ? 's'
                                                : ''}
                                        </span>
                                    )}
                            </dd>
                        </div>
                        {visita.observacoes && (
                            <div>
                                <dt className="text-xs font-medium text-gray-400 uppercase">
                                    Observações
                                </dt>
                                <dd className="mt-0.5 text-gray-700">
                                    {visita.observacoes}
                                </dd>
                            </div>
                        )}
                    </dl>

                    <hr className="my-3 border-gray-200" />

                    <div>
                        <h3 className="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900">
                            <FileText className="size-4" aria-hidden />
                            Relatórios
                        </h3>
                        <div className="grid grid-cols-2 gap-2">
                            <Link
                                href={relatoriosIndex.url({
                                    visita: visita.id!,
                                })}
                                onClick={fecharModal}
                                className={botaoAcaoClass}
                            >
                                Ver relatórios
                            </Link>
                            {podeCriarRelatorio(auth.user, visita) && (
                                <Link
                                    href={create.url({ visita: visita.id! })}
                                    onClick={fecharModal}
                                    className={botaoAcaoClass}
                                >
                                    Criar relatório
                                </Link>
                            )}
                        </div>
                    </div>

                    <hr className="my-3 border-gray-200" />

                    <div>
                        <h3 className="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900">
                            <Zap className="size-4" aria-hidden />
                            Ações
                        </h3>
                        <div className="grid grid-cols-2 gap-2">
                            {podeEditarVisita(auth.user, visita) && (
                                <Link
                                    href={edit({ visita: visita.id! }).url}
                                    onClick={fecharModal}
                                    className={botaoAcaoClass}
                                >
                                    Visualizar detalhes
                                    <ArrowUpRight
                                        className="size-4"
                                        aria-hidden
                                    />
                                </Link>
                            )}
                            {visitaAgendada &&
                                !ehLider &&
                                (jaInscrito ? (
                                    <button
                                        type="button"
                                        onClick={handleCancelarInscricao}
                                        disabled={cancelando}
                                        className={botaoAcaoClass}
                                    >
                                        {cancelando
                                            ? 'Cancelando...'
                                            : 'Cancelar inscrição'}
                                    </button>
                                ) : (
                                    !dataFimPassou && (
                                        <button
                                            type="button"
                                            onClick={handleParticipar}
                                            className={botaoAcaoClass}
                                        >
                                            Participar
                                            <UserPlus
                                                className="size-4"
                                                aria-hidden
                                            />
                                        </button>
                                    )
                                ))}
                            {podeCancelarVisita && visitaAgendada && (
                                <button
                                    type="button"
                                    onClick={handleCancelarVisita}
                                    disabled={cancelandoVisita}
                                    className={botaoPerigoClass}
                                >
                                    {cancelandoVisita
                                        ? 'Cancelando visita...'
                                        : 'Cancelar visita'}
                                    <TriangleAlert
                                        className="size-4"
                                        aria-hidden
                                    />
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            )}

            {visita && passo === 'inscricao' && (
                <div className="p-4 sm:p-6">
                    <div className="mb-4 flex items-start justify-between gap-4">
                        <h2 className="text-lg font-semibold text-gray-900">
                            Escolha o tipo de participação
                        </h2>
                        <button
                            type="button"
                            onClick={fecharModal}
                            className="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700"
                            aria-label="Fechar"
                        >
                            ✕
                        </button>
                    </div>
                    <div className="space-y-2">
                        <button
                            type="button"
                            onClick={() => setTipo('palhaco')}
                            className={`w-full rounded-lg border p-3 text-left transition-colors ${tipo === 'palhaco' ? 'border-amber-500 bg-amber-50' : 'border-gray-200 hover:border-gray-300'}`}
                        >
                            <span className="mr-2">🎪</span>Palhaço
                        </button>
                        <button
                            type="button"
                            onClick={() => setTipo('paisana')}
                            className={`w-full rounded-lg border p-3 text-left transition-colors ${tipo === 'paisana' ? 'border-amber-500 bg-amber-50' : 'border-gray-200 hover:border-gray-300'}`}
                        >
                            <span className="mr-2">👔</span>Paisana
                        </button>
                    </div>
                    <div className="mt-5 flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            className="flex-1"
                            onClick={() => setPasso('detalhes')}
                            disabled={enviando}
                        >
                            Voltar
                        </Button>
                        <Button
                            size="sm"
                            className="flex-1"
                            onClick={handleConfirmar}
                            disabled={!tipo || enviando}
                        >
                            {enviando ? 'Enviando...' : 'Confirmar'}
                        </Button>
                    </div>
                </div>
            )}
        </Modal>
    );
};

export default Show;
