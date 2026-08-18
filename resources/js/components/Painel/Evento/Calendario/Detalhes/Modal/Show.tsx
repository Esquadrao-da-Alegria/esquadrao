import { Link } from '@inertiajs/react';
import { type FC } from 'react';

import Modal from '@/components/Modal/Show';
import { labelStatus, labelTipo } from '@/lib/evento';
import { show } from '@/routes/eventos';
import type { Evento } from '@/types';

interface Props {
    evento: Evento | null;
    onFechar: () => void;
}

const Show: FC<Props> = ({ evento, onFechar }) => {
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
        <Modal isOpen={evento !== null} onClose={onFechar} className="max-w-md">
            {evento && (
                <div className="p-6">
                    <div className="mb-4 flex items-start justify-between gap-4">
                        <h2 className="text-lg font-semibold text-gray-900">
                            Detalhes do evento
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

                    <dl className="space-y-3 text-sm">
                        <div>
                            <dt className="text-xs font-medium text-gray-400 uppercase">
                                Título
                            </dt>
                            <dd className="mt-0.5 font-medium text-gray-900">
                                {evento.titulo}
                            </dd>
                        </div>

                        <div>
                            <dt className="text-xs font-medium text-gray-400 uppercase">
                                Tipo
                            </dt>
                            <dd className="mt-0.5 text-gray-700">
                                {labelTipo(evento.tipo)}
                            </dd>
                        </div>

                        <div>
                            <dt className="text-xs font-medium text-gray-400 uppercase">
                                Data
                            </dt>
                            <dd className="mt-0.5 text-gray-700 capitalize">
                                {formatarData(new Date(evento.data_inicio))}
                            </dd>
                        </div>

                        <div>
                            <dt className="text-xs font-medium text-gray-400 uppercase">
                                Horário
                            </dt>
                            <dd className="mt-0.5 text-gray-700">
                                {formatarHora(new Date(evento.data_inicio))}
                                {evento.data_fim
                                    ? ` – ${formatarHora(new Date(evento.data_fim))}`
                                    : ''}
                            </dd>
                        </div>

                        <div>
                            <dt className="text-xs font-medium text-gray-400 uppercase">
                                Local
                            </dt>
                            <dd className="mt-0.5 text-gray-700">
                                {evento.local ?? '—'}
                            </dd>
                        </div>

                        <div>
                            <dt className="text-xs font-medium text-gray-400 uppercase">
                                Status
                            </dt>
                            <dd className="mt-0.5">
                                <span className="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                    {labelStatus(evento.status)}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt className="text-xs font-medium text-gray-400 uppercase">
                                Vagas
                            </dt>
                            <dd className="mt-0.5 text-gray-700">
                                {evento.participantes_ativos_count ?? 0}/
                                {evento.limite_participantes ?? '∞'}
                            </dd>
                        </div>

                        {evento.responsavel && (
                            <div>
                                <dt className="text-xs font-medium text-gray-400 uppercase">
                                    Responsável
                                </dt>
                                <dd className="mt-0.5 text-gray-700">
                                    {evento.responsavel.name}
                                </dd>
                            </div>
                        )}
                    </dl>

                    <div className="mt-6">
                        <Link
                            href={show({ evento: evento.id }).url}
                            onClick={onFechar}
                            className="inline-flex w-full items-center justify-center rounded-lg border border-amber-600 bg-white px-4 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-50"
                        >
                            Ver detalhes
                        </Link>
                    </div>
                </div>
            )}
        </Modal>
    );
};

export default Show;
