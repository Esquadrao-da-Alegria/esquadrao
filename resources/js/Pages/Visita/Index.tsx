// REACT/INERTIA
import { Link, router } from '@inertiajs/react';
import { type FC, useState } from 'react';

// UI
import PainelLayout from '@/layouts/PainelLayout';

// ROTAS
import { create, index } from '@/routes/visitas';

// TIPOS
import type { Evento } from '@/types';
import type { Visita } from '@/types/visita';

// COMPONENTES
import EventoDetalhesModalShow from '@/components/Painel/Evento/Calendario/Detalhes/Modal/Show';
import DetalhesModalShow from '@/components/Painel/Visita/Calendario/Detalhes/Modal/Show';
import ListaCompletaModalShow from '@/components/Painel/Visita/Calendario/ListaCompleta/Modal/Show';
import CalendarioShow from '@/components/Painel/Visita/Calendario/Show';

// ICONS
import {
    CalendarDays,
    ChevronLeft,
    ChevronRight,
    MapPin,
    Plus,
} from 'lucide-react';

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

const Index: FC<Props> = ({
    visitas,
    eventos = [],
    mes,
    cidades = [],
    cidadeId = 'todas',
    cidadeUsuarioId = null,
    visitaId = null,
}) => {
    const [visitaSelecionada, setVisitaSelecionada] = useState<Visita | null>(
        visitas.find((visita) => visita.id === visitaId) ?? null,
    );
    const [eventoSelecionado, setEventoSelecionado] = useState<Evento | null>(
        null,
    );
    const [diaOverflow, setDiaOverflow] = useState<Date | null>(null);
    const [visitasOverflow, setVisitasOverflow] = useState<Visita[]>([]);
    const [eventosOverflow, setEventosOverflow] = useState<Evento[]>([]);

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

                    <div className="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center sm:gap-2">
                        {/* Seletor de cidade */}
                        {cidades.length > 0 && (
                            <div className="flex w-full items-center gap-2 rounded-full border border-amber-200 bg-white px-3.5 py-1.5 shadow-sm sm:w-auto">
                                <MapPin className="size-4 shrink-0 text-amber-700/70" />
                                <select
                                    value={cidadeId}
                                    onChange={(e) =>
                                        navegar(
                                            mes,
                                            e.target.value === 'todas'
                                                ? 'todas'
                                                : Number(e.target.value),
                                        )
                                    }
                                    className="w-full cursor-pointer bg-transparent pr-1 text-sm font-medium text-amber-900 focus:outline-none sm:w-auto"
                                    aria-label="Filtrar por cidade"
                                >
                                    <option value="todas">
                                        Todas as cidades
                                    </option>
                                    {cidades.map((cidade) => (
                                        <option
                                            key={cidade.id}
                                            value={cidade.id}
                                        >
                                            {cidade.nome}
                                            {cidade.id === cidadeUsuarioId
                                                ? ' (Sua cidade)'
                                                : ''}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        )}

                        {/* Seletor de mês */}
                        <div className="flex w-full items-center justify-between gap-1 rounded-full border border-amber-200 bg-white p-1 shadow-sm sm:w-auto">
                            <button
                                type="button"
                                onClick={() =>
                                    navegar(mesAnterior(mes), cidadeId)
                                }
                                className="flex size-8 items-center justify-center rounded-full text-amber-700 transition hover:bg-amber-50"
                                aria-label="Mês anterior"
                            >
                                <ChevronLeft className="size-4" />
                            </button>
                            <input
                                type="month"
                                value={mes}
                                onChange={(e) =>
                                    navegar(e.target.value, cidadeId)
                                }
                                className="rounded px-2 py-1 text-sm font-medium text-amber-900 focus:outline-none"
                            />
                            <button
                                type="button"
                                onClick={() =>
                                    navegar(mesSeguinte(mes), cidadeId)
                                }
                                className="flex size-8 items-center justify-center rounded-full text-amber-700 transition hover:bg-amber-50"
                                aria-label="Próximo mês"
                            >
                                <ChevronRight className="size-4" />
                            </button>
                        </div>

                        {/* Botão nova visita */}
                        <Link
                            href={create().url}
                            className="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-5 py-2.5 text-sm font-semibold text-amber-700 shadow-sm transition hover:bg-amber-50 focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:outline-none sm:w-auto"
                        >
                            <Plus
                                className="size-5"
                                strokeWidth={2}
                                aria-hidden
                            />
                            Nova visita
                        </Link>
                    </div>
                </header>

                {/* Calendário */}
                <CalendarioShow
                    visitas={visitas}
                    eventos={eventos}
                    mes={mes}
                    onSelecionarVisita={setVisitaSelecionada}
                    onSelecionarEvento={setEventoSelecionado}
                    onAbrirListaCompleta={abrirListaCompleta}
                />
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
