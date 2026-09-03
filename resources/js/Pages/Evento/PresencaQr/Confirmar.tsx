import PainelLayout from '@/layouts/PainelLayout';
import { labelTipo } from '@/lib/evento';
import { router } from '@inertiajs/react';
import { CalendarCheck, CheckCircle2, MapPin } from 'lucide-react';

interface Props {
    evento: {
        id: number;
        titulo: string;
        tipo: string;
        local: string | null;
        data_inicio: string;
        data_fim: string;
    };
    confirmado: boolean;
}

const dataHora = (valor: string) =>
    new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'full',
        timeStyle: 'short',
    }).format(new Date(valor));

export default function Confirmar({ evento, confirmado }: Props) {
    return (
        <PainelLayout>
            <div className="mx-auto flex min-h-[70vh] max-w-lg items-center px-5 py-8 sm:px-6">
                <section className="w-full overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
                    <div className="border-b border-amber-50 bg-amber-50/50 px-5 py-5 text-center sm:px-7">
                        <CalendarCheck className="mx-auto size-9 text-amber-700" aria-hidden />
                        <p className="mt-3 text-xs font-semibold tracking-wide text-amber-700 uppercase">
                            {labelTipo(evento.tipo as never)}
                        </p>
                        <h1 className="mt-1 text-xl font-semibold text-amber-950">
                            {evento.titulo}
                        </h1>
                    </div>

                    <div className="space-y-5 p-5 sm:p-7">
                        <div className="space-y-3 text-sm text-amber-900/70">
                            <p className="flex items-start gap-2">
                                <CalendarCheck className="mt-0.5 size-4 shrink-0 text-amber-700" aria-hidden />
                                {dataHora(evento.data_inicio)}
                            </p>
                            <p className="flex items-start gap-2">
                                <MapPin className="mt-0.5 size-4 shrink-0 text-amber-700" aria-hidden />
                                {evento.local ?? 'Local não informado'}
                            </p>
                        </div>

                        {confirmado ? (
                            <div className="rounded-xl border border-green-200 bg-green-50 px-4 py-4 text-center text-sm font-medium text-green-800">
                                <CheckCircle2 className="mx-auto mb-2 size-6" aria-hidden />
                                Sua presença já está confirmada.
                            </div>
                        ) : (
                            <>
                                <p className="text-center text-sm text-amber-900/60">
                                    Confirme somente se você está participando presencialmente desta atividade.
                                </p>
                                <button
                                    type="button"
                                    onClick={() => router.post('/eventos/presencas-qr/confirmar')}
                                    className="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-full bg-amber-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-800"
                                >
                                    <CheckCircle2 className="size-4" aria-hidden />
                                    Confirmar minha presença
                                </button>
                            </>
                        )}
                    </div>
                </section>
            </div>
        </PainelLayout>
    );
}
