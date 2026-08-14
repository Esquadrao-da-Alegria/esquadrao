import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { show as participanteShow } from '@/routes/dashboards/visitas-por-participante';
import type {
    AcompanhamentoParticipante,
    FiltrosParticipante,
} from '@/types/dashboard-participante';
import { Link } from '@inertiajs/react';

interface Props {
    participante: AcompanhamentoParticipante | null;
    filtros: FiltrosParticipante;
    onOpenChange: (aberto: boolean) => void;
}

const percentual = (valor: number | null) =>
    valor === null ? 'Não disponível' : `${valor}%`;

export default function Show({ participante, filtros, onOpenChange }: Props) {
    if (!participante) return null;

    const filtrosAtivos = Object.fromEntries(
        Object.entries(filtros).filter(
            ([, valor]) => valor !== null && valor !== undefined && valor !== '',
        ),
    );

    return (
        <Dialog open onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto border-amber-100 bg-white sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle className="text-amber-950">
                        {participante.nome}
                    </DialogTitle>
                    <p className="text-sm text-gray-500">
                        {participante.cidade} · {participante.cargos.join(', ')}
                    </p>
                </DialogHeader>

                <div className="grid gap-3 sm:grid-cols-2">
                    <Detalhe titulo="Visitas válidas" valor={String(participante.visitas_validas)} detalhe={participante.meta_mensal === null ? 'Sem meta de visitas' : `Meta de ${participante.meta_mensal} por mês`} />
                    <Detalhe titulo="Saldo atual" valor={participante.saldo_atual === null ? 'Não aplicável' : `${participante.saldo_atual > 0 ? '+' : ''}${participante.saldo_atual}`} detalhe={participante.compensacao_atual ?? 'Sem compensação pendente'} />
                    <Detalhe titulo="Reuniões" valor={percentual(participante.reunioes.percentual)} detalhe={`${participante.reunioes.presencas} presenças em ${participante.reunioes.oferecidos} eventos`} />
                    <Detalhe titulo="Oficinas" valor={percentual(participante.oficinas.percentual)} detalhe={`${participante.oficinas.presencas} presenças em ${participante.oficinas.oferecidos} eventos`} />
                    <Detalhe titulo="Relatórios" valor={`${participante.relatorios_pendentes} pendentes`} detalhe={`${participante.relatorios_fora_prazo} fora do prazo`} />
                    <Detalhe titulo="Última atividade" valor={participante.ultima_atividade ? new Date(participante.ultima_atividade).toLocaleDateString('pt-BR') : 'Não registrada'} detalhe={participante.dias_sem_atividade === null ? 'Sem dados suficientes' : `${participante.dias_sem_atividade} dias sem atividade documentada`} />
                </div>

                <p className="rounded-xl bg-amber-50 px-4 py-3 text-xs leading-relaxed text-amber-900/70">
                    Os dados apoiam a análise da coordenação e não geram decisões automáticas sobre o voluntário.
                </p>

                <DialogFooter>
                    <button type="button" onClick={() => onOpenChange(false)} className="rounded-full border px-5 py-2.5 font-semibold text-gray-700 transition hover:bg-gray-50">
                        Fechar
                    </button>
                    <Link href={participanteShow(participante.id, { query: filtrosAtivos })} className="inline-flex items-center justify-center rounded-full border-2 border-amber-600 bg-white px-5 py-2.5 font-semibold text-amber-700 transition hover:bg-amber-50">
                        Ver histórico completo
                    </Link>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

function Detalhe({ titulo, valor, detalhe }: { titulo: string; valor: string; detalhe: string }) {
    return (
        <div className="rounded-xl border border-amber-100 p-4">
            <p className="text-xs font-medium text-amber-900/60">{titulo}</p>
            <p className="mt-1 font-semibold text-amber-950">{valor}</p>
            <p className="mt-1 text-xs text-gray-500">{detalhe}</p>
        </div>
    );
}
