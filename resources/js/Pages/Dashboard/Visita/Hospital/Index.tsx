import PainelLayout from '@/layouts/PainelLayout';
import { visitasPorHospital } from '@/routes/dashboards';
import type {
    AlaDashboardHospital,
    EvolucaoDashboardHospital,
    FiltrosDashboardHospital,
    IndicadoresDashboardHospital,
    OpcaoDashboard,
    PaginacaoDashboard,
    ResumoDashboardHospital,
    VisitaDashboardHospital,
} from '@/types/dashboard';
import { Head, Link, router } from '@inertiajs/react';
import {
    BarChart3,
    Building2,
    CalendarRange,
    MapPin,
    Sparkles,
    UsersRound,
} from 'lucide-react';
import { useState } from 'react';

interface Props {
    filtros: FiltrosDashboardHospital;
    indicadores: IndicadoresDashboardHospital;
    evolucao: EvolucaoDashboardHospital[];
    hospitais: ResumoDashboardHospital[];
    detalhes: {
        possui_alas: boolean;
        alas: AlaDashboardHospital[];
        visitas: PaginacaoDashboard<VisitaDashboardHospital>;
    } | null;
    opcoes: {
        cidades: OpcaoDashboard[];
        hospitais: OpcaoDashboard[];
        alas: OpcaoDashboard[];
    };
    escopo_global: boolean;
}

const formatarNumero = (valor: number, casas = 0) =>
    valor.toLocaleString('pt-BR', {
        minimumFractionDigits: casas,
        maximumFractionDigits: casas,
    });

const formatarData = (valor: string) =>
    new Date(valor).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });

export default function Index({
    filtros,
    indicadores,
    evolucao,
    hospitais,
    detalhes,
    opcoes,
    escopo_global,
}: Props) {
    const [form, setForm] = useState({
        mes_inicio: filtros.mes_inicio,
        mes_fim: filtros.mes_fim,
        cidade_id: filtros.cidade_id?.toString() ?? '',
        visao_global: filtros.visao_global ? '1' : '',
        hospital_id: filtros.hospital_id?.toString() ?? '',
        ala_id: filtros.ala_id?.toString() ?? '',
    });

    const aplicarFiltros = (alteracoes: Partial<typeof form>) => {
        const novosFiltros = { ...form, ...alteracoes };
        setForm(novosFiltros);
        router.get(
            visitasPorHospital().url,
            Object.fromEntries(
                Object.entries(novosFiltros).filter(([, valor]) => valor !== ''),
            ),
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const limparFiltros = () =>
        router.get(visitasPorHospital().url, {}, { preserveScroll: true });

    const maiorEvolucao = Math.max(...evolucao.map((item) => item.total), 1);

    return (
        <PainelLayout>
            <Head title="Visitas por hospital" />

            <div className="mx-auto max-w-7xl space-y-6 px-5 py-8 sm:px-6 lg:px-8">
                <header className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm font-semibold text-amber-700">
                            Dashboard gerencial
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold tracking-tight text-amber-950 sm:text-3xl">
                            Visitas por hospital
                        </h1>
                        <p className="mt-2 max-w-2xl text-sm leading-relaxed text-amber-900/60">
                            Acompanhe a atuação por cidade, hospital e ala,
                            incluindo visitas que ainda não possuem relatório.
                        </p>
                    </div>
                    {!escopo_global && (
                        <span className="inline-flex w-fit items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800">
                            <MapPin className="size-3.5" /> Escopo da sua cidade
                        </span>
                    )}
                </header>

                <section className="space-y-3">
                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                        <Campo label="Mês inicial">
                            <input
                                type="month"
                                value={form.mes_inicio}
                                onChange={(event) => aplicarFiltros({ mes_inicio: event.target.value })}
                                className="h-11 w-full rounded-2xl border border-gray-200 bg-white px-4 text-sm shadow-sm focus:border-amber-300 focus:ring-2 focus:ring-amber-100 focus:outline-none"
                                required
                            />
                        </Campo>
                        <Campo label="Mês final">
                            <input
                                type="month"
                                value={form.mes_fim}
                                onChange={(event) => aplicarFiltros({ mes_fim: event.target.value })}
                                className="h-11 w-full rounded-2xl border border-gray-200 bg-white px-4 text-sm shadow-sm focus:border-amber-300 focus:ring-2 focus:ring-amber-100 focus:outline-none"
                                required
                            />
                        </Campo>
                        <Campo label="Cidade">
                            <select
                                value={form.visao_global ? 'todas' : form.cidade_id}
                                disabled={!escopo_global}
                                onChange={(event) =>
                                    aplicarFiltros({
                                        cidade_id: event.target.value === 'todas' ? '' : event.target.value,
                                        visao_global: event.target.value === 'todas' ? '1' : '',
                                        hospital_id: '',
                                        ala_id: '',
                                    })
                                }
                                className="h-11 w-full rounded-2xl border border-gray-200 bg-white px-4 text-sm shadow-sm focus:border-amber-300 focus:ring-2 focus:ring-amber-100 focus:outline-none disabled:bg-amber-50"
                            >
                                {escopo_global && <option value="todas">Todas as cidades</option>}
                                {opcoes.cidades.map((cidade) => (
                                    <option key={cidade.id} value={cidade.id}>
                                        {cidade.nome}
                                    </option>
                                ))}
                            </select>
                        </Campo>
                        <Campo label="Hospital">
                            <select
                                value={form.hospital_id}
                                disabled={!form.cidade_id}
                                onChange={(event) =>
                                    aplicarFiltros({
                                        hospital_id: event.target.value,
                                        ala_id: '',
                                    })
                                }
                                className="h-11 w-full rounded-2xl border border-gray-200 bg-white px-4 text-sm shadow-sm focus:border-amber-300 focus:ring-2 focus:ring-amber-100 focus:outline-none disabled:bg-gray-50"
                            >
                                <option value="">Todos</option>
                                {opcoes.hospitais.map((hospital) => (
                                    <option key={hospital.id} value={hospital.id}>
                                        {hospital.nome}
                                    </option>
                                ))}
                            </select>
                        </Campo>
                        <Campo label="Ala / unidade">
                            <select
                                value={form.ala_id}
                                disabled={!form.hospital_id}
                                onChange={(event) => aplicarFiltros({ ala_id: event.target.value })}
                                className="h-11 w-full rounded-2xl border border-gray-200 bg-white px-4 text-sm shadow-sm focus:border-amber-300 focus:ring-2 focus:ring-amber-100 focus:outline-none disabled:bg-gray-50"
                            >
                                <option value="">Todas</option>
                                {opcoes.alas.map((ala) => (
                                    <option key={ala.id} value={ala.id}>
                                        {ala.nome}
                                    </option>
                                ))}
                            </select>
                        </Campo>
                    </div>
                    <div className="flex justify-end">
                        <button
                            type="button"
                            onClick={limparFiltros}
                            className="rounded-full px-4 py-2 text-sm font-medium text-amber-800 transition hover:bg-amber-50"
                        >
                            Limpar filtros
                        </button>
                    </div>
                </section>

                <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    <Indicador icon={CalendarRange} titulo="Visitas" valor={formatarNumero(indicadores.total_visitas)} />
                    <Indicador icon={Building2} titulo="Hospitais visitados" valor={formatarNumero(indicadores.hospitais_visitados)} />
                    <Indicador icon={UsersRound} titulo="Participações confirmadas" valor={formatarNumero(indicadores.total_participacoes)} />
                    <Indicador icon={BarChart3} titulo="Média por visita" valor={formatarNumero(indicadores.media_participantes, 1)} />
                    <Indicador icon={Sparkles} titulo="Impacto estimado" valor={formatarNumero(indicadores.impacto_estimado)} detalhe={`${indicadores.visitas_sem_impacto} sem impacto informado`} />
                </section>

                <section className="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                    <h2 className="font-semibold text-amber-950">Evolução mensal</h2>
                    <p className="mt-1 text-sm text-amber-900/50">Quantidade de visitas não canceladas por mês.</p>
                    <div className="mt-6 flex min-h-48 items-end gap-3 overflow-x-auto pb-2">
                        {evolucao.map((item) => (
                            <div key={item.mes} className="flex min-w-14 flex-1 flex-col items-center gap-2">
                                <span className="text-xs font-semibold text-amber-900">{item.total}</span>
                                <div className="flex h-32 w-full items-end rounded-lg bg-amber-50 p-1">
                                    <div
                                        className="w-full rounded-md bg-gradient-to-t from-amber-600 to-yellow-400 transition-all"
                                        style={{ height: `${Math.max((item.total / maiorEvolucao) * 100, item.total ? 6 : 0)}%` }}
                                        aria-label={`${item.rotulo}: ${item.total} visitas`}
                                    />
                                </div>
                                <span className="text-center text-[11px] text-gray-500 capitalize">{item.rotulo}</span>
                            </div>
                        ))}
                    </div>
                </section>

                <section className="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
                    <div className="border-b border-amber-100 p-5">
                        <h2 className="font-semibold text-amber-950">Hospitais no período</h2>
                        <p className="mt-1 text-sm text-amber-900/50">Selecione um hospital para abrir o detalhamento.</p>
                    </div>
                    {hospitais.length === 0 ? (
                        <EstadoVazio />
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[760px] text-left text-sm">
                                <thead className="bg-amber-50/70 text-xs uppercase tracking-wide text-amber-900/60">
                                    <tr>
                                        <th className="px-5 py-3">Hospital</th><th className="px-4 py-3">Visitas</th><th className="px-4 py-3">Participações</th><th className="px-4 py-3">Média</th><th className="px-4 py-3">Impacto estimado</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-amber-50">
                                    {hospitais.map((hospital) => (
                                        <tr key={hospital.id} className="transition hover:bg-amber-50/40">
                                            <td className="px-5 py-4">
                                                <Link
                                                    href={visitasPorHospital({ query: { ...filtros, hospital_id: hospital.id, ala_id: undefined } })}
                                                    className="font-semibold text-amber-800 hover:text-amber-950 hover:underline"
                                                >
                                                    {hospital.nome}
                                                </Link>
                                                <span className="mt-0.5 block text-xs text-gray-500">{hospital.cidade}{hospital.possui_alas ? ' · possui alas' : ''}</span>
                                            </td>
                                            <td className="px-4 py-4 font-semibold">{hospital.total_visitas}</td>
                                            <td className="px-4 py-4">{hospital.total_participacoes}</td>
                                            <td className="px-4 py-4">{formatarNumero(hospital.media_participantes, 1)}</td>
                                            <td className="px-4 py-4">{formatarNumero(hospital.impacto_estimado)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </section>

                {detalhes && (
                    <section className="grid gap-6 xl:grid-cols-[0.8fr_2fr]">
                        <div className="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                            <h2 className="font-semibold text-amber-950">Distribuição por ala</h2>
                            <div className="mt-4 space-y-3">
                                {!detalhes.possui_alas && (
                                    <p className="rounded-xl bg-amber-50/70 px-4 py-3 text-sm text-amber-900/60">
                                        Este hospital não possui alas cadastradas.
                                    </p>
                                )}
                                {detalhes.alas.map((ala) => (
                                    <div key={ala.id ?? 'sem-ala'} className="flex items-center justify-between rounded-xl bg-amber-50/70 px-4 py-3">
                                        <span className="text-sm font-medium text-amber-950">{ala.nome}</span>
                                        <span className="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-amber-800">{ala.total_visitas}</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                        <div className="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
                            <div className="border-b border-amber-100 p-5"><h2 className="font-semibold text-amber-950">Visitas consideradas</h2></div>
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[680px] text-left text-sm">
                                    <thead className="bg-amber-50/70 text-xs uppercase text-amber-900/60"><tr><th className="px-5 py-3">Data</th><th className="px-4 py-3">Ala</th><th className="px-4 py-3">Status</th><th className="px-4 py-3">Participantes</th><th className="px-4 py-3">Impacto</th></tr></thead>
                                    <tbody className="divide-y divide-amber-50">
                                        {detalhes.visitas.data.map((visita) => (
                                            <tr key={visita.id}><td className="px-5 py-4 font-medium">{formatarData(visita.inicio_em)}</td><td className="px-4 py-4">{visita.ala}</td><td className="px-4 py-4 capitalize">{visita.status.replaceAll('_', ' ')}</td><td className="px-4 py-4">{visita.participantes}</td><td className="px-4 py-4">{visita.impacto_estimado === null ? <span className="text-gray-400">Não informado</span> : formatarNumero(visita.impacto_estimado)}</td></tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                            <div className="flex items-center justify-between border-t border-amber-100 p-4 text-sm text-gray-500">
                                <span>Página {detalhes.visitas.current_page} de {detalhes.visitas.last_page}</span>
                                <div className="flex gap-2">
                                    {detalhes.visitas.prev_page_url && <Link href={detalhes.visitas.prev_page_url} preserveScroll className="rounded-full border border-amber-200 px-3 py-1.5 text-amber-800 hover:bg-amber-50">Anterior</Link>}
                                    {detalhes.visitas.next_page_url && <Link href={detalhes.visitas.next_page_url} preserveScroll className="rounded-full border border-amber-200 px-3 py-1.5 text-amber-800 hover:bg-amber-50">Próxima</Link>}
                                </div>
                            </div>
                        </div>
                    </section>
                )}
            </div>
        </PainelLayout>
    );
}

function Campo({ label, children }: { label: string; children: React.ReactNode }) {
    return <label className="space-y-1.5 text-sm font-medium text-amber-950"><span>{label}</span>{children}</label>;
}

function Indicador({ icon: Icone, titulo, valor, detalhe }: { icon: typeof CalendarRange; titulo: string; valor: string; detalhe?: string }) {
    return <article className="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm"><div className="flex items-center justify-between"><span className="text-sm font-medium text-amber-900/60">{titulo}</span><Icone className="size-5 text-amber-600/70" /></div><p className="mt-3 text-3xl font-bold tracking-tight text-amber-950">{valor}</p>{detalhe && <p className="mt-1 text-xs text-gray-400">{detalhe}</p>}</article>;
}

function EstadoVazio() {
    return <div className="px-5 py-12 text-center"><Building2 className="mx-auto size-9 text-amber-300" /><p className="mt-3 font-medium text-amber-950">Nenhuma visita encontrada</p><p className="mt-1 text-sm text-gray-500">Ajuste o período ou os filtros para consultar outros registros.</p></div>;
}
