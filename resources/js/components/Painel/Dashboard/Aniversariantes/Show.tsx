import { router } from '@inertiajs/react';
import { CakeSlice, ChevronDown, ChevronUp, UsersRound } from 'lucide-react';
import { useState } from 'react';

interface Aniversariante {
    id: number;
    nome: string;
    foto_url: string | null;
    dia: number;
    mes: number;
    eh_hoje: boolean;
}

interface Props {
    aniversariantes: {
        itens: Aniversariante[];
        mes: number;
        cidade_id: number | null;
        possui_escopo_global: boolean;
        cidades: Array<{ id: number; nome: string }>;
    };
}

const meses = [
    'janeiro',
    'fevereiro',
    'março',
    'abril',
    'maio',
    'junho',
    'julho',
    'agosto',
    'setembro',
    'outubro',
    'novembro',
    'dezembro',
];

const iniciais = (nome: string) =>
    nome
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((parte) => parte[0])
        .join('')
        .toUpperCase();

const data = (dia: number, mes: number) =>
    `${String(dia).padStart(2, '0')}/${String(mes).padStart(2, '0')}`;

const Show: React.FC<Props> = ({ aniversariantes }) => {
    const [expandido, setExpandido] = useState(false);
    const aniversariantesHoje = aniversariantes.itens.filter(
        (aniversariante) => aniversariante.eh_hoje,
    );
    const itensVisiveis = expandido
        ? aniversariantes.itens
        : aniversariantes.itens.slice(0, 6);

    return (
        <section className="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div className="flex items-start gap-2.5">
                    <span className="flex size-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700">
                        <CakeSlice className="size-4" aria-hidden />
                    </span>
                    <div>
                        <h2 className="font-semibold text-amber-950">
                            Aniversariantes
                        </h2>
                        <p className="mt-1 text-sm text-gray-500">
                            Pessoas que celebram em {meses[aniversariantes.mes - 1]}.
                        </p>
                    </div>
                </div>

                {aniversariantes.possui_escopo_global && (
                    <select
                        value={aniversariantes.cidade_id ?? 'todas'}
                        onChange={(event) =>
                            router.get(
                                '/dashboard',
                                {
                                    cidade_aniversariantes_id:
                                        event.target.value,
                                },
                                {
                                    preserveScroll: true,
                                    preserveState: true,
                                    only: ['aniversariantes'],
                                },
                            )
                        }
                        className="h-9 max-w-full rounded-lg border border-amber-200 bg-white px-2 text-sm text-amber-950 focus:ring-2 focus:ring-amber-500/20 focus:outline-none"
                        aria-label="Cidade dos aniversariantes"
                    >
                        <option value="todas">Todas as cidades</option>
                        {aniversariantes.cidades.map((cidade) => (
                            <option key={cidade.id} value={cidade.id}>
                                {cidade.nome}
                            </option>
                        ))}
                    </select>
                )}
            </div>

            {aniversariantesHoje.length > 0 && (
                <div className="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                    <p className="text-xs font-semibold tracking-wide text-amber-800 uppercase">
                        Aniversariantes de hoje
                    </p>
                    <div className="mt-2 flex flex-wrap gap-2">
                        {aniversariantesHoje.map((aniversariante) => (
                            <span
                                key={aniversariante.id}
                                className="inline-flex items-center gap-2 rounded-full bg-white py-1 pr-3 pl-1 text-sm font-medium text-amber-950 shadow-sm"
                            >
                                <Avatar aniversariante={aniversariante} />
                                {aniversariante.nome}
                            </span>
                        ))}
                    </div>
                </div>
            )}

            {aniversariantes.itens.length === 0 ? (
                <p className="mt-5 flex items-center gap-2 rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-500">
                    <UsersRound className="size-4 shrink-0" aria-hidden />
                    Nenhum aniversariante neste mês.
                </p>
            ) : (
                <ul className="mt-4 divide-y divide-amber-50">
                    {itensVisiveis.map((aniversariante) => (
                        <li
                            key={aniversariante.id}
                            className="flex items-center gap-3 py-2.5"
                        >
                            <Avatar aniversariante={aniversariante} />
                            <span className="min-w-0 flex-1 truncate text-sm font-medium text-amber-950">
                                {aniversariante.nome}
                            </span>
                            {aniversariante.eh_hoje && (
                                <span className="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800">
                                    Hoje
                                </span>
                            )}
                            <time className="shrink-0 text-xs font-medium text-gray-500">
                                {data(aniversariante.dia, aniversariante.mes)}
                            </time>
                        </li>
                    ))}
                </ul>
            )}

            {aniversariantes.itens.length > 6 && (
                <button
                    type="button"
                    onClick={() => setExpandido(!expandido)}
                    className="mt-3 inline-flex cursor-pointer items-center gap-1 text-sm font-semibold text-amber-700 hover:underline"
                >
                    {expandido ? 'Recolher' : 'Ver todos'}
                    {expandido ? (
                        <ChevronUp className="size-4" aria-hidden />
                    ) : (
                        <ChevronDown className="size-4" aria-hidden />
                    )}
                </button>
            )}
        </section>
    );
};

function Avatar({ aniversariante }: { aniversariante: Aniversariante }) {
    if (aniversariante.foto_url) {
        return (
            <img
                src={aniversariante.foto_url}
                alt=""
                className="size-8 shrink-0 rounded-full object-cover"
            />
        );
    }

    return (
        <span className="flex size-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-[10px] font-semibold text-amber-800">
            {iniciais(aniversariante.nome)}
        </span>
    );
}

export default Show;
