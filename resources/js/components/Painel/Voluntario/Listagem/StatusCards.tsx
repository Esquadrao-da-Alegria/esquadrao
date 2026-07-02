import { Eye } from 'lucide-react';
import { statusOptions } from './status';
import { StatusCounters, StatusFiltro, StatusOption } from './types';

interface Props {
    contadores: StatusCounters;
    statusFiltro: StatusFiltro;
    onStatusChange: (status: StatusFiltro) => void;
}

const StatusCards: React.FC<Props> = ({
    contadores,
    statusFiltro,
    onStatusChange,
}) => {
    const opcoes: Array<
        StatusOption | { key: 'todos'; label: string; className: string }
    > = [
        {
            key: 'todos',
            label: 'Todos',
            className: 'bg-amber-50 text-amber-800',
        },
        ...statusOptions,
    ];

    return (
        <section
            className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5"
            aria-label="Filtrar convites por status"
        >
            {opcoes.map((status) => {
                const selecionado = statusFiltro === status.key;
                const contador =
                    status.key === 'todos'
                        ? contadores.convidados
                        : contadores[status.key];

                return (
                    <button
                        key={status.key}
                        type="button"
                        onClick={() => onStatusChange(status.key)}
                        aria-pressed={selecionado}
                        aria-label={`Ver convites: ${status.label}`}
                        title={`Ver convites: ${status.label}`}
                        className={`group min-h-28 rounded-lg border bg-white px-4 py-4 text-left shadow-sm transition hover:border-amber-300 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 ${
                            selecionado
                                ? 'border-amber-400 ring-2 ring-amber-100'
                                : 'border-gray-200'
                        }`}
                    >
                        <span className="flex items-start justify-between gap-3">
                            <span className="text-2xl font-semibold text-gray-950">
                                {contador ?? 0}
                            </span>
                            <span
                                className={`flex size-8 shrink-0 items-center justify-center rounded-full transition ${
                                    selecionado
                                        ? 'bg-amber-100 text-amber-800'
                                        : 'bg-gray-50 text-gray-400 group-hover:bg-amber-50 group-hover:text-amber-700'
                                }`}
                                aria-hidden
                            >
                                <Eye className="size-4" />
                            </span>
                        </span>
                        <span
                            className={`mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${status.className}`}
                        >
                            {status.label}
                        </span>
                        <span className="mt-2 block text-xs font-medium text-gray-400 group-hover:text-amber-700">
                            {selecionado ? 'Filtro ativo' : 'Ver registros'}
                        </span>
                    </button>
                );
            })}
        </section>
    );
};

export default StatusCards;
