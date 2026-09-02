import EventoCardShow from '@/components/Painel/Visita/Card/EventoCardShow';
import CardShow from '@/components/Painel/Visita/Card/Show';
import { classeIndicadorPorOcupacao } from '@/lib/visita';
import type { Evento } from '@/types';
import type { Visita } from '@/types/visita';
import { type FC } from 'react';

const DIAS_SEMANA = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
const MAX_CARDS = 2;

export type ItemCalendarioVisita =
    | { tipo: 'visita'; data: Visita; dataInicio: Date }
    | { tipo: 'evento'; data: Evento; dataInicio: Date };

interface Props {
    visitas: Visita[];
    eventos?: Evento[];
    mes: string; // YYYY-MM
    onSelecionarVisita: (visita: Visita) => void;
    onSelecionarEvento?: (evento: Evento) => void;
    onAbrirListaCompleta: (
        dia: Date,
        visitas: Visita[],
        eventos: Evento[],
    ) => void;
}

function gerarDiasDoMes(mes: string): Date[] {
    const [ano, mesNum] = mes.split('-').map(Number);
    const primeiroDia = new Date(ano, mesNum - 1, 1);
    const ultimoDia = new Date(ano, mesNum, 0);

    const dias: Date[] = [];

    // Dias do mês anterior para completar a primeira semana
    const diaInicio = primeiroDia.getDay(); // 0 = Dom
    for (let i = diaInicio - 1; i >= 0; i--) {
        const d = new Date(primeiroDia);
        d.setDate(d.getDate() - (i + 1));
        dias.push(d);
    }

    // Dias do mês atual
    for (
        let d = new Date(primeiroDia);
        d <= ultimoDia;
        d.setDate(d.getDate() + 1)
    ) {
        dias.push(new Date(d));
    }

    // Dias do próximo mês para completar a última semana
    const restante = 7 - (dias.length % 7);
    if (restante < 7) {
        for (let i = 1; i <= restante; i++) {
            const d = new Date(ultimoDia);
            d.setDate(d.getDate() + i);
            dias.push(d);
        }
    }

    return dias;
}

function mesmodia(d1: Date, d2: Date): boolean {
    return (
        d1.getFullYear() === d2.getFullYear() &&
        d1.getMonth() === d2.getMonth() &&
        d1.getDate() === d2.getDate()
    );
}

const Show: FC<Props> = ({
    visitas,
    eventos = [],
    mes,
    onSelecionarVisita,
    onSelecionarEvento,
    onAbrirListaCompleta,
}) => {
    const dias = gerarDiasDoMes(mes);
    const [ano, mesNum] = mes.split('-').map(Number);
    const hoje = new Date();

    return (
        <div className="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
            <div>
                {/* Cabeçalho dias da semana */}
                <div className="grid grid-cols-7 border-b border-amber-100">
                    {DIAS_SEMANA.map((dia) => (
                        <div
                            key={dia}
                            className="py-2 text-center text-[10px] font-semibold tracking-wide text-amber-700/70 uppercase sm:text-xs"
                        >
                            <span className="sm:hidden">{dia.charAt(0)}</span>
                            <span className="hidden sm:inline">{dia}</span>
                        </div>
                    ))}
                </div>

                {/* Grade de dias */}
                <div className="grid grid-cols-7">
                    {dias.map((dia, idx) => {
                        const ehMesAtual =
                            dia.getMonth() === mesNum - 1 &&
                            dia.getFullYear() === ano;
                        const visitasDoDia = visitas.filter((v) =>
                            mesmodia(new Date(v.inicio_em), dia),
                        );
                        const eventosDoDia = eventos.filter((e) =>
                            mesmodia(new Date(e.data_inicio), dia),
                        );

                        const itensDoDia: ItemCalendarioVisita[] = [
                            ...visitasDoDia.map((v) => ({
                                tipo: 'visita' as const,
                                data: v,
                                dataInicio: new Date(v.inicio_em),
                            })),
                            ...eventosDoDia.map((e) => ({
                                tipo: 'evento' as const,
                                data: e,
                                dataInicio: new Date(e.data_inicio),
                            })),
                        ].sort(
                            (a, b) =>
                                a.dataInicio.getTime() - b.dataInicio.getTime(),
                        );

                        const itensVisiveis = itensDoDia.slice(0, MAX_CARDS);
                        const overflow = itensDoDia.length - MAX_CARDS;

                        const ehHoje = mesmodia(dia, hoje);

                        return (
                            <div
                                key={idx}
                                className={`min-h-16 min-w-0 border-r border-b border-gray-100 p-1 sm:min-h-24 sm:p-1.5 ${
                                    !ehMesAtual ? 'bg-gray-50/50' : ''
                                }`}
                            >
                                <span
                                    className={`mb-1 flex size-5 items-center justify-center rounded-full text-[11px] font-medium sm:size-6 sm:text-xs ${
                                        ehHoje
                                            ? 'bg-amber-500 text-white'
                                            : ehMesAtual
                                              ? 'text-gray-700'
                                              : 'text-gray-300'
                                    }`}
                                >
                                    {dia.getDate()}
                                </span>

                                {itensDoDia.length > 0 && (
                                    <button
                                        type="button"
                                        onClick={() =>
                                            onAbrirListaCompleta(
                                                dia,
                                                visitasDoDia,
                                                eventosDoDia,
                                            )
                                        }
                                        className="flex min-h-8 w-full flex-wrap content-start items-start gap-1 rounded p-0.5 text-left sm:hidden"
                                        aria-label={`Ver ${itensDoDia.length} ${itensDoDia.length === 1 ? 'atividade' : 'atividades'} do dia ${dia.getDate()}`}
                                    >
                                        {itensDoDia.slice(0, 4).map((item) => (
                                            <span
                                                key={`${item.tipo}-${item.data.id}`}
                                                className={`size-2 rounded-full ${item.tipo === 'visita' ? classeIndicadorPorOcupacao(item.data) : 'bg-indigo-600'}`}
                                                aria-hidden
                                            />
                                        ))}
                                        {itensDoDia.length > 4 && (
                                            <span className="text-[9px] font-medium leading-2 text-amber-800">
                                                +{itensDoDia.length - 4}
                                            </span>
                                        )}
                                    </button>
                                )}

                                <div className="hidden space-y-0.5 sm:block">
                                    {itensVisiveis.map((item) =>
                                        item.tipo === 'visita' ? (
                                            <CardShow
                                                key={`visita-${item.data.id}`}
                                                visita={item.data}
                                                onClick={() =>
                                                    onSelecionarVisita(
                                                        item.data,
                                                    )
                                                }
                                            />
                                        ) : (
                                            <EventoCardShow
                                                key={`evento-${item.data.id}`}
                                                evento={item.data}
                                                onClick={() =>
                                                    onSelecionarEvento?.(
                                                        item.data,
                                                    )
                                                }
                                            />
                                        ),
                                    )}

                                    {overflow > 0 && (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                onAbrirListaCompleta(
                                                    dia,
                                                    visitasDoDia,
                                                    eventosDoDia,
                                                )
                                            }
                                            className="w-full rounded px-1 py-0.5 text-left text-xs text-amber-700 hover:bg-amber-50"
                                        >
                                            +{overflow} mais
                                        </button>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </div>
    );
};

export default Show;
