// REACT/INERTIA
import { Link, router } from '@inertiajs/react';
import { type FC, useState } from 'react';

// UI
import PainelLayout from '@/layouts/PainelLayout';

// ROTAS
import { create, index } from '@/routes/visitas';

// TIPOS
import type { Visita } from '@/types';

// COMPONENTES
import DetalhesModalShow from '@/components/Painel/Visita/Calendario/Detalhes/Modal/Show';
import ListaCompletaModalShow from '@/components/Painel/Visita/Calendario/ListaCompleta/Modal/Show';
import CalendarioShow from '@/components/Painel/Visita/Calendario/Show';

// ICONS
import { CalendarDays, ChevronLeft, ChevronRight, Plus } from 'lucide-react';

interface Props {
    visitas: Visita[];
    mes: string; // YYYY-MM
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

const Index: FC<Props> = ({ visitas, mes }) => {
    const [visitaSelecionada, setVisitaSelecionada] = useState<Visita | null>(
        null,
    );
    const [diaOverflow, setDiaOverflow] = useState<Date | null>(null);
    const [visitasOverflow, setVisitasOverflow] = useState<Visita[]>([]);

    const navegarMes = (novoMes: string) => {
        router.visit(index({ query: { mes: novoMes } }).url, {
            preserveScroll: true,
        });
    };

    const abrirListaCompleta = (dia: Date, visitasDoDia: Visita[]) => {
        setDiaOverflow(dia);
        setVisitasOverflow(visitasDoDia);
    };

    const fecharListaCompleta = () => {
        setDiaOverflow(null);
        setVisitasOverflow([]);
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
                                {visitas.length === 0
                                    ? 'Nenhuma visita neste mês'
                                    : `${visitas.length} ${visitas.length === 1 ? 'visita' : 'visitas'}`}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        {/* Seletor de mês */}
                        <div className="flex items-center gap-1 rounded-full border border-amber-200 bg-white p-1">
                            <button
                                type="button"
                                onClick={() => navegarMes(mesAnterior(mes))}
                                className="flex size-8 items-center justify-center rounded-full text-amber-700 transition hover:bg-amber-50"
                                aria-label="Mês anterior"
                            >
                                <ChevronLeft className="size-4" />
                            </button>
                            <input
                                type="month"
                                value={mes}
                                onChange={(e) => navegarMes(e.target.value)}
                                className="rounded px-2 py-1 text-sm font-medium text-amber-900 focus:outline-none"
                            />
                            <button
                                type="button"
                                onClick={() => navegarMes(mesSeguinte(mes))}
                                className="flex size-8 items-center justify-center rounded-full text-amber-700 transition hover:bg-amber-50"
                                aria-label="Próximo mês"
                            >
                                <ChevronRight className="size-4" />
                            </button>
                        </div>

                        {/* Botão nova visita */}
                        <Link
                            href={create().url}
                            className="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 text-sm font-semibold text-amber-700 shadow-sm transition hover:bg-amber-50 focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:outline-none sm:w-auto"
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
                    mes={mes}
                    onSelecionarVisita={setVisitaSelecionada}
                    onAbrirListaCompleta={abrirListaCompleta}
                />
            </div>

            {/* Modal detalhes */}
            <DetalhesModalShow
                visita={visitaSelecionada}
                onFechar={fecharDetalhes}
            />

            {/* Modal lista completa */}
            <ListaCompletaModalShow
                dia={diaOverflow}
                visitas={visitasOverflow}
                onFechar={fecharListaCompleta}
                onSelecionarVisita={(v) => {
                    fecharListaCompleta();
                    setVisitaSelecionada(v);
                }}
            />
        </PainelLayout>
    );
};

export default Index;
