import PainelLayout from '@/layouts/PainelLayout';
import type {
    AtividadeVisaoGeral,
    AvisoVisaoGeral,
    PendenciaVisaoGeral,
} from '@/types/dashboard-home';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowRight,
    Bell,
    CalendarCheck,
    CalendarDays,
    CheckCircle2,
    CircleAlert,
    FileWarning,
    MapPin,
    Target,
    UsersRound,
} from 'lucide-react';

interface Props {
    contexto: {
        nome: string;
        cidade: string | null;
        possui_vinculo: boolean;
        mensagem: string;
    };
    proximas_atividades: AtividadeVisaoGeral[];
    pendencias: PendenciaVisaoGeral[];
    avisos: AvisoVisaoGeral[];
    resumo: {
        visitas_validas_mes: number | null;
        oficinas_semestre: number | null;
        reunioes_semestre: number | null;
        meta: {
            situacao: 'isento' | 'aplicavel' | 'dados_insuficientes';
            atual: number | null;
            objetivo: number | null;
        };
    };
}

const formatarData = (valor: string) =>
    new Date(valor).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });

const formatarHora = (valor: string) =>
    new Date(valor).toLocaleTimeString('pt-BR', {
        hour: '2-digit',
        minute: '2-digit',
    });

const rotuloCategoria = (categoria: AtividadeVisaoGeral['categoria']) =>
    ({ visita: 'Visita', oficina: 'Oficina', reuniao: 'Reunião' })[categoria];

export default function Dashboard({
    contexto,
    proximas_atividades,
    pendencias,
    avisos,
    resumo,
}: Props) {
    const primeiroNome = contexto.nome.trim().split(' ')[0] || contexto.nome;
    const meta =
        resumo.meta.situacao === 'isento'
            ? 'Isento'
            : resumo.meta.situacao === 'aplicavel'
              ? `${resumo.meta.atual} de ${resumo.meta.objetivo}`
              : 'Indisponível';

    return (
        <PainelLayout>
            <Head title="Visão Geral" />
            <div className="mx-auto max-w-7xl space-y-6 px-5 py-8 sm:px-6 lg:px-8">
                <header>
                    <p className="text-sm font-semibold text-amber-700">
                        Visão Geral
                    </p>
                    <h1 className="mt-1 text-2xl font-semibold text-amber-950 sm:text-3xl">
                        Olá, {primeiroNome}
                    </h1>
                    <p className="mt-2 max-w-3xl text-sm leading-relaxed text-amber-900/60">
                        {contexto.mensagem}
                    </p>
                    {contexto.cidade && (
                        <p className="mt-2 flex items-center gap-1.5 text-xs font-medium text-amber-900/50">
                            <MapPin className="size-3.5" aria-hidden />
                            {contexto.cidade}
                        </p>
                    )}
                </header>

                {!contexto.possui_vinculo && (
                    <section className="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                        <h2 className="font-semibold text-amber-950">
                            Conta administrativa sem voluntário vinculado
                        </h2>
                        <p className="mt-1 text-sm leading-relaxed text-amber-900/70">
                            As informações pessoais permanecem vazias. Nenhum
                            dado de outro voluntário é usado para preencher esta
                            página.
                        </p>
                    </section>
                )}

                <section className="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
                    <Bloco
                        titulo="Próximas atividades"
                        descricao="Somente visitas confirmadas e inscrições ativas."
                    >
                        {proximas_atividades.length === 0 ? (
                            <Vazio texto="Você não possui atividades futuras confirmadas.">
                                <Link
                                    href="/visitas"
                                    className="mt-2 inline-flex items-center gap-1 font-semibold text-amber-700 hover:underline"
                                >
                                    Ver visitas disponíveis{' '}
                                    <ArrowRight
                                        className="size-4"
                                        aria-hidden
                                    />
                                </Link>
                            </Vazio>
                        ) : (
                            <div className="grid gap-3 md:grid-cols-2">
                                {proximas_atividades.map((atividade) => (
                                    <article
                                        key={`${atividade.categoria}-${atividade.id}`}
                                        className="flex items-center justify-between gap-3 rounded-xl border border-amber-100 p-4"
                                    >
                                        <div className="min-w-0">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <span className="text-xs font-semibold text-amber-700 uppercase">
                                                    {rotuloCategoria(
                                                        atividade.categoria,
                                                    )}
                                                </span>
                                                <span className="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">
                                                    {atividade.situacao}
                                                </span>
                                            </div>
                                            <h3 className="mt-1 truncate font-semibold text-amber-950">
                                                {atividade.titulo}
                                            </h3>
                                            <p className="mt-1 truncate text-xs text-gray-500">
                                                {formatarData(
                                                    atividade.inicio_em,
                                                )}{' '}
                                                às{' '}
                                                {formatarHora(
                                                    atividade.inicio_em,
                                                )}{' '}
                                                · {atividade.local}
                                            </p>
                                            {atividade.cidade && (
                                                <p className="truncate text-xs text-gray-500">
                                                    {atividade.cidade}
                                                </p>
                                            )}
                                        </div>
                                        <Link
                                            href={atividade.detalhes_url}
                                            aria-label={`Ver ${atividade.titulo}`}
                                            className="flex size-9 shrink-0 items-center justify-center rounded-full border border-amber-200 text-amber-700 hover:bg-amber-50"
                                        >
                                            <ArrowRight
                                                className="size-4"
                                                aria-hidden
                                            />
                                        </Link>
                                    </article>
                                ))}
                            </div>
                        )}
                    </Bloco>

                    <Bloco
                        titulo="Pendências"
                        descricao="Ações que precisam da sua atenção."
                    >
                        {pendencias.length === 0 ? (
                            <p className="flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                                <CheckCircle2
                                    className="size-4 shrink-0"
                                    aria-hidden
                                />
                                Está tudo em dia por aqui
                            </p>
                        ) : (
                            <div className="space-y-3">
                                {pendencias.map((pendencia) => (
                                    <article
                                        key={pendencia.id}
                                        className={`rounded-xl border p-4 ${
                                            pendencia.estado_prazo ===
                                            'atrasado'
                                                ? 'border-red-100 bg-red-50/60'
                                                : pendencia.estado_prazo ===
                                                    'prazo_proximo'
                                                  ? 'border-amber-200 bg-amber-50/70'
                                                  : 'border-amber-100'
                                        }`}
                                    >
                                        <div className="flex items-start gap-2">
                                            <FileWarning
                                                className={`mt-0.5 size-4 shrink-0 ${pendencia.estado_prazo === 'atrasado' ? 'text-red-600' : 'text-amber-600'}`}
                                                aria-hidden
                                            />
                                            <div>
                                                <h3 className="text-sm font-semibold text-amber-950">
                                                    {pendencia.titulo}
                                                </h3>
                                                <p className="mt-1 text-xs text-gray-500">
                                                    {pendencia.descricao}
                                                </p>
                                                <p
                                                    className={`mt-2 text-xs font-medium ${pendencia.estado_prazo === 'atrasado' ? 'text-red-700' : 'text-amber-800'}`}
                                                >
                                                    {pendencia.estado_prazo ===
                                                    'atrasado'
                                                        ? 'Prazo de 48 horas encerrado'
                                                        : `Prazo: ${formatarData(pendencia.prazo_em)} às ${formatarHora(pendencia.prazo_em)}`}
                                                </p>
                                                <Link
                                                    href={pendencia.acao.url}
                                                    className="mt-3 inline-flex text-sm font-semibold text-amber-700 hover:underline"
                                                >
                                                    {pendencia.acao.titulo}
                                                </Link>
                                            </div>
                                        </div>
                                    </article>
                                ))}
                            </div>
                        )}
                    </Bloco>
                </section>

                <section aria-labelledby="resumo-pessoal">
                    <div className="mb-3 flex flex-wrap items-end justify-between gap-2">
                        <div>
                            <h2
                                id="resumo-pessoal"
                                className="font-semibold text-amber-950"
                            >
                                Resumo pessoal
                            </h2>
                            <p className="mt-1 text-sm text-gray-500">
                                Um recorte rápido da sua participação atual.
                            </p>
                        </div>
                        <Link
                            href="/dashboards/meu"
                            className="inline-flex items-center gap-1 text-sm font-semibold text-amber-700 hover:underline"
                        >
                            Ver meu dashboard{' '}
                            <ArrowRight className="size-4" aria-hidden />
                        </Link>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <Indicador
                            icon={CalendarCheck}
                            titulo="Visitas válidas"
                            valor={resumo.visitas_validas_mes ?? '—'}
                            detalhe="No mês atual"
                        />
                        <Indicador
                            icon={CalendarDays}
                            titulo="Oficinas frequentadas"
                            valor={resumo.oficinas_semestre ?? '—'}
                            detalhe="No semestre atual"
                        />
                        <Indicador
                            icon={UsersRound}
                            titulo="Reuniões frequentadas"
                            valor={resumo.reunioes_semestre ?? '—'}
                            detalhe="No semestre atual"
                        />
                        <Indicador
                            icon={Target}
                            titulo="Situação da meta"
                            valor={meta}
                            detalhe={
                                resumo.meta.situacao === 'aplicavel'
                                    ? 'Visitas válidas neste mês'
                                    : 'Confira os detalhes no seu dashboard'
                            }
                        />
                    </div>
                </section>

                <section className="rounded-2xl border border-amber-100 bg-gradient-to-r from-amber-50 to-yellow-50/50 p-5">
                    <div className="flex items-center gap-2">
                        <Bell className="size-4 text-amber-600" aria-hidden />
                        <h2 className="font-semibold text-amber-950">
                            Avisos e comunicados
                        </h2>
                    </div>
                    {avisos.length === 0 ? (
                        <p className="mt-3 text-sm text-amber-900/60">
                            Nenhum aviso no momento.
                        </p>
                    ) : (
                        <div className="mt-3 grid gap-2 md:grid-cols-2">
                            {avisos.map((aviso) => (
                                <article
                                    key={aviso.id}
                                    className="flex gap-2 rounded-xl bg-white/80 px-4 py-3"
                                >
                                    <CircleAlert
                                        className="mt-0.5 size-4 shrink-0 text-amber-600"
                                        aria-hidden
                                    />
                                    <div>
                                        <h3 className="text-sm font-semibold text-amber-950">
                                            {aviso.titulo}
                                        </h3>
                                        <p className="mt-1 text-sm leading-relaxed text-amber-900/70">
                                            {aviso.mensagem}
                                        </p>
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </PainelLayout>
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
    valor: number | string;
    detalhe: string;
}) {
    return (
        <article className="rounded-xl border border-amber-100 bg-white p-4 shadow-sm">
            <div className="flex items-center justify-between gap-3">
                <div>
                    <p className="text-xs font-medium text-amber-900/60">
                        {titulo}
                    </p>
                    <p className="mt-1 text-2xl font-bold text-amber-950">
                        {valor}
                    </p>
                </div>
                <span className="flex size-9 shrink-0 items-center justify-center rounded-full bg-amber-50">
                    <Icone className="size-4 text-amber-600" aria-hidden />
                </span>
            </div>
            <p className="mt-2 text-xs text-gray-500">{detalhe}</p>
        </article>
    );
}

function Bloco({
    titulo,
    descricao,
    children,
}: {
    titulo: string;
    descricao: string;
    children: React.ReactNode;
}) {
    return (
        <section className="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
            <h2 className="font-semibold text-amber-950">{titulo}</h2>
            <p className="mt-1 text-sm text-gray-500">{descricao}</p>
            <div className="mt-4">{children}</div>
        </section>
    );
}

function Vazio({
    texto,
    children,
}: {
    texto: string;
    children?: React.ReactNode;
}) {
    return (
        <div className="p-6 text-center text-sm text-gray-500">
            <p>{texto}</p>
            {children}
        </div>
    );
}
