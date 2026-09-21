import { formatarDataHora, labelStatus } from '@/lib/visita';
import type { Visita } from '@/types/visita';
import { type FC } from 'react';

interface Props {
    visita: Visita;
}

const ContextoVisita: FC<Props> = ({ visita }) => {
    return (
        <div className="rounded-2xl border border-amber-100 bg-amber-50/40 p-5">
            <h3 className="mb-4 text-sm font-semibold tracking-wide text-amber-900/70 uppercase">
                Contexto da visita
            </h3>
            <dl className="grid gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt className="text-xs font-medium text-amber-900/45 uppercase">
                        Hospital
                    </dt>
                    <dd className="mt-0.5 font-medium text-amber-950">
                        {visita.hospital?.nome ?? '—'}
                    </dd>
                </div>
                {visita.alaUnidade ? (
                    <div>
                        <dt className="text-xs font-medium text-amber-900/45 uppercase">
                            Ala / Unidade
                        </dt>
                        <dd className="mt-0.5 text-amber-900">
                            {visita.alaUnidade.nome}
                        </dd>
                    </div>
                ) : null}
                <div>
                    <dt className="text-xs font-medium text-amber-900/45 uppercase">
                        Início
                    </dt>
                    <dd className="mt-0.5 text-amber-900">
                        {formatarDataHora(visita.inicio_em)}
                    </dd>
                </div>
                <div>
                    <dt className="text-xs font-medium text-amber-900/45 uppercase">
                        Fim
                    </dt>
                    <dd className="mt-0.5 text-amber-900">
                        {formatarDataHora(visita.fim_em)}
                    </dd>
                </div>
                <div>
                    <dt className="text-xs font-medium text-amber-900/45 uppercase">
                        Status
                    </dt>
                    <dd className="mt-0.5 text-amber-900">
                        {labelStatus(visita.status)}
                    </dd>
                </div>
                <div>
                    <dt className="text-xs font-medium text-amber-900/45 uppercase">
                        Líder
                    </dt>
                    <dd className="mt-0.5 text-amber-900">
                        {visita.lider?.name ?? '—'}
                    </dd>
                </div>
                <div className="sm:col-span-2">
                    <dt className="text-xs font-medium text-amber-900/45 uppercase">
                        Participantes
                    </dt>
                    <dd className="mt-0.5 text-amber-900">
                        {(() => {
                            const lista = (visita.participantes ?? []).filter(
                                (p) =>
                                    p.papel_na_visita === 'participante' &&
                                    p.status_participacao !== 'cancelado',
                            );
                            if (lista.length === 0) return '—';
                            return (
                                <ul className="space-y-0.5">
                                    {lista.map((p) => (
                                        <li key={p.id ?? p.voluntario_id}>
                                            {p.tipo_participacao === 'palhaco'
                                                ? '🎪'
                                                : '👔'}{' '}
                                            {p.voluntario?.name ?? '—'}
                                        </li>
                                    ))}
                                </ul>
                            );
                        })()}
                    </dd>
                </div>
            </dl>
        </div>
    );
};

export default ContextoVisita;
