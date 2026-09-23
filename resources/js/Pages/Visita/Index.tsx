// REACT/INERTIA
import { Link, router, usePage } from '@inertiajs/react';
import { type FC, useEffect, useState } from 'react';

// UI
import PainelLayout from '@/layouts/PainelLayout';
import { toastConfirmacao } from '@/lib/utils/toast';

// ROTAS
import { create, index } from '@/routes/visitas';

// TIPOS
import type { Evento, SharedData } from '@/types';
import type { Visita } from '@/types/visita';

// COMPONENTES
import EventoDetalhesModalShow from '@/components/Painel/Evento/Calendario/Detalhes/Modal/Show';
import DetalhesModalShow from '@/components/Painel/Visita/Calendario/Detalhes/Modal/Show';
import ListaCompletaModalShow from '@/components/Painel/Visita/Calendario/ListaCompleta/Modal/Show';
import LegendaCalendarioShow from '@/components/Painel/Visita/Calendario/Legenda/Show';
import MetasCalendarioShow, {
    type AcompanhamentoMeta,
} from '@/components/Painel/Visita/Calendario/Metas/Show';
import CalendarioShow from '@/components/Painel/Visita/Calendario/Show';

// ICONS
import { CalendarDays, ChevronDown, ChevronLeft, ChevronRight, Download, Lock, MapPin, Plus, Unlock } from 'lucide-react';

interface CidadeOption {
    id: number;
    nome: string;
}

interface Props {
    visitas: Visita[];
    eventos?: Evento[];
    mes: string; // YYYY-MM
    cidades?: CidadeOption[];
    cidadeId?: number | 'todas';
    cidadeUsuarioId?: number | null;
    visitaId?: number | null;
    ehGestor?: boolean;
    agendaLiberacao?: { liberado: boolean; editavel: boolean } | null;
    podeGerenciarAgenda?: boolean;
    acompanhamentoMetas?: AcompanhamentoMeta[];
}

function nomeMes(mes: string): string {
    const [ano, mesNum] = mes.split('-').map(Number);
    const data = new Date(ano, mesNum - 1, 1);
    return data.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
}

function mesAnterior(mes: string): string {
    const [ano, mesNum] = mes.split('-').map(Number);
    const d = new Date(ano, mesNum - 2, 1);
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
}

function mesSeguinte(mes: string): string {
    const [ano, mesNum] = mes.split('-').map(Number);
    const d = new Date(ano, mesNum, 1);
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
}

const controleToolbarClass =
    'flex h-10 items-center rounded-full border border-amber-200 bg-white shadow-sm';

const Index: FC<Props> = ({
    visitas,
    eventos = [],
    mes,
    cidades = [],
    cidadeId = 'todas',
    cidadeUsuarioId = null,
    visitaId = null,
    ehGestor = false,
    agendaLiberacao = null,
    podeGerenciarAgenda = false,
    acompanhamentoMetas = [],
}) => {
    const { auth } = usePage<SharedData>().props;
    const cidadeBaseUsuario =
        cidadeUsuarioId ?? auth?.user?.voluntario?.cidade_base_id ?? null;

    const [visitaSelecionada, setVisitaSelecionada] = useState<Visita | null>(
        visitas.find((visita) => visita.id === visitaId) ?? null,
    );
    const [eventoSelecionado, setEventoSelecionado] = useState<Evento | null>(
        null,
    );
    const [diaOverflow, setDiaOverflow] = useState<Date | null>(null);
    const [visitasOverflow, setVisitasOverflow] = useState<Visita[]>([]);
    const [eventosOverflow, setEventosOverflow] = useState<Evento[]>([]);
    const [mostrarExport, setMostrarExport] = useState(false);
    const [alterandoAgenda, setAlterandoAgenda] = useState(false);
    const [agendaLiberada, setAgendaLiberada] = useState(
        agendaLiberacao?.liberado ?? false,
    );

    useEffect(() => {
        setAgendaLiberada(agendaLiberacao?.liberado ?? false);
    }, [mes, cidadeId, agendaLiberacao?.liberado]);

    const navegar = (novoMes: string, novaCidade: number | 'todas') => {
        const query: Record<string, string | number> = { mes: novoMes };
        if (novaCidade && novaCidade !== 'todas') {
            query.cidade_id = novaCidade;
        } else if (novaCidade === 'todas') {
            query.cidade_id = 'todas';
        }

        router.visit(index({ query }).url, {
            preserveScroll: true,
        });
    };

    const abrirListaCompleta = (
        dia: Date,
        visitasDoDia: Visita[],
        eventosDoDia: Evento[] = [],
    ) => {
        setDiaOverflow(dia);
        setVisitasOverflow(visitasDoDia);
        setEventosOverflow(eventosDoDia);
    };

    const fecharListaCompleta = () => {
        setDiaOverflow(null);
        setVisitasOverflow([]);
        setEventosOverflow([]);
    };

    const fecharDetalhes = () => setVisitaSelecionada(null);

    const alterarAgenda = async () => {
        if (cidadeId === 'todas' || !agendaLiberacao || alterandoAgenda) return;

        const liberar = !agendaLiberada;
        const cidade = cidades.find((item) => item.id === Number(cidadeId));
        const confirmado = await toastConfirmacao(
            liberar
                ? `Liberar o agendamento de visitas hospitalares em ${cidade?.nome ?? 'esta cidade'} para ${nomeMes(mes)}?`
                : `Bloquear novos agendamentos de visitas hospitalares em ${cidade?.nome ?? 'esta cidade'} para ${nomeMes(mes)}? Visitas já cadastradas serão preservadas.`,
        );

        if (!confirmado) return;

        const [ano, numeroMes] = mes.split('-').map(Number);
        setAlterandoAgenda(true);
        router.put('/visitas/agenda-liberacao', {
            cidade_id: Number(cidadeId),
            ano,
            mes: numeroMes,
            liberado: liberar,
        }, {
            preserveScroll: true,
            onSuccess: () => setAgendaLiberada(liberar),
            onFinish: () => setAlterandoAgenda(false),
        });
    };

    return (
        <PainelLayout>
            <div className="mx-auto max-w-7xl px-5 py-8 sm:px-6 lg:px-8">
                {/* Header */}
                <header className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-center gap-3">
                        <CalendarDays className="size-7 text-amber-700/60" />
                        <div>
                            <h1 className="text-2xl font-semibold tracking-tight text-amber-950 capitalize sm:text-3xl">
                                {nomeMes(mes)}
                            </h1>
                            <p className="mt-0.5 text-sm text-amber-900/50">
                                {visitas.length === 0 && eventos.length === 0
                                    ? 'Nenhuma atividade neste mês'
                                    : `${visitas.length} ${visitas.length === 1 ? 'visita' : 'visitas'} · ${eventos.length} ${eventos.length === 1 ? 'evento' : 'eventos'}`}
                            </p>
                        </div>
                    </div>

                    <div className="flex flex-col gap-3 w-full sm:w-auto sm:flex-row sm:items-center sm:gap-2">
                        {/* Seletor de cidade */}
                        {cidades.length > 0 && (
                            <div className={`${controleToolbarClass} w-full gap-2 px-3 sm:w-auto`}>
                                <MapPin className="size-4 shrink-0 text-amber-700/70" />
                                <select
                                    value={cidadeId === 'todas' ? 'todas' : String(cidadeId)}
                                    onChange={(e) =>
                                        navegar(
                                            mes,
                                            e.target.value === 'todas'
                                                ? 'todas'
                                                : Number(e.target.value),
                                        )
                                    }
                                    className="w-full bg-transparent text-sm font-medium text-amber-900 focus:outline-none cursor-pointer pr-1 sm:w-auto"
                                    aria-label="Filtrar por cidade"
                                >
                                    <option value="todas">Todas as cidades</option>
                                    {cidades.map((cidade) => (
                                        <option key={cidade.id} value={cidade.id}>
                                            {cidade.nome}
                                            {cidade.id === cidadeBaseUsuario
                                                ? ' (Sua cidade)'
                                                : ''}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        )}

                        {/* Seletor de mês */}
                        <div className={`${controleToolbarClass} w-full gap-0.5 px-1 sm:w-auto`}>
                            <button
                                type="button"
                                onClick={() => navegar(mesAnterior(mes), cidadeId)}
                                className="flex size-8 shrink-0 items-center justify-center rounded-full text-amber-700 transition hover:bg-amber-50"
                                aria-label="Mês anterior"
                            >
                                <ChevronLeft className="size-4" />
                            </button>
                            <input
                                type="month"
                                value={mes}
                                onChange={(e) => navegar(e.target.value, cidadeId)}
                                className="bg-transparent px-1.5 text-sm font-medium text-amber-900 focus:outline-none"
                            />
                            <button
                                type="button"
                                onClick={() => navegar(mesSeguinte(mes), cidadeId)}
                                className="flex size-8 shrink-0 items-center justify-center rounded-full text-amber-700 transition hover:bg-amber-50"
                                aria-label="Próximo mês"
                            >
                                <ChevronRight className="size-4" />
                            </button>
                        </div>

                        {/* Exportar para calendário */}
                        <div className="relative">
                            <button
                                type="button"
                                onClick={() => setMostrarExport(!mostrarExport)}
                                className="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-full border border-amber-300 bg-white px-4 py-2.5 text-sm font-medium text-amber-700 shadow-sm transition hover:bg-amber-50 focus:outline-none sm:w-auto"
                            >
                                <Download className="size-4" aria-hidden />
                                Exportar
                                <ChevronDown className="size-3.5" aria-hidden />
                            </button>
                            {mostrarExport && (
                                <div className="absolute right-0 z-10 mt-2 w-52 rounded-xl border border-amber-100 bg-white shadow-lg">
                                    <a
                                        href="/calendario/exportar/visitas?tipo=minhas"
                                        className="flex rounded-t-xl px-4 py-2.5 text-sm text-amber-900 hover:bg-amber-50"
                                        onClick={() => setMostrarExport(false)}
                                    >
                                        Minhas visitas
                                    </a>
                                    <a
                                        href="/calendario/exportar/visitas?tipo=cidade"
                                        className="flex rounded-b-xl px-4 py-2.5 text-sm text-amber-900 hover:bg-amber-50"
                                        onClick={() => setMostrarExport(false)}
                                    >
                                        Todas da minha cidade
                                    </a>
                                </div>
                            )}
                        </div>

                        {/* Botão nova visita */}
                        {cidadeId !== 'todas' && agendaLiberada ? (
                            <Link
                                href={create({ query: { mes, cidade_id: cidadeId } }).url}
                                className={`${controleToolbarClass} w-full shrink-0 justify-center gap-2 border-amber-600 px-4 text-sm font-semibold text-amber-700 transition hover:bg-amber-50 focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:outline-none sm:w-auto`}
                            >
                                <Plus className="size-4" strokeWidth={2} aria-hidden />
                                Nova visita
                            </Link>
                        ) : (
                            <button type="button" disabled title={cidadeId === 'todas' ? 'Selecione uma cidade para cadastrar uma visita.' : 'O agendamento está bloqueado para este mês.'} className={`${controleToolbarClass} w-full shrink-0 cursor-not-allowed justify-center gap-2 border-gray-200 bg-gray-100 px-4 text-sm font-semibold text-gray-400 shadow-none sm:w-auto`}>
                                <Lock className="size-4" aria-hidden />
                                Nova visita bloqueada
                            </button>
                        )}
                    </div>
                </header>

                {ehGestor && (
                    <section className="mb-6 flex flex-col gap-4 rounded-2xl border border-amber-100 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-start gap-3">
                            <span className={`mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-full ${agendaLiberada ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}`}>
                                {agendaLiberada ? <Unlock className="size-4" aria-hidden /> : <Lock className="size-4" aria-hidden />}
                            </span>
                            <div>
                                <h2 className="text-sm font-semibold text-amber-950">
                                    {cidadeId === 'todas'
                                        ? 'Selecione uma cidade para controlar o agendamento'
                                        : agendaLiberacao
                                            ? `Agendamento ${agendaLiberada ? 'liberado' : 'bloqueado'}`
                                            : 'Agendamento sem permissão de alteração'}
                                </h2>
                                <p className="mt-1 text-xs leading-relaxed text-amber-900/55">
                                    O controle vale para novas visitas hospitalares em {nomeMes(mes)}. Visitas já cadastradas não são canceladas pelo bloqueio.
                                </p>
                            </div>
                        </div>
                        {cidadeId !== 'todas' && podeGerenciarAgenda && agendaLiberacao?.editavel && (
                            <button type="button" onClick={alterarAgenda} disabled={alterandoAgenda} className={`inline-flex min-h-11 w-full shrink-0 items-center justify-center gap-2 rounded-full border-2 px-4 py-2.5 text-sm font-semibold transition disabled:cursor-wait disabled:opacity-60 sm:w-auto ${agendaLiberada ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700'}`}>
                                {agendaLiberada ? <Lock className="size-4" aria-hidden /> : <Unlock className="size-4" aria-hidden />}
                                {alterandoAgenda
                                    ? agendaLiberada ? 'Bloqueando...' : 'Liberando...'
                                    : agendaLiberada ? 'Bloquear agendamento' : 'Liberar agendamento'}
                            </button>
                        )}
                        {cidadeId !== 'todas' && agendaLiberacao && !agendaLiberacao.editavel && <span className="text-xs font-medium text-amber-900/50">Mês passado — somente leitura</span>}
                    </section>
                )}

                {/* Calendário */}
                <CalendarioShow
                    visitas={visitas}
                    eventos={eventos}
                    mes={mes}
                    onSelecionarVisita={setVisitaSelecionada}
                    onSelecionarEvento={setEventoSelecionado}
                    onAbrirListaCompleta={abrirListaCompleta}
                />
                <LegendaCalendarioShow />
                <MetasCalendarioShow metas={acompanhamentoMetas} />
            </div>

            {/* Modal detalhes visita */}
            <DetalhesModalShow
                visita={visitaSelecionada}
                onFechar={fecharDetalhes}
            />

            {/* Modal detalhes evento */}
            <EventoDetalhesModalShow
                evento={eventoSelecionado}
                onFechar={() => setEventoSelecionado(null)}
            />

            {/* Modal lista completa */}
            <ListaCompletaModalShow
                dia={diaOverflow}
                visitas={visitasOverflow}
                eventos={eventosOverflow}
                onFechar={fecharListaCompleta}
                onSelecionarVisita={(v) => {
                    fecharListaCompleta();
                    setVisitaSelecionada(v);
                }}
                onSelecionarEvento={(e) => {
                    fecharListaCompleta();
                    setEventoSelecionado(e);
                }}
            />
        </PainelLayout>
    );
};

export default Index;
