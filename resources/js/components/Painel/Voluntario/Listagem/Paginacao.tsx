import { Link } from '@inertiajs/react';
import { PaginatedVoluntarios } from './types';

interface Props {
    paginacao: PaginatedVoluntarios;
}

const normalizarLabel = (label: string) => {
    return label
        .replace('&laquo; Previous', 'Anterior')
        .replace('Next &raquo;', 'Próxima')
        .replace('&laquo;', '')
        .replace('&raquo;', '');
};

const Paginacao: React.FC<Props> = ({ paginacao }) => {
    if (!paginacao?.links || paginacao.last_page <= 1) {
        return null;
    }

    return (
        <nav className="mt-4 flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-500 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <p>
                Mostrando {paginacao.from ?? 0} a {paginacao.to ?? 0} de{' '}
                {paginacao.total} voluntários
            </p>
            <div className="flex flex-wrap gap-1.5">
                {paginacao.links.map((link, index) =>
                    link.url ? (
                        <Link
                            key={`${link.label}-${index}`}
                            href={link.url}
                            preserveScroll
                            preserveState
                            className={`inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border px-3 font-medium transition ${
                                link.active
                                    ? 'border-amber-300 bg-amber-50 text-amber-800'
                                    : 'border-gray-200 bg-white text-gray-600 hover:border-amber-200 hover:bg-amber-50'
                            }`}
                        >
                            {normalizarLabel(link.label)}
                        </Link>
                    ) : (
                        <span
                            key={`${link.label}-${index}`}
                            className="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-gray-100 bg-gray-50 px-3 font-medium text-gray-300"
                        >
                            {normalizarLabel(link.label)}
                        </span>
                    ),
                )}
            </div>
        </nav>
    );
};

export default Paginacao;
