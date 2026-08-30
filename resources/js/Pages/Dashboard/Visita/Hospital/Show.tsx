import PainelLayout from '@/layouts/PainelLayout';
import { visitasPorHospital } from '@/routes/dashboards';
import type {
    AcompanhamentoSemanalHospital,
    AlaDashboardHospital,
    FiltrosDashboardHospital,
    IndicadoresDashboardHospital,
    PaginacaoDashboard,
    ResumoDashboardHospital,
    SituacaoMetaHospital,
    VisitaDashboardHospital,
} from '@/types/dashboard';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Building2,
    CalendarCheck,
    ClipboardList,
    MapPin,
    Sparkles,
    UsersRound,
} from 'lucide-react';

interface Props {
    hospital: {
        id: number;
        nome: string;
        cidade: string;
        ativo: boolean;
    };
    resumo: ResumoDashboardHospital;
    indicadores: IndicadoresDashboardHospital;
    detalhes: {
        possui_alas: boolean;
        alas: AlaDashboardHospital[];
        visitas: PaginacaoDashboard<VisitaDashboardHospital>;
    };
    metas_semanais: AcompanhamentoSemanalHospital[];
    filtros: Omit<FiltrosDashboardHospital, 'hospital_id' | 'ala_id'>;
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

const formatarMes = (valor: string) => {
    const [ano, mes] = valor.split('-').map(Number);

    return new Date(ano, mes - 1).toLocaleDateString('pt-BR', {
        month: 'long',
        year: 'numeric',
    });
};

export default function Show({
    hospital,
    resumo,
    indicadores,
    detalhes,
    metas_semanais,
    filtros,
}: Props) {
    return (
        <PainelLayout>
            <Head title={`Acompanhamento · ${hospital.nome}`} />

            <div className="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                <header className="space-y-4">
                    <Link
                        href={visitasPorHospital({ query: filtros })}
                        className="inline-flex items-center gap-2 text-sm font-semibold text-amber-800 transition hover:text-amber-950"
                    >
                        <ArrowLeft className="size-4" /> Voltar aos hospitais
                    </Link>
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p className="text-sm font-semibold text-amber-700">
                                Acompanhamento do hospital
                            </p>
                            <h1 className="mt-1 text-2xl font-semibold tracking-tight text-amber-950 sm:text-3xl">
                                {hospital.nome}
                            </h1>
                            <p className="mt-2 flex items-center gap-1.5 text-sm text-amber-900/60">
                                <MapPin className="size-4" /> {hospital.cidade}
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            {!hospital.ativo && (
                                <span className="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600">
                                    Hospital inativo
                                </span>
                            )}
                            <SituacaoMeta situacao={resumo.situacao_meta} />
                        </div>
                    </div>
                </header>

                <section className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <Indicador
                        icon={CalendarCheck}
                        titulo="Visitas realizadas"
                        valor={formatarNumero(indicadores.total_visitas)}
                    />
                    <Indicador
                        icon={ClipboardList}
                        titulo="Meta no período"
                        valor={
                            resumo.percentual_meta === null
                                ? 'Não definida'
                                : `${resumo.realizadas_com_meta}/${resumo.meta_total}`
                        }
                        detalhe={
                            resumo.percentual_meta !== null
                                ? `${resumo.percentual_meta}% de cumprimento`
                                : undefined
                        }
                    />
                    <Indicador
                        icon={UsersRound}
                        titulo="Participações"
                        valor={formatarNumero(
                            indicadores.total_participacoes,
                        )}
                        detalhe={`${formatarNumero(indicadores.media_participantes, 1)} por visita`}
                    />
                    <Indicador
                        icon={Sparkles}
                        titulo="Impacto estimado"
                        valor={formatarNumero(indicadores.impacto_estimado)}
                        detalhe={`${indicadores.visitas_sem_relatorio} visita(s) sem relatório`}
                    />
                </section>

                <section className="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
                    <TituloSecao
                        titulo="Acompanhamento mensal"
                        descricao="A situação considera somente meses com uma meta configurada."
                    />
                    <div className="grid gap-3 p-4 sm:grid-cols-2 lg:hidden">
                        {resumo.meses.map((mes) => (
                            <article
                                key={mes.mes}
                                className="rounded-xl border border-amber-100 p-4"
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <p className="font-semibold text-amber-950 capitalize">
                                        {formatarMes(mes.mes)}
                                    </p>
                                    <SituacaoMeta situacao={mes.situacao} />
                                </div>
                                <dl className="mt-4 grid grid-cols-3 gap-2 text-center">
                                    <Metrica label="Meta" valor={mes.meta ?? '—'} />
                                    <Metrica
                                        label="Realizadas"
                                        valor={mes.realizadas}
                                    />
                                    <Metrica
                                        label="Cumprimento"
                                        valor={
                                            mes.percentual === null
                                                ? '—'
                                                : `${mes.percentual}%`
                                        }
                                    />
                                </dl>
                            </article>
                        ))}
                    </div>
                    <div className="hidden overflow-x-auto lg:block">
                        <table className="w-full min-w-[760px] text-left text-sm">
                            <thead className="bg-amber-50/70 text-xs tracking-wide text-amber-900/60 uppercase">
                                <tr>
                                    <th className="px-5 py-3">Mês</th>
                                    <th className="px-4 py-3">Meta</th>
                                    <th className="px-4 py-3">Realizadas</th>
                                    <th className="px-4 py-3">Diferença</th>
                                    <th className="px-4 py-3">Cumprimento</th>
                                    <th className="px-5 py-3">Situação</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-amber-50">
                                {resumo.meses.map((mes) => (
                                    <tr key={mes.mes}>
                                        <td className="px-5 py-4 font-semibold text-amber-950 capitalize">
                                            {formatarMes(mes.mes)}
                                        </td>
                                        <td className="px-4 py-4">
                                            {mes.meta ?? '—'}
                                        </td>
                                        <td className="px-4 py-4">
                                            {mes.realizadas}
                                        </td>
                                        <td className="px-4 py-4">
                                            {mes.diferenca === null
                                                ? '—'
                                                : mes.diferenca > 0
                                                  ? `+${mes.diferenca}`
                                                  : mes.diferenca}
                                        </td>
                                        <td className="px-4 py-4">
                                            {mes.percentual === null
                                                ? '—'
                                                : `${mes.percentual}%`}
                                        </td>
                                        <td className="px-5 py-4">
                                            <SituacaoMeta
                                                situacao={mes.situacao}
                                            />
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </section>

                <section className="grid gap-6 xl:grid-cols-[0.8fr_2fr]">
                    <div className="rounded-2xl border border-amber-100 bg-white shadow-sm">
                        <TituloSecao titulo="Distribuição por ala" />
                        <div className="space-y-3 p-4">
                            {!detalhes.possui_alas && (
                                <p className="rounded-xl bg-amber-50/70 px-4 py-3 text-sm text-amber-900/60">
                                    Este hospital não possui alas cadastradas.
                                </p>
                            )}
                            {detalhes.alas.map((ala) => (
                                <div
                                    key={ala.id ?? 'sem-ala'}
                                    className="flex items-center justify-between rounded-xl bg-amber-50/70 px-4 py-3"
                                >
                                    <span className="text-sm font-medium text-amber-950">
                                        {ala.nome}
                                    </span>
                                    <span className="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-amber-800">
                                        {ala.total_visitas}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
                        <TituloSecao
                            titulo="Metas semanais"
                            descricao="Detalhamento operacional por semana e ala, quando configurado."
                        />
                        {metas_semanais.length === 0 ? (
                            <p className="p-5 text-sm text-gray-500">
                                Nenhuma meta semanal foi definida neste período.
                            </p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[680px] text-left text-sm">
                                    <thead className="bg-amber-50/70 text-xs text-amber-900/60 uppercase">
                                        <tr>
                                            <th className="px-5 py-3">Período</th>
                                            <th className="px-4 py-3">Ala</th>
                                            <th className="px-4 py-3">Meta</th>
                                            <th className="px-4 py-3">
                                                Realizadas
                                            </th>
                                            <th className="px-5 py-3">
                                                Situação
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-amber-50">
                                        {metas_semanais.map((meta) => (
                                            <tr
                                                key={`${meta.mes}-${meta.semana}-${meta.ala ?? 'hospital'}`}
                                            >
                                                <td className="px-5 py-4 font-medium capitalize">
                                                    {formatarMes(meta.mes)} · dias{' '}
                                                    {meta.periodo}
                                                </td>
                                                <td className="px-4 py-4">
                                                    {meta.ala ?? 'Hospital'}
                                                </td>
                                                <td className="px-4 py-4">
                                                    {meta.meta}
                                                </td>
                                                <td className="px-4 py-4">
                                                    {meta.realizadas}
                                                </td>
                                                <td className="px-5 py-4">
                                                    <SituacaoMeta
                                                        situacao={meta.situacao}
                                                    />
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                </section>

                <section className="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
                    <TituloSecao
                        titulo="Visitas consideradas"
                        descricao="Visitas realizadas no período, inclusive quando o relatório ainda não foi informado."
                    />
                    {detalhes.visitas.data.length === 0 ? (
                        <div className="px-5 py-12 text-center">
                            <Building2 className="mx-auto size-9 text-amber-300" />
                            <p className="mt-3 font-medium text-amber-950">
                                Nenhuma visita realizada no período
                            </p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[760px] text-left text-sm">
                                <thead className="bg-amber-50/70 text-xs text-amber-900/60 uppercase">
                                    <tr>
                                        <th className="px-5 py-3">Data</th>
                                        <th className="px-4 py-3">Ala</th>
                                        <th className="px-4 py-3">Status</th>
                                        <th className="px-4 py-3">
                                            Participantes
                                        </th>
                                        <th className="px-4 py-3">Relatório</th>
                                        <th className="px-5 py-3">Impacto</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-amber-50">
                                    {detalhes.visitas.data.map((visita) => (
                                        <tr key={visita.id}>
                                            <td className="px-5 py-4 font-medium">
                                                {formatarData(visita.inicio_em)}
                                            </td>
                                            <td className="px-4 py-4">
                                                {visita.ala}
                                            </td>
                                            <td className="px-4 py-4 capitalize">
                                                {visita.status.replaceAll(
                                                    '_',
                                                    ' ',
                                                )}
                                            </td>
                                            <td className="px-4 py-4">
                                                {visita.participantes}
                                            </td>
                                            <td className="px-4 py-4">
                                                {visita.possui_relatorio ? (
                                                    <span className="font-medium text-emerald-700">
                                                        Informado
                                                    </span>
                                                ) : (
                                                    <span className="font-medium text-amber-700">
                                                        Não informado
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-5 py-4">
                                                {visita.impacto_estimado ===
                                                null ? (
                                                    <span className="text-gray-400">
                                                        Não informado
                                                    </span>
                                                ) : (
                                                    formatarNumero(
                                                        visita.impacto_estimado,
                                                    )
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                    <Paginacao paginacao={detalhes.visitas} />
                </section>
            </div>
        </PainelLayout>
    );
}

const situacoesMeta: Record<
    SituacaoMetaHospital,
    { texto: string; classe: string }
> = {
    dentro_meta: {
        texto: 'Dentro da meta',
        classe: 'bg-emerald-50 text-emerald-700',
    },
    atencao: {
        texto: 'Atenção',
        classe: 'bg-red-50 text-red-700',
    },
    em_andamento: {
        texto: 'Em andamento',
        classe: 'bg-blue-50 text-blue-700',
    },
    sem_meta_definida: {
        texto: 'Sem meta definida',
        classe: 'bg-gray-100 text-gray-600',
    },
    futuro: {
        texto: 'Período futuro',
        classe: 'bg-violet-50 text-violet-700',
    },
};

function SituacaoMeta({ situacao }: { situacao: SituacaoMetaHospital }) {
    const configuracao = situacoesMeta[situacao];

    return (
        <span
            className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${configuracao.classe}`}
        >
            {configuracao.texto}
        </span>
    );
}

function Indicador({
    icon: Icone,
    titulo,
    valor,
    detalhe,
}: {
    icon: typeof CalendarCheck;
    titulo: string;
    valor: string;
    detalhe?: string;
}) {
    return (
        <article className="min-w-0 rounded-2xl border border-amber-100 bg-white p-4 shadow-sm sm:p-5">
            <div className="flex items-start justify-between gap-2">
                <span className="text-xs font-medium text-amber-900/60 sm:text-sm">
                    {titulo}
                </span>
                <Icone className="size-4 shrink-0 text-amber-600/70 sm:size-5" />
            </div>
            <p className="mt-3 break-words text-xl font-bold tracking-tight text-amber-950 sm:text-2xl">
                {valor}
            </p>
            {detalhe && <p className="mt-1 text-xs text-gray-400">{detalhe}</p>}
        </article>
    );
}

function TituloSecao({
    titulo,
    descricao,
}: {
    titulo: string;
    descricao?: string;
}) {
    return (
        <div className="border-b border-amber-100 p-4 sm:p-5">
            <h2 className="font-semibold text-amber-950">{titulo}</h2>
            {descricao && (
                <p className="mt-1 text-sm text-amber-900/50">{descricao}</p>
            )}
        </div>
    );
}

function Metrica({ label, valor }: { label: string; valor: string | number }) {
    return (
        <div>
            <dt className="text-[11px] text-gray-500">{label}</dt>
            <dd className="mt-1 font-semibold text-amber-950">{valor}</dd>
        </div>
    );
}

function Paginacao({
    paginacao,
}: {
    paginacao: PaginacaoDashboard<VisitaDashboardHospital>;
}) {
    if (paginacao.last_page <= 1) {
        return null;
    }

    return (
        <div className="flex flex-col gap-3 border-t border-amber-100 p-4 text-sm text-gray-500 sm:flex-row sm:items-center sm:justify-between">
            <span>
                Página {paginacao.current_page} de {paginacao.last_page}
            </span>
            <div className="flex gap-2">
                {paginacao.prev_page_url && (
                    <Link
                        href={paginacao.prev_page_url}
                        preserveScroll
                        className="flex-1 rounded-full border border-amber-200 px-4 py-2 text-center font-medium text-amber-800 hover:bg-amber-50 sm:flex-none"
                    >
                        Anterior
                    </Link>
                )}
                {paginacao.next_page_url && (
                    <Link
                        href={paginacao.next_page_url}
                        preserveScroll
                        className="flex-1 rounded-full border border-amber-200 px-4 py-2 text-center font-medium text-amber-800 hover:bg-amber-50 sm:flex-none"
                    >
                        Próxima
                    </Link>
                )}
            </div>
        </div>
    );
}
