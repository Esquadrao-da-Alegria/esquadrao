import PainelLayout from '@/layouts/PainelLayout';
import { Head, router } from '@inertiajs/react';
import { CalendarDays, ChevronDown, ChevronLeft, ChevronRight, Eye, Search, UsersRound } from 'lucide-react';
import { useEffect, useState } from 'react';

interface Integrante {
    id: number;
    nome: string;
    cidade: string | null;
    situacao_cadastro: string;
    reunioes: number;
    oficinas: number;
    eventos: Array<{
        id: number;
        tipo: 'reuniao' | 'oficina';
        titulo: string;
        data_inicio: string;
    }>;
}

interface Cidade {
    id: number;
    nome: string;
}

interface Props {
    integrantes: {
        data: Integrante[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filtros: {
        ano: number;
        semestre: number;
        cidade_id: number | null;
        visao_global: boolean;
        busca: string | null;
        situacao: 'ativos' | 'inativos' | 'todos';
    };
    opcoes: { cidades: Cidade[] };
    escopo_global: boolean;
}

export default function Index({
    integrantes,
    filtros,
    opcoes,
    escopo_global: escopoGlobal,
}: Props) {
    const [busca, setBusca] = useState(filtros.busca ?? '');
    const [detalhesAbertos, setDetalhesAbertos] = useState<number[]>([]);
    const [rascunho, setRascunho] = useState({
        ano: String(filtros.ano),
        semestre: String(filtros.semestre),
        cidade: filtros.visao_global ? 'todas' : String(filtros.cidade_id ?? ''),
        situacao: filtros.situacao,
    });

    useEffect(() => {
        setBusca(filtros.busca ?? '');
        setRascunho({
            ano: String(filtros.ano),
            semestre: String(filtros.semestre),
            cidade: filtros.visao_global
                ? 'todas'
                : String(filtros.cidade_id ?? ''),
            situacao: filtros.situacao,
        });
    }, [filtros]);

    const consultar = (alteracoes: Record<string, string | number | undefined>) => {
        const parametros = {
            ...filtros,
            ...alteracoes,
            page: undefined,
        };

        router.get(
            '/dashboards/participacao-semestral',
            Object.fromEntries(
                Object.entries(parametros).filter(
                    ([, valor]) => valor !== '' && valor !== null && valor !== undefined,
                ),
            ),
            { preserveScroll: true, preserveState: true, replace: true },
        );
    };

    const aplicarFiltros = () =>
        consultar({
            ano: rascunho.ano,
            semestre: rascunho.semestre,
            busca: busca || undefined,
            situacao: rascunho.situacao,
            cidade_id: rascunho.cidade === 'todas' ? undefined : rascunho.cidade,
            visao_global: rascunho.cidade === 'todas' ? 1 : undefined,
        });

    const navegar = (url: string | null) => {
        if (url) router.get(url, {}, { preserveScroll: true, preserveState: true });
    };

    const alternarDetalhes = (id: number) =>
        setDetalhesAbertos((atuais) =>
            atuais.includes(id)
                ? atuais.filter((item) => item !== id)
                : [...atuais, id],
        );

    return (
        <PainelLayout>
            <Head title="Participação semestral" />
            <div className="mx-auto max-w-7xl space-y-6 px-5 py-8 sm:px-6 lg:px-8">
                <header>
                    <p className="text-sm font-semibold text-amber-700">
                        Acompanhamento
                    </p>
                    <h1 className="mt-1 text-2xl font-semibold text-amber-950 sm:text-3xl">
                        Participação semestral
                    </h1>
                    <p className="mt-2 max-w-3xl text-sm leading-relaxed text-amber-900/60">
                        Presenças confirmadas em reuniões e oficinas da cidade-base de cada integrante.
                    </p>
                </header>

                <section className="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm sm:p-5">
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        <label className="text-sm font-medium text-amber-950">
                            Ano
                            <input
                                type="number"
                                min="2020"
                                max="2100"
                                value={rascunho.ano}
                                onChange={(event) => setRascunho((atual) => ({ ...atual, ano: event.target.value }))}
                                className="mt-1 block h-10 w-full rounded-lg border border-amber-200 px-3 text-sm focus:ring-2 focus:ring-amber-500/20 focus:outline-none"
                            />
                        </label>
                        <label className="text-sm font-medium text-amber-950">
                            Semestre
                            <select
                                value={rascunho.semestre}
                                onChange={(event) => setRascunho((atual) => ({ ...atual, semestre: event.target.value }))}
                                className="mt-1 block h-10 w-full rounded-lg border border-amber-200 bg-white px-3 text-sm focus:ring-2 focus:ring-amber-500/20 focus:outline-none"
                            >
                                <option value="1">1º semestre</option>
                                <option value="2">2º semestre</option>
                            </select>
                        </label>
                        {escopoGlobal && (
                            <label className="text-sm font-medium text-amber-950">
                                Cidade
                                <select
                                    value={rascunho.cidade}
                                    onChange={(event) => setRascunho((atual) => ({ ...atual, cidade: event.target.value }))}
                                    className="mt-1 block h-10 w-full rounded-lg border border-amber-200 bg-white px-3 text-sm focus:ring-2 focus:ring-amber-500/20 focus:outline-none"
                                >
                                    <option value="todas">Todas as cidades</option>
                                    {opcoes.cidades.map((cidade) => (
                                        <option key={cidade.id} value={cidade.id}>{cidade.nome}</option>
                                    ))}
                                </select>
                            </label>
                        )}
                        <label className="text-sm font-medium text-amber-950">
                            Situação atual
                            <select
                                value={rascunho.situacao}
                                onChange={(event) => setRascunho((atual) => ({ ...atual, situacao: event.target.value as typeof atual.situacao }))}
                                className="mt-1 block h-10 w-full rounded-lg border border-amber-200 bg-white px-3 text-sm focus:ring-2 focus:ring-amber-500/20 focus:outline-none"
                            >
                                <option value="ativos">Ativos</option>
                                <option value="inativos">Inativos</option>
                                <option value="todos">Todos</option>
                            </select>
                        </label>
                        <label className="text-sm font-medium text-amber-950">
                            Integrante
                            <div className="relative mt-1">
                                <Search className="pointer-events-none absolute top-3 left-3 size-4 text-amber-600" aria-hidden />
                                <input
                                    value={busca}
                                    onChange={(event) => setBusca(event.target.value)}
                                    onKeyDown={(event) => event.key === 'Enter' && aplicarFiltros()}
                                    placeholder="Nome ou e-mail"
                                    className="block h-10 w-full rounded-lg border border-amber-200 py-2 pr-3 pl-9 text-sm focus:ring-2 focus:ring-amber-500/20 focus:outline-none"
                                />
                            </div>
                        </label>
                    </div>
                    <div className="mt-4 flex justify-end">
                        <button
                            type="button"
                            onClick={aplicarFiltros}
                            className="rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-amber-950 transition hover:bg-amber-400 focus:ring-2 focus:ring-amber-500/30 focus:outline-none"
                        >
                            Aplicar filtros
                        </button>
                    </div>
                </section>

                <section className="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
                    <div className="flex items-center gap-2 border-b border-amber-100 px-4 py-4 sm:px-5">
                        <UsersRound className="size-4 text-amber-700" aria-hidden />
                        <h2 className="font-semibold text-amber-950">Integrantes</h2>
                    </div>
                    {integrantes.data.length === 0 ? (
                        <div className="px-5 py-10 text-center text-sm text-gray-500">
                            Nenhum integrante encontrado para estes filtros.
                        </div>
                    ) : (
                        <>
                            <div className="divide-y divide-amber-50">
                                {integrantes.data.map((integrante) => {
                                    const aberto = detalhesAbertos.includes(integrante.id);

                                    return (
                                        <article key={integrante.id}>
                                            <div className="grid grid-cols-[minmax(0,1fr)_auto] gap-x-4 gap-y-3 px-4 py-4 sm:grid-cols-[minmax(14rem,1fr)_auto_auto] sm:items-center sm:px-5">
                                                <div className="min-w-0">
                                                    <p className="truncate text-sm font-semibold text-amber-950">{integrante.nome}</p>
                                                    <p className="mt-1 truncate text-xs text-gray-500">
                                                        {integrante.cidade ?? 'Sem cidade-base'}
                                                        {integrante.situacao_cadastro === 'inativo' && ' · Inativo'}
                                                    </p>
                                                </div>
                                                <div className="flex items-center gap-4 text-right sm:gap-6">
                                                    <Quantidade titulo="Oficinas" valor={integrante.oficinas} />
                                                    <Quantidade titulo="Reuniões" valor={integrante.reunioes} />
                                                </div>
                                                {integrante.eventos.length > 0 && (
                                                    <button
                                                        type="button"
                                                        onClick={() => alternarDetalhes(integrante.id)}
                                                        className="col-span-2 inline-flex items-center justify-self-start gap-1.5 rounded-lg px-2 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-50 sm:col-span-1 sm:justify-self-end"
                                                        aria-expanded={aberto}
                                                    >
                                                        <Eye className="size-4" aria-hidden />
                                                        Ver {integrante.eventos.length} {integrante.eventos.length === 1 ? 'atividade' : 'atividades'}
                                                        <ChevronDown className={`size-3.5 transition ${aberto ? 'rotate-180' : ''}`} aria-hidden />
                                                    </button>
                                                )}
                                            </div>
                                            {aberto && (
                                                <div className="border-t border-amber-50 bg-amber-50/50 px-4 py-3 sm:px-5">
                                                    {integrante.eventos.length === 0 ? (
                                                        <p className="text-xs text-gray-500">Nenhuma presença confirmada neste semestre.</p>
                                                    ) : (
                                                        <ul className="space-y-2">
                                                            {integrante.eventos.map((evento) => (
                                                                <li key={evento.id} className="flex items-center justify-between gap-3 text-sm">
                                                                    <span className="min-w-0 truncate text-amber-950">{evento.titulo}</span>
                                                                    <span className="shrink-0 text-xs text-gray-500">
                                                                        {evento.tipo === 'reuniao' ? 'Reunião' : 'Oficina'} · {formatarData(evento.data_inicio)}
                                                                    </span>
                                                                </li>
                                                            ))}
                                                        </ul>
                                                    )}
                                                </div>
                                            )}
                                        </article>
                                    );
                                })}
                            </div>
                            <div className="flex items-center justify-between border-t border-amber-100 px-4 py-3 sm:px-5">
                                <span className="text-xs text-gray-500">Página {integrantes.current_page} de {integrantes.last_page}</span>
                                <div className="flex gap-2">
                                    <BotaoPaginacao titulo="Página anterior" icone={ChevronLeft} disabled={!integrantes.prev_page_url} onClick={() => navegar(integrantes.prev_page_url)} />
                                    <BotaoPaginacao titulo="Próxima página" icone={ChevronRight} disabled={!integrantes.next_page_url} onClick={() => navegar(integrantes.next_page_url)} />
                                </div>
                            </div>
                        </>
                    )}
                </section>

                <p className="flex items-start gap-2 px-1 text-xs leading-relaxed text-gray-500">
                    <CalendarDays className="mt-0.5 size-4 shrink-0 text-amber-600" aria-hidden />
                    Os totais usam somente eventos finalizados e presenças registradas como presentes. A situação exibida é a situação atual do cadastro.
                </p>
            </div>
        </PainelLayout>
    );
}

function Quantidade({ titulo, valor }: { titulo: string; valor: number }) {
    return (
        <div className="text-right">
            <p className="text-[11px] font-medium tracking-wide text-gray-500 uppercase">{titulo}</p>
            <p className="mt-0.5 text-lg font-semibold text-amber-950">{valor}</p>
        </div>
    );
}

function formatarData(data: string) {
    const [ano, mes, dia] = data.slice(0, 10).split('-');

    return `${dia}/${mes}/${ano}`;
}

function BotaoPaginacao({
    titulo,
    icone: Icone,
    disabled,
    onClick,
}: {
    titulo: string;
    icone: typeof ChevronLeft;
    disabled: boolean;
    onClick: () => void;
}) {
    return (
        <button
            type="button"
            aria-label={titulo}
            disabled={disabled}
            onClick={onClick}
            className="flex size-8 items-center justify-center rounded-lg border border-amber-200 text-amber-800 transition hover:bg-amber-50 disabled:cursor-not-allowed disabled:opacity-40"
        >
            <Icone className="size-4" aria-hidden />
        </button>
    );
}
