import { type FC } from 'react';

import Modal from '@/components/Modal/Show';
import { labelStatus as labelStatusEvento } from '@/lib/evento';
import { classeCardPorStatus, labelStatus, tituloVisita } from '@/lib/visita';
import type { Evento } from '@/types';
import type { Visita } from '@/types/visita';
import { Calendar } from 'lucide-react';

interface Props {
    dia: Date | null;
    visitas: Visita[];
    eventos?: Evento[];
    onFechar: () => void;
    onSelecionarVisita: (visita: Visita) => void;
    onSelecionarEvento?: (evento: Evento) => void;
}

const Show: FC<Props> = ({
    dia,
    visitas,
    eventos = [],
    onFechar,
    onSelecionarVisita,
    onSelecionarEvento,
}) => {
    const formatarDia = (d: Date) =>
        d.toLocaleDateString('pt-BR', {
            weekday: 'long',
            day: '2-digit',
            month: 'long',
        });

    const itens = [
        ...visitas.map((v) => ({
            tipo: 'visita' as const,
            data: v,
            dataInicio: new Date(v.inicio_em),
        })),
        ...eventos.map((e) => ({
            tipo: 'evento' as const,
            data: e,
            dataInicio: new Date(e.data_inicio),
        })),
    ].sort((a, b) => a.dataInicio.getTime() - b.dataInicio.getTime());

    return (
        <Modal isOpen={dia !== null} onClose={onFechar} className="max-w-sm">
            {dia && (
                <div className="p-6">
                    <div className="mb-4 flex items-center justify-between gap-4">
                        <h2 className="text-base font-semibold text-gray-900 capitalize">
                            {formatarDia(dia)}
                        </h2>
                        <button
                            type="button"
                            onClick={onFechar}
                            className="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700"
                            aria-label="Fechar"
                        >
                            ✕
                        </button>
                    </div>

                    <ul className="space-y-2">
                        {itens.map((item) => {
                            if (item.tipo === 'visita') {
                                const visita = item.data;
                                const hora = item.dataInicio.toLocaleTimeString(
                                    'pt-BR',
                                    {
                                        hour: '2-digit',
                                        minute: '2-digit',
                                    },
                                );

                                return (
                                    <li key={`visita-${visita.id}`}>
                                        <button
                                            type="button"
                                            onClick={() =>
                                                onSelecionarVisita(visita)
                                            }
                                            className={`w-full rounded-lg border px-3 py-2 text-left text-sm transition hover:opacity-80 ${classeCardPorStatus(visita)}`}
                                        >
                                            <span className="block font-medium">
                                                {hora} · {tituloVisita(visita)}
                                            </span>
                                            <span className="block text-xs opacity-75">
                                                {labelStatus(visita.status)}
                                            </span>
                                        </button>
                                    </li>
                                );
                            }

                            const evento = item.data;
                            const hora = item.dataInicio.toLocaleTimeString(
                                'pt-BR',
                                {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                },
                            );

                            return (
                                <li key={`evento-${evento.id}`}>
                                    <button
                                        type="button"
                                        onClick={() =>
                                            onSelecionarEvento?.(evento)
                                        }
                                        className="w-full rounded-lg border border-indigo-600 bg-indigo-600 px-3 py-2 text-left text-sm text-white transition hover:opacity-85"
                                    >
                                        <span className="flex items-center gap-1 font-medium">
                                            <Calendar className="size-3.5 shrink-0 opacity-80" />
                                            {hora} · {evento.titulo}
                                        </span>
                                        <span className="block text-xs opacity-80">
                                            Evento ·{' '}
                                            {labelStatusEvento(evento.status)}
                                        </span>
                                    </button>
                                </li>
                            );
                        })}
                    </ul>
                </div>
            )}
        </Modal>
    );
};

export default Show;
