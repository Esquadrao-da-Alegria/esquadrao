import { type FC } from 'react';

import Modal from '@/components/Modal/Show';
import { classeCardPorStatus, labelStatus } from '@/lib/evento';
import type { Evento } from '@/types';

interface Props {
    dia: Date | null;
    eventos: Evento[];
    onFechar: () => void;
    onSelecionarEvento: (evento: Evento) => void;
}

const Show: FC<Props> = ({ dia, eventos, onFechar, onSelecionarEvento }) => {
    const formatarDia = (d: Date) =>
        d.toLocaleDateString('pt-BR', {
            weekday: 'long',
            day: '2-digit',
            month: 'long',
        });

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
                        {eventos.map((evento) => {
                            const hora = new Date(
                                evento.data_inicio,
                            ).toLocaleTimeString('pt-BR', {
                                hour: '2-digit',
                                minute: '2-digit',
                            });

                            return (
                                <li key={evento.id}>
                                    <button
                                        type="button"
                                        onClick={() =>
                                            onSelecionarEvento(evento)
                                        }
                                        className={`w-full rounded-lg border px-3 py-2 text-left text-sm transition hover:opacity-80 ${classeCardPorStatus(evento.status)}`}
                                    >
                                        <span className="block font-medium">
                                            {hora} · {evento.titulo}
                                        </span>
                                        <span className="block text-xs opacity-75">
                                            {labelStatus(evento.status)}
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
