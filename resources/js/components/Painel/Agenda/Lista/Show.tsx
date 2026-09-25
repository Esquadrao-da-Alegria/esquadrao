import { type FC, useState } from 'react';
import { Eye, UserMinus, Users } from 'lucide-react';

import type { Evento } from '@/types';
import type { Visita } from '@/types/visita';
import { classeCardPorStatus } from '@/lib/evento';
import {
    classeCardPorOcupacao,
    participacaoAtivaDoUsuario,
    usuarioEhLiderDaVisita,
} from '@/lib/visita';

type Atividade =
    | { tipo_registro: 'visita'; dado: Visita }
    | { tipo_registro: 'evento'; dado: Evento };

interface Props {
    visitas?: Visita[];
    eventos?: Evento[];
    onSelecionarVisita?: (visita: Visita) => void;
    onSelecionarEvento?: (evento: Evento) => void;
    onParticiparVisita?: (visita: Visita) => void;
    onCancelarInscricao?: (visita: Visita, participanteId: number) => Promise<boolean>;
    usuarioId?: number;
}

const dataAtividade = (atividade: Atividade): string =>
    atividade.tipo_registro === 'visita' ? atividade.dado.inicio_em : atividade.dado.data_inicio;

const inicioDoDiaAtual = (): Date => {
    const hoje = new Date();

    hoje.setHours(0, 0, 0, 0);

    return hoje;
};

const formatarDia = (valor: string) => new Date(valor).toLocaleDateString('pt-BR', { day: '2-digit' });

const formatarSemana = (valor: string) => new Date(valor).toLocaleDateString('pt-BR', { weekday: 'short' }).replace('.', '');

const formatarHora = (valor: string | null | undefined) => valor
    ? new Date(valor).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
    : null;

const nomesParticipantes = (atividade: Atividade): string[] => {
    if (atividade.tipo_registro === 'visita') {
        return (atividade.dado.participantes ?? [])
            .filter((item) => item.status_participacao === 'confirmado')
            .map((item) => item.voluntario?.name)
            .filter((nome): nome is string => Boolean(nome));
    }

    return (atividade.dado.participantes_ativos ?? []).map((item) => item.name);
};

const classeCor = (atividade: Atividade): string => {
    if (atividade.tipo_registro === 'visita') {
        return classeCardPorOcupacao(atividade.dado);
    }

    return classeCardPorStatus(atividade.dado.status);
};

const Show: FC<Props> = ({ visitas = [], eventos = [], onSelecionarVisita, onSelecionarEvento, onParticiparVisita, onCancelarInscricao, usuarioId }) => {
    const [cancelandoParticipanteId, setCancelandoParticipanteId] = useState<number | null>(null);
    const atividades: Atividade[] = [
        ...visitas.map((dado) => ({ tipo_registro: 'visita' as const, dado })),
        ...eventos.map((dado) => ({ tipo_registro: 'evento' as const, dado })),
    ]
        .filter((atividade) => new Date(dataAtividade(atividade)) >= inicioDoDiaAtual())
        .sort((a, b) => dataAtividade(a).localeCompare(dataAtividade(b)));

    if (atividades.length === 0) {
        return <div className="rounded-2xl border border-dashed border-amber-200 bg-white px-5 py-12 text-center text-sm text-amber-900/55">Nenhuma atividade a partir de hoje neste período.</div>;
    }

    const cancelarInscricao = async (visita: Visita, participanteId: number) => {
        if (cancelandoParticipanteId !== null || !onCancelarInscricao) return;

        setCancelandoParticipanteId(participanteId);
        await onCancelarInscricao(visita, participanteId);
        setCancelandoParticipanteId(null);
    };

    return (
        <section className="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
            <div className="space-y-2 bg-amber-50/40 p-2 sm:p-3">
                {atividades.map((atividade) => {
                    const visita = atividade.tipo_registro === 'visita' ? atividade.dado : null;
                    const evento = atividade.tipo_registro === 'evento' ? atividade.dado : null;
                    const inicio = visita?.inicio_em ?? evento!.data_inicio;
                    const participantes = nomesParticipantes(atividade);
                    const limite = visita?.limite_participantes ?? evento?.limite_participantes ?? null;
                    const inscritosAtivos = visita?.participantes?.filter((item) => item.status_participacao === 'confirmado' || item.status_participacao === 'pendente') ?? [];
                    const participacaoAtiva = visita && usuarioId !== undefined
                        ? participacaoAtivaDoUsuario(visita, usuarioId)
                        : null;
                    const podeParticipar = visita?.status === 'agendada'
                        && new Date(visita.inicio_em) >= new Date()
                        && !inscritosAtivos.some((item) => item.voluntario_id === usuarioId)
                        && (limite === null || inscritosAtivos.length < limite);
                    const podeCancelarInscricao = visita?.status === 'agendada'
                        && new Date(visita.fim_em) >= new Date()
                        && usuarioId !== undefined
                        && !usuarioEhLiderDaVisita(visita, usuarioId)
                        && participacaoAtiva?.id !== undefined;
                    const local = visita
                        ? [visita.hospital?.nome, visita.alaUnidade?.nome].filter(Boolean).join(' · ') || 'Local não informado'
                        : evento?.local || 'Local não informado';
                    const cidade = visita?.hospital?.cidade?.nome ?? evento?.cidade?.nome;
                    const titulo = visita ? `Visita ${visita.tipo}` : evento!.titulo;

                    return (
                        <article key={`${atividade.tipo_registro}-${atividade.dado.id}`} className="grid grid-cols-[3.75rem_minmax(0,1fr)] gap-3 rounded-xl border border-amber-100 bg-white p-3 shadow-sm sm:grid-cols-[5.25rem_minmax(0,1fr)_minmax(14rem,1fr)_auto] sm:items-center sm:p-4">
                            <div className={`self-start rounded-lg px-2 py-2 text-center shadow-sm sm:self-center sm:px-3 ${classeCor(atividade)}`}>
                                <div className="text-2xl font-semibold leading-none sm:text-3xl">{formatarDia(inicio)}</div>
                                <div className="mt-1 text-[10px] font-medium uppercase tracking-wide">{formatarSemana(inicio)}</div>
                                <div className="mt-1 text-[10px]">{formatarHora(inicio)}</div>
                            </div>
                            <div className="min-w-0">
                                <div className="flex items-center gap-2">
                                    <span className="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold capitalize text-amber-800">{visita ? 'Visita' : evento?.tipo}</span>
                                </div>
                                <div className="mt-1 truncate text-base font-semibold text-amber-950" title={titulo}>{titulo}</div>
                                <div className="mt-0.5 truncate text-sm text-amber-900/60" title={local}>{local}{cidade ? ` · ${cidade}` : ''}</div>
                            </div>
                            <div className="col-start-2 min-w-0 sm:col-start-auto">
                                <div className="flex items-center gap-1.5 text-sm font-medium text-amber-900/75"><Users className="size-4 shrink-0" />{participantes.length}{limite !== null ? `/${limite}` : ' sem limite'}</div>
                                <div className="mt-0.5 text-sm leading-relaxed text-amber-900/55">{participantes.length ? participantes.join(', ') : 'Sem participantes confirmados'}</div>
                            </div>
                            <div className="col-span-2 flex items-center justify-center gap-2 border-t border-amber-50 pt-2 sm:col-span-1 sm:flex-col sm:items-stretch sm:border-0 sm:pt-0 lg:flex-row lg:items-center lg:justify-end">
                                <button type="button" onClick={() => visita ? onSelecionarVisita?.(visita) : onSelecionarEvento?.(evento!)} className="inline-flex min-h-9 flex-1 items-center justify-center gap-1.5 rounded-full border border-amber-100 bg-amber-50/60 px-3 text-xs font-semibold text-amber-800 shadow-sm transition hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 lg:flex-none"><Eye className="size-3.5" />Detalhes</button>
                                {podeParticipar ? <button type="button" onClick={() => onParticiparVisita?.(visita!)} className="inline-flex min-h-9 flex-1 items-center justify-center gap-1.5 rounded-full bg-amber-600 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-700 hover:shadow focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 lg:flex-none"><Users className="size-3.5" />Participar</button> : null}
                                {podeCancelarInscricao ? <button type="button" onClick={() => cancelarInscricao(visita!, participacaoAtiva.id!)} disabled={cancelandoParticipanteId === participacaoAtiva.id} className="inline-flex min-h-9 flex-1 items-center justify-center gap-1.5 rounded-full border border-red-100 bg-red-50/60 px-3 text-xs font-semibold text-red-700 shadow-sm transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 disabled:opacity-50 lg:flex-none"><UserMinus className="size-3.5" />{cancelandoParticipanteId === participacaoAtiva.id ? 'Cancelando...' : 'Cancelar inscrição'}</button> : null}
                            </div>
                        </article>
                    );
                })}
            </div>
        </section>
    );
};

export default Show;
