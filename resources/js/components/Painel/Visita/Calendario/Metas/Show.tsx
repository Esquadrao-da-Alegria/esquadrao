import { Target } from 'lucide-react';
import { type FC } from 'react';

export interface AcompanhamentoMeta {
    hospital_id: number;
    hospital: string;
    ala_id: number | null;
    ala: string;
    semana: number;
    meta_semanal: number | null;
    planejadas_semana: number | null;
    faltam_semana: number;
    meta_mensal: number;
    planejadas_mes: number;
    faltam_mes: number;
}

interface Props {
    metas: AcompanhamentoMeta[];
}

const Show: FC<Props> = ({ metas }) => {
    if (metas.length === 0) return null;

    return (
        <section className="mt-4 overflow-hidden rounded-xl border border-amber-100 bg-white" aria-labelledby="titulo-acompanhamento-metas">
            <div className="flex items-center gap-2 border-b border-amber-100 px-3 py-2">
                <Target className="size-3.5 text-amber-700" aria-hidden />
                <h2 id="titulo-acompanhamento-metas" className="text-xs font-semibold text-amber-950">
                    Hospitais que ainda precisam de visitas
                </h2>
            </div>
            <div className="divide-y divide-amber-50 text-[11px] sm:text-xs">
                {metas.map((meta) => (
                    <div key={`${meta.hospital_id}-${meta.ala_id ?? 'geral'}`} className="flex min-w-0 items-center gap-2 px-3 py-2 text-amber-950">
                        <span className="min-w-0 flex-1" title={`${meta.hospital} · ${meta.ala}`}>
                            <span className="block truncate font-medium">{meta.hospital}</span>
                            <span className="block truncate text-[10px] text-amber-900/45">{meta.ala}</span>
                        </span>
                        <span className="shrink-0 rounded-full bg-amber-50 px-2 py-0.5 tabular-nums" title={`Semana ${meta.semana}: faltam ${meta.faltam_semana} visitas`}>
                            S {meta.meta_semanal === null ? '—' : `${meta.planejadas_semana}/${meta.meta_semanal}`}
                        </span>
                        <span className="shrink-0 rounded-full bg-amber-50 px-2 py-0.5 tabular-nums" title={`No mês faltam ${meta.faltam_mes} visitas`}>
                            M {meta.planejadas_mes}/{meta.meta_mensal}
                        </span>
                    </div>
                ))}
            </div>
        </section>
    );
};

export default Show;
