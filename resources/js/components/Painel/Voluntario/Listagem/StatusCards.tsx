import { statusOptions } from './status';
import { StatusCounters, StatusFiltro } from './types';

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
    return (
        <section className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            {statusOptions.map((status) => (
                <button
                    key={status.key}
                    type="button"
                    onClick={() => onStatusChange(status.key)}
                    className={`min-h-20 rounded-2xl border bg-white px-4 py-4 text-left shadow-sm transition hover:border-amber-200 hover:shadow-md ${
                        statusFiltro === status.key
                            ? 'border-amber-300 ring-2 ring-amber-100'
                            : 'border-gray-200'
                    }`}
                >
                    <span className="block text-2xl font-semibold text-gray-950">
                        {contadores[status.key] ?? 0}
                    </span>
                    <span
                        className={`mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${status.className}`}
                    >
                        {status.label}
                    </span>
                </button>
            ))}
        </section>
    );
};

export default StatusCards;
