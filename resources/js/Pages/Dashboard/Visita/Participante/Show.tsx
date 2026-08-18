import PainelLayout from '@/layouts/PainelLayout';
import { visitasPorParticipante } from '@/routes/dashboards';
import type {
    AcompanhamentoParticipante,
    FiltrosParticipante,
} from '@/types/dashboard-participante';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    CalendarCheck,
    Clock3,
    FileWarning,
    Info,
    MapPin,
} from 'lucide-react';

interface Participacao {
    visita_id: number;
    data: string;
    hospital: string;
    outra_cidade: boolean;
    motivo: string;
    valida: boolean;
}
interface Presenca {
    evento_id: number;
    tipo: string;
    titulo: string;
    cidade_id: number;
    data_inicio: string;
}
interface Props {
    participante: AcompanhamentoParticipante;
    participacoes: Participacao[];
    presencas: Presenca[];
    filtros: FiltrosParticipante;
}

const data = (valor: string) =>
    new Date(valor).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
const mes = (valor: string) =>
    new Date(`${valor}-02`).toLocaleDateString('pt-BR', {
        month: 'long',
        year: 'numeric',
    });

export default function Show({
    participante,
    participacoes,
    presencas,
    filtros,
}: Props) {
    const filtrosAtivos = Object.fromEntries(
        Object.entries(filtros).filter(
            ([, valor]) =>
                valor !== null && valor !== undefined && valor !== '',
        ),
    );
    return (
        <PainelLayout>
            <Head title={`Histórico de ${participante.nome}`} />
            <div className="mx-auto max-w-6xl space-y-6 px-5 py-8 sm:px-6 lg:px-8">
                <header>
                    <Link
                        href={visitasPorParticipante({ query: filtrosAtivos })}
                        className="inline-flex items-center gap-2 text-sm font-medium text-amber-700 hover:underline"
                    >
                        <ArrowLeft className="size-4" /> Voltar ao
                        acompanhamento
                    </Link>
                    <div className="mt-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-sm text-amber-700">
                                Histórico explicável
                            </p>
                            <h1 className="text-2xl font-semibold text-amber-950 sm:text-3xl">
                                {participante.nome}
                            </h1>
                            <p className="mt-1 text-sm text-gray-500">
                                {participante.cidade} ·{' '}
                                {participante.cargos.join(', ')}
                            </p>
                        </div>
                        <span className="w-fit rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 ring-1 ring-amber-200">
                            Apoio à decisão
                        </span>
                    </div>
                </header>

                <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Resumo
                        icon={CalendarCheck}
                        titulo="Visitas válidas"
                        valor={participante.visitas_validas}
                    />
                    <Resumo
                        icon={FileWarning}
                        titulo="Relatórios pendentes"
                        valor={participante.relatorios_pendentes}
                    />
                    <Resumo
                        icon={Clock3}
                        titulo="Fora do prazo"
                        valor={participante.relatorios_fora_prazo}
                    />
                    <Resumo
                        icon={MapPin}
                        titulo="Dias sem atividade"
                        valor={participante.dias_sem_atividade ?? '—'}
                    />
                </section>

                {participante.compensacoes.length > 0 && (
                    <section className="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
                        <div className="border-b border-amber-100 p-5">
                            <h2 className="font-semibold text-amber-950">
                                Metas, saldos e compensações
                            </h2>
                            <p className="mt-1 text-sm text-gray-500">
                                Créditos e débitos valem somente para o mês
                                seguinte.
                            </p>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[850px] text-sm">
                                <thead className="bg-amber-50/70 text-left text-xs text-amber-900/60 uppercase">
                                    <tr>
                                        <th className="px-5 py-3">Mês</th>
                                        <th>Meta</th>
                                        <th>Visitas</th>
                                        <th>Saldo</th>
                                        <th>Crédito usado</th>
                                        <th>Débito compensado</th>
                                        <th>Transferido</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-amber-50">
                                    {participante.compensacoes.map((item) => (
                                        <tr key={item.mes}>
                                            <td className="px-5 py-4 font-medium capitalize">
                                                {mes(item.mes)}
                                            </td>
                                            <td>{item.meta}</td>
                                            <td>{item.visitas}</td>
                                            <td>
                                                {item.saldo > 0
                                                    ? `+${item.saldo}`
                                                    : item.saldo}
                                            </td>
                                            <td>
                                                {
                                                    item.credito_anterior_utilizado
                                                }
                                            </td>
                                            <td>
                                                {
                                                    item.debito_anterior_compensado
                                                }
                                            </td>
                                            <td>
                                                {item.credito_transferido > 0
                                                    ? `Crédito ${item.credito_transferido}`
                                                    : item.debito_transferido >
                                                        0
                                                      ? `Débito ${item.debito_transferido}`
                                                      : '—'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </section>
                )}

                <section className="grid gap-6 lg:grid-cols-2">
                    <Bloco titulo="Visitas consideradas e excluídas">
                        {participacoes.length === 0 ? (
                            <Vazio texto="Nenhuma participação no período." />
                        ) : (
                            participacoes.map((item) => (
                                <div
                                    key={item.visita_id}
                                    className="flex gap-3 rounded-xl border border-gray-100 p-3"
                                >
                                    <span
                                        className={`mt-1 size-2 shrink-0 rounded-full ${item.valida ? 'bg-emerald-500' : 'bg-amber-500'}`}
                                    />
                                    <div>
                                        <p className="text-sm font-medium text-gray-900">
                                            {item.hospital}{' '}
                                            {item.outra_cidade && (
                                                <span className="text-xs text-amber-700">
                                                    · outra cidade
                                                </span>
                                            )}
                                        </p>
                                        <p className="text-xs text-gray-500">
                                            {data(item.data)} · {item.motivo}
                                        </p>
                                    </div>
                                </div>
                            ))
                        )}
                    </Bloco>
                    <Bloco titulo="Reuniões e oficinas com presença">
                        {presencas.length === 0 ? (
                            <Vazio texto="Nenhuma presença registrada no período." />
                        ) : (
                            presencas.map((item) => (
                                <div
                                    key={item.evento_id}
                                    className="rounded-xl border border-gray-100 p-3"
                                >
                                    <p className="text-sm font-medium text-gray-900">
                                        {item.titulo}
                                    </p>
                                    <p className="text-xs text-gray-500 capitalize">
                                        {item.tipo} · {data(item.data_inicio)}
                                    </p>
                                </div>
                            ))
                        )}
                    </Bloco>
                </section>

                <section className="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                    <div className="flex gap-3">
                        <Info className="mt-0.5 size-5 shrink-0 text-gray-500" />
                        <div>
                            <h2 className="font-semibold text-gray-800">
                                Afastamentos, justificativas e horas
                                administrativas
                            </h2>
                            <p className="mt-1 text-sm leading-relaxed text-gray-600">
                                Dados ainda não disponíveis no sistema. Esta
                                área está reservada para a futura modelagem e
                                não gera sinalização negativa.
                            </p>
                        </div>
                    </div>
                </section>
                <p className="text-xs leading-relaxed text-gray-500">
                    Este histórico explica os dados disponíveis e não substitui
                    a análise da coordenação. Nenhuma informação desta tela
                    altera automaticamente o cadastro.
                </p>
            </div>
        </PainelLayout>
    );
}

function Resumo({
    icon: Icone,
    titulo,
    valor,
}: {
    icon: typeof CalendarCheck;
    titulo: string;
    valor: number | string;
}) {
    return (
        <article className="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
            <div className="flex justify-between">
                <span className="text-sm text-amber-900/60">{titulo}</span>
                <Icone className="size-5 text-amber-600" />
            </div>
            <p className="mt-3 text-3xl font-bold text-amber-950">{valor}</p>
        </article>
    );
}
function Bloco({
    titulo,
    children,
}: {
    titulo: string;
    children: React.ReactNode;
}) {
    return (
        <section className="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
            <h2 className="font-semibold text-amber-950">{titulo}</h2>
            <div className="mt-4 space-y-3">{children}</div>
        </section>
    );
}
function Vazio({ texto }: { texto: string }) {
    return (
        <p className="rounded-xl bg-gray-50 p-4 text-sm text-gray-500">
            {texto}
        </p>
    );
}
