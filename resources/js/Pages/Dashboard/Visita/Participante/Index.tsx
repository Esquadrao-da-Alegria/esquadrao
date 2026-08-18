import DetalhesModal from '@/components/Painel/Dashboard/Visita/Participante/Detalhes/Modal/Show';
import PainelLayout from '@/layouts/PainelLayout';
import { visitasPorParticipante } from '@/routes/dashboards';
import type {
    AcompanhamentoParticipante,
    FiltrosParticipante,
} from '@/types/dashboard-participante';
import { Head, Link, router } from '@inertiajs/react';
import {
    AlertTriangle,
    CheckCircle2,
    ChevronDown,
    Eye,
    MapPin,
    Search,
    ShieldQuestion,
    SlidersHorizontal,
    UsersRound,
    X,
} from 'lucide-react';
import { cloneElement, useEffect, useState } from 'react';

interface Opcao {
    id: number;
    nome?: string;
    name?: string;
}

interface Props {
    participantes: {
        data: AcompanhamentoParticipante[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    indicadores: Record<string, number>;
    filtros: FiltrosParticipante;
    opcoes: { cidades: Opcao[]; cargos: Opcao[]; participantes: Opcao[] };
    escopo_global: boolean;
}

const textos: Record<string, string> = {
    dentro_meta: 'Dentro da meta',
    atencao: 'Atenção',
    compensacao_pendente: 'Compensação pendente',
    requer_analise: 'Requer análise',
    isento: 'Isento',
    dados_insuficientes: 'Dados insuficientes',
};

const estiloSituacao: Record<string, string> = {
    dentro_meta: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    atencao: 'bg-amber-50 text-amber-800 ring-amber-200',
    compensacao_pendente: 'bg-amber-50 text-amber-800 ring-amber-200',
    requer_analise: 'bg-red-50 text-red-700 ring-red-200',
    isento: 'bg-gray-100 text-gray-600 ring-gray-200',
    dados_insuficientes: 'bg-gray-100 text-gray-600 ring-gray-200',
};

export default function Index({
    participantes,
    indicadores,
    filtros,
    opcoes,
    escopo_global,
}: Props) {
    const [busca, setBusca] = useState(filtros.busca ?? '');
    const [rascunho, setRascunho] = useState({
        periodo_tipo: filtros.periodo_tipo,
        ano: String(filtros.ano),
        mes: String(filtros.mes ?? new Date().getMonth() + 1),
        semestre: String(filtros.semestre ?? 1),
        cidade: filtros.visao_global
            ? 'todas'
            : String(filtros.cidade_id ?? ''),
        cargo_id: String(filtros.cargo_id ?? ''),
        tipo_atuacao: filtros.tipo_atuacao ?? '',
        situacao: filtros.situacao ?? '',
        atividade: filtros.atividade ?? '',
    });
    const quantidadeAvancados = [
        filtros.cargo_id,
        filtros.tipo_atuacao,
        filtros.situacao,
        filtros.atividade,
    ].filter(Boolean).length;
    const [filtrosAvancados, setFiltrosAvancados] = useState(
        quantidadeAvancados > 0,
    );
    const [consultando, setConsultando] = useState(false);
    const [participanteSelecionado, setParticipanteSelecionado] =
        useState<AcompanhamentoParticipante | null>(null);

    const consultar = (
        alteracoes: Record<string, string | number | undefined | null>,
        preservarEstado = true,
    ) => {
        const query = {
            ...filtros,
            ...alteracoes,
            page: undefined,
        };

        router.get(
            visitasPorParticipante().url,
            Object.fromEntries(
                Object.entries(query).filter(
                    ([, valor]) =>
                        valor !== '' && valor !== null && valor !== undefined,
                ),
            ),
            {
                preserveScroll: true,
                preserveState: preservarEstado,
                replace: true,
                onStart: () => setConsultando(true),
                onFinish: () => setConsultando(false),
            },
        );
    };

    const aplicarFiltros = () =>
        consultar({
            busca: busca || undefined,
            periodo_tipo: rascunho.periodo_tipo,
            ano: rascunho.ano,
            mes: rascunho.periodo_tipo === 'mes' ? rascunho.mes : undefined,
            semestre:
                rascunho.periodo_tipo === 'semestre'
                    ? rascunho.semestre
                    : undefined,
            cidade_id:
                rascunho.cidade === 'todas' ? undefined : rascunho.cidade,
            visao_global: rascunho.cidade === 'todas' ? 1 : undefined,
            cargo_id: rascunho.cargo_id || undefined,
            tipo_atuacao: rascunho.tipo_atuacao || undefined,
            situacao: rascunho.situacao || undefined,
            atividade: rascunho.atividade || undefined,
        });

    const limparAvancados = () => {
        setRascunho((atual) => ({
            ...atual,
            cargo_id: '',
            tipo_atuacao: '',
            situacao: '',
            atividade: '',
        }));
        consultar({
            cargo_id: undefined,
            tipo_atuacao: undefined,
            situacao: undefined,
            atividade: undefined,
        });
    };

    useEffect(() => {
        setRascunho({
            periodo_tipo: filtros.periodo_tipo,
            ano: String(filtros.ano),
            mes: String(filtros.mes ?? new Date().getMonth() + 1),
            semestre: String(filtros.semestre ?? 1),
            cidade: filtros.visao_global
                ? 'todas'
                : String(filtros.cidade_id ?? ''),
            cargo_id: String(filtros.cargo_id ?? ''),
            tipo_atuacao: filtros.tipo_atuacao ?? '',
            situacao: filtros.situacao ?? '',
            atividade: filtros.atividade ?? '',
        });
    }, [filtros]);

    return (
        <PainelLayout>
            <Head title="Visitas por participante" />
            <div className="mx-auto max-w-7xl space-y-6 px-5 py-8 sm:px-6 lg:px-8">
                <header>
                    <p className="text-sm font-semibold text-amber-700">
                        Apoio à decisão
                    </p>
                    <h1 className="mt-1 text-2xl font-semibold text-amber-950 sm:text-3xl">
                        Participação dos voluntários
                    </h1>
                    <p className="mt-2 max-w-3xl text-sm leading-relaxed text-amber-900/60">
                        Indicadores organizam os dados para análise humana.
                        Nenhuma sinalização aplica advertência, afastamento ou
                        altera o cadastro.
                    </p>
                </header>

                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        aplicarFiltros();
                    }}
                    className="space-y-3"
                >
                    <div className="flex flex-col gap-3 lg:flex-row">
                        <div className="relative flex-1">
                            <Search
                                className="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-gray-400"
                                aria-hidden
                            />
                            <input
                                type="search"
                                value={busca}
                                onChange={(event) =>
                                    setBusca(event.target.value)
                                }
                                placeholder="Buscar por nome ou e-mail..."
                                className="h-11 w-full rounded-2xl border border-gray-200 bg-white pr-4 pl-11 text-sm text-gray-700 shadow-sm transition placeholder:text-gray-400 focus:border-amber-300 focus:ring-2 focus:ring-amber-100 focus:outline-none"
                            />
                        </div>
                        <select
                            value={rascunho.periodo_tipo}
                            onChange={(event) =>
                                setRascunho((atual) => ({
                                    ...atual,
                                    periodo_tipo: event.target
                                        .value as FiltrosParticipante['periodo_tipo'],
                                }))
                            }
                            className="h-11 rounded-2xl border border-gray-200 bg-white px-4 text-sm shadow-sm focus:border-amber-300 focus:ring-2 focus:ring-amber-100 focus:outline-none lg:w-44"
                        >
                            <option value="mes">Por mês</option>
                            <option value="semestre">Por semestre</option>
                            <option value="ano">Por ano</option>
                        </select>
                        <select
                            value={rascunho.cidade}
                            disabled={!escopo_global}
                            onChange={(event) =>
                                setRascunho((atual) => ({
                                    ...atual,
                                    cidade: event.target.value,
                                }))
                            }
                            className="h-11 rounded-2xl border border-gray-200 bg-white px-4 text-sm shadow-sm focus:border-amber-300 focus:ring-2 focus:ring-amber-100 focus:outline-none disabled:bg-amber-50 lg:w-60"
                        >
                            {escopo_global && (
                                <option value="todas">Todas as cidades</option>
                            )}
                            {opcoes.cidades.map((cidade) => (
                                <option key={cidade.id} value={cidade.id}>
                                    {cidade.nome}
                                </option>
                            ))}
                        </select>
                        <button
                            type="button"
                            onClick={() =>
                                setFiltrosAvancados((aberto) => !aberto)
                            }
                            aria-expanded={filtrosAvancados}
                            className="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 text-sm font-medium text-amber-900 shadow-sm transition hover:border-amber-300 hover:bg-amber-50"
                        >
                            <SlidersHorizontal className="size-4" /> Mais
                            filtros
                            {quantidadeAvancados > 0 &&
                                ` (${quantidadeAvancados})`}{' '}
                            <ChevronDown
                                className={`size-4 transition ${filtrosAvancados ? 'rotate-180' : ''}`}
                            />
                        </button>
                        <button
                            type="submit"
                            disabled={consultando}
                            className="inline-flex h-11 items-center justify-center rounded-2xl border-2 border-amber-600 bg-white px-5 text-sm font-semibold text-amber-700 shadow-sm transition hover:bg-amber-50 disabled:cursor-wait disabled:opacity-60"
                        >
                            {consultando ? 'Aplicando...' : 'Aplicar filtros'}
                        </button>
                    </div>

                    {filtrosAvancados && (
                        <div className="grid gap-3 rounded-2xl border border-amber-100 bg-amber-50/40 p-4 sm:grid-cols-2 lg:grid-cols-4">
                            <Campo label="Ano">
                                <input
                                    type="number"
                                    min="2020"
                                    max="2100"
                                    value={rascunho.ano}
                                    onChange={(event) =>
                                        setRascunho((atual) => ({
                                            ...atual,
                                            ano: event.target.value,
                                        }))
                                    }
                                />
                            </Campo>
                            {rascunho.periodo_tipo === 'mes' && (
                                <Campo label="Mês">
                                    <select
                                        value={rascunho.mes}
                                        onChange={(event) =>
                                            setRascunho((atual) => ({
                                                ...atual,
                                                mes: event.target.value,
                                            }))
                                        }
                                    >
                                        {Array.from(
                                            { length: 12 },
                                            (_, indice) => (
                                                <option
                                                    key={indice + 1}
                                                    value={indice + 1}
                                                >
                                                    {new Date(
                                                        2026,
                                                        indice,
                                                    ).toLocaleDateString(
                                                        'pt-BR',
                                                        { month: 'long' },
                                                    )}
                                                </option>
                                            ),
                                        )}
                                    </select>
                                </Campo>
                            )}
                            {rascunho.periodo_tipo === 'semestre' && (
                                <Campo label="Semestre">
                                    <select
                                        value={rascunho.semestre}
                                        onChange={(event) =>
                                            setRascunho((atual) => ({
                                                ...atual,
                                                semestre: event.target.value,
                                            }))
                                        }
                                    >
                                        <option value="1">1º semestre</option>
                                        <option value="2">2º semestre</option>
                                    </select>
                                </Campo>
                            )}
                            <Campo label="Cargo">
                                <select
                                    value={rascunho.cargo_id}
                                    onChange={(event) =>
                                        setRascunho((atual) => ({
                                            ...atual,
                                            cargo_id: event.target.value,
                                        }))
                                    }
                                >
                                    <option value="">Todos</option>
                                    {opcoes.cargos.map((cargo) => (
                                        <option key={cargo.id} value={cargo.id}>
                                            {cargo.nome}
                                        </option>
                                    ))}
                                </select>
                            </Campo>
                            <Campo label="Tipo de atuação">
                                <select
                                    value={rascunho.tipo_atuacao}
                                    onChange={(event) =>
                                        setRascunho((atual) => ({
                                            ...atual,
                                            tipo_atuacao: event.target.value,
                                        }))
                                    }
                                >
                                    <option value="">Todos</option>
                                    <option value="visitas">Visitas</option>
                                    <option value="isento">
                                        Apoio / isento
                                    </option>
                                    <option value="dados_insuficientes">
                                        Não definido
                                    </option>
                                </select>
                            </Campo>
                            <Campo label="Situação">
                                <select
                                    value={rascunho.situacao}
                                    onChange={(event) =>
                                        setRascunho((atual) => ({
                                            ...atual,
                                            situacao: event.target.value,
                                        }))
                                    }
                                >
                                    <option value="">Todas</option>
                                    {Object.entries(textos).map(
                                        ([valor, texto]) => (
                                            <option key={valor} value={valor}>
                                                {texto}
                                            </option>
                                        ),
                                    )}
                                </select>
                            </Campo>
                            <Campo label="Participou em">
                                <select
                                    value={rascunho.atividade}
                                    onChange={(event) =>
                                        setRascunho((atual) => ({
                                            ...atual,
                                            atividade: event.target.value,
                                        }))
                                    }
                                >
                                    <option value="">Todos os eventos</option>
                                    <option value="visitas">
                                        Visitas válidas
                                    </option>
                                    <option value="reunioes">Reuniões</option>
                                    <option value="oficinas">Oficinas</option>
                                </select>
                            </Campo>
                            <button
                                type="button"
                                onClick={limparAvancados}
                                className="self-end rounded-full px-4 py-2 text-sm font-medium text-amber-800 transition hover:bg-amber-100"
                            >
                                Limpar filtros avançados
                            </button>
                        </div>
                    )}

                    {quantidadeAvancados > 0 && (
                        <div className="flex flex-wrap items-center gap-2 text-xs text-amber-900/70">
                            <span>Filtros ativos:</span>
                            {filtros.cargo_id && (
                                <FiltroAtivo
                                    texto={`Cargo: ${opcoes.cargos.find((cargo) => cargo.id === Number(filtros.cargo_id))?.nome ?? filtros.cargo_id}`}
                                    onRemove={() =>
                                        consultar({ cargo_id: undefined })
                                    }
                                />
                            )}
                            {filtros.tipo_atuacao && (
                                <FiltroAtivo
                                    texto={`Atuação: ${filtros.tipo_atuacao === 'isento' ? 'Apoio / isento' : filtros.tipo_atuacao === 'visitas' ? 'Visitas' : 'Não definido'}`}
                                    onRemove={() =>
                                        consultar({ tipo_atuacao: undefined })
                                    }
                                />
                            )}
                            {filtros.situacao && (
                                <FiltroAtivo
                                    texto={`Situação: ${textos[filtros.situacao]}`}
                                    onRemove={() =>
                                        consultar({ situacao: undefined })
                                    }
                                />
                            )}
                            {filtros.atividade && (
                                <FiltroAtivo
                                    texto={`Atividade: ${filtros.atividade}`}
                                    onRemove={() =>
                                        consultar({ atividade: undefined })
                                    }
                                />
                            )}
                        </div>
                    )}
                </form>

                <section className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <Card
                        icon={UsersRound}
                        titulo="Acompanhados"
                        valor={indicadores.total}
                    />
                    <Card
                        icon={CheckCircle2}
                        titulo="Dentro da meta"
                        valor={indicadores.dentro_meta}
                        tom="verde"
                    />
                    <Card
                        icon={AlertTriangle}
                        titulo="Requer análise"
                        valor={indicadores.requer_analise}
                        tom="vermelho"
                    />
                    <Card
                        icon={ShieldQuestion}
                        titulo="Dados insuficientes"
                        valor={indicadores.dados_insuficientes}
                    />
                </section>

                <section className="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
                    <div className="border-b border-amber-100 p-5">
                        <h2 className="font-semibold text-amber-950">
                            Acompanhamento
                        </h2>
                        <p className="mt-1 text-sm text-gray-500">
                            Consulte o resumo e abra os detalhes para
                            compreender os indicadores.
                        </p>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[720px] text-left text-sm">
                            <thead className="bg-amber-50/70 text-xs text-amber-900/60 uppercase">
                                <tr>
                                    <th className="px-5 py-3">Voluntário</th>
                                    <th className="px-4 py-3">Eventos</th>
                                    <th className="px-4 py-3">Situação</th>
                                    <th className="px-5 py-3 text-right">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-amber-50">
                                {participantes.data.map((participante) => {
                                    const totalEventos =
                                        participante.visitas_validas +
                                        participante.reunioes.presencas +
                                        participante.oficinas.presencas;
                                    return (
                                        <tr
                                            key={participante.id}
                                            className="hover:bg-amber-50/40"
                                        >
                                            <td className="px-5 py-4">
                                                <span className="font-semibold text-amber-950">
                                                    {participante.nome}
                                                </span>
                                                <span className="block text-xs text-gray-500">
                                                    <MapPin className="mr-1 inline size-3" />
                                                    {participante.cidade} ·{' '}
                                                    {participante.cargos.join(
                                                        ', ',
                                                    )}
                                                </span>
                                            </td>
                                            <td className="px-4 py-4">
                                                <span className="font-semibold text-amber-950">
                                                    {totalEventos}
                                                </span>
                                                <span className="block text-xs text-gray-500">
                                                    atividades documentadas
                                                </span>
                                            </td>
                                            <td className="px-4 py-4">
                                                <span
                                                    className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ${estiloSituacao[participante.situacao]}`}
                                                >
                                                    {
                                                        textos[
                                                            participante
                                                                .situacao
                                                        ]
                                                    }
                                                </span>
                                            </td>
                                            <td className="px-5 py-4 text-right">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        setParticipanteSelecionado(
                                                            participante,
                                                        )
                                                    }
                                                    aria-label={`Ver detalhes de ${participante.nome}`}
                                                    title={`Ver detalhes de ${participante.nome}`}
                                                    className="inline-flex size-9 items-center justify-center rounded-full border border-amber-200 text-amber-700 transition hover:bg-amber-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500"
                                                >
                                                    <Eye
                                                        className="size-4"
                                                        aria-hidden
                                                    />
                                                </button>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                    {participantes.data.length === 0 && (
                        <p className="p-10 text-center text-sm text-gray-500">
                            Nenhum voluntário encontrado para os filtros
                            selecionados.
                        </p>
                    )}
                    <div className="flex items-center justify-between border-t border-amber-100 p-4 text-sm text-gray-500">
                        <span>
                            Página {participantes.current_page} de{' '}
                            {participantes.last_page}
                        </span>
                        <div className="flex gap-2">
                            {participantes.prev_page_url && (
                                <Link
                                    preserveScroll
                                    href={participantes.prev_page_url}
                                    className="rounded-full border border-amber-200 px-3 py-1.5 text-amber-800"
                                >
                                    Anterior
                                </Link>
                            )}
                            {participantes.next_page_url && (
                                <Link
                                    preserveScroll
                                    href={participantes.next_page_url}
                                    className="rounded-full border border-amber-200 px-3 py-1.5 text-amber-800"
                                >
                                    Próxima
                                </Link>
                            )}
                        </div>
                    </div>
                </section>

                <p className="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-xs leading-relaxed text-gray-600">
                    <strong>Leitura dos dados:</strong> as cores facilitam a
                    consulta e não representam decisões automáticas. A
                    coordenação deve considerar contexto, justificativas e
                    histórico antes de qualquer decisão.
                </p>
            </div>

            <DetalhesModal
                participante={participanteSelecionado}
                filtros={filtros}
                onOpenChange={(aberto) =>
                    !aberto && setParticipanteSelecionado(null)
                }
            />
        </PainelLayout>
    );
}

function Campo({
    label,
    children,
}: {
    label: string;
    children: React.ReactElement<{ className?: string }>;
}) {
    return (
        <label className="space-y-1.5 text-sm font-medium text-amber-950">
            <span>{label}</span>
            {cloneElement(children, {
                className:
                    'h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm focus:border-amber-300 focus:ring-2 focus:ring-amber-100 focus:outline-none',
            })}
        </label>
    );
}

function FiltroAtivo({
    texto,
    onRemove,
}: {
    texto: string;
    onRemove: () => void;
}) {
    return (
        <span className="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-white px-2.5 py-1 font-medium text-amber-800">
            {texto}
            <button
                type="button"
                onClick={onRemove}
                aria-label={`Remover ${texto}`}
                className="rounded-full p-0.5 hover:bg-amber-100"
            >
                <X className="size-3" />
            </button>
        </span>
    );
}

function Card({
    icon: Icone,
    titulo,
    valor,
    tom,
}: {
    icon: typeof UsersRound;
    titulo: string;
    valor: number;
    tom?: string;
}) {
    return (
        <article className="rounded-xl border border-amber-100 bg-white p-4 shadow-sm">
            <div className="flex items-center justify-between">
                <div>
                    <p className="text-xs font-medium text-amber-900/60">
                        {titulo}
                    </p>
                    <p className="mt-1 text-2xl font-bold text-amber-950">
                        {valor}
                    </p>
                </div>
                <span className="flex size-9 items-center justify-center rounded-full bg-amber-50">
                    <Icone
                        className={`size-4 ${tom === 'verde' ? 'text-emerald-600' : tom === 'vermelho' ? 'text-red-500' : 'text-amber-600'}`}
                    />
                </span>
            </div>
        </article>
    );
}
