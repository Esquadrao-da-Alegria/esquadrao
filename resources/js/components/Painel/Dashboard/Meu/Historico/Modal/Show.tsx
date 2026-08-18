import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { show as eventoShow } from '@/routes/eventos';
import { create as relatorioCreate } from '@/routes/visitas/relatorios';
import type { HistoricoMeuDashboard } from '@/types/meu-dashboard';
import { Link } from '@inertiajs/react';

interface Props {
    item: HistoricoMeuDashboard | null;
    onOpenChange: (aberto: boolean) => void;
}

const formatarData = (valor: string) =>
    new Date(valor).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

export default function Show({ item, onOpenChange }: Props) {
    if (!item) return null;

    return (
        <Dialog open onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto border-amber-100 bg-white sm:max-w-xl">
                <DialogHeader>
                    <DialogTitle className="text-amber-950">
                        {item.titulo}
                    </DialogTitle>
                    <p className="text-sm text-gray-500 capitalize">
                        {item.tipo} · {formatarData(item.data)}
                    </p>
                </DialogHeader>

                <dl className="grid gap-3 sm:grid-cols-2">
                    <Detalhe titulo="Local" valor={item.local} />
                    <Detalhe titulo="Cidade" valor={item.cidade} />
                    {item.ala && (
                        <Detalhe titulo="Ala / unidade" valor={item.ala} />
                    )}
                    {item.tipo_participacao && (
                        <Detalhe
                            titulo="Participação"
                            valor={item.tipo_participacao.replaceAll('_', ' ')}
                        />
                    )}
                    <Detalhe
                        titulo="Situação"
                        valor={item.situacao.replaceAll('_', ' ')}
                    />
                    {item.relatorio && (
                        <Detalhe
                            titulo="Relatório"
                            valor={item.relatorio.replaceAll('_', ' ')}
                        />
                    )}
                    {item.impacto_estimado !== null && (
                        <Detalhe
                            titulo="Impacto estimado da visita"
                            valor={`${item.impacto_estimado} pessoas`}
                        />
                    )}
                </dl>

                <div className="rounded-xl bg-amber-50 px-4 py-3">
                    <p className="text-xs font-semibold text-amber-900">
                        Como esta atividade foi interpretada
                    </p>
                    <p className="mt-1 text-sm text-amber-900/70">
                        {item.motivo}
                    </p>
                </div>

                <DialogFooter>
                    <button
                        type="button"
                        onClick={() => onOpenChange(false)}
                        className="rounded-full border px-5 py-2.5 font-semibold text-gray-700 transition hover:bg-gray-50"
                    >
                        Fechar
                    </button>
                    {item.tipo === 'visita' &&
                        item.relatorio === 'pendente' && (
                            <Link
                                href={relatorioCreate(item.id)}
                                className="rounded-full border-2 border-amber-600 px-5 py-2.5 font-semibold text-amber-700 transition hover:bg-amber-50"
                            >
                                Cadastrar relatório
                            </Link>
                        )}
                    {item.tipo !== 'visita' && (
                        <Link
                            href={eventoShow(item.id)}
                            className="rounded-full border-2 border-amber-600 px-5 py-2.5 font-semibold text-amber-700 transition hover:bg-amber-50"
                        >
                            Ver atividade
                        </Link>
                    )}
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

function Detalhe({ titulo, valor }: { titulo: string; valor: string }) {
    return (
        <div className="rounded-xl border border-amber-100 p-3">
            <dt className="text-xs text-amber-900/60">{titulo}</dt>
            <dd className="mt-1 text-sm font-semibold text-amber-950 capitalize">
                {valor}
            </dd>
        </div>
    );
}
