import InputError from '@/components/input-error';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { useForm } from '@inertiajs/react';
import { History, ShieldCheck, UserRoundCog } from 'lucide-react';
import { FormEvent, useState } from 'react';

type Pessoa = { id: number; name: string };
type Ajuste = {
    id: number;
    tipo: string;
    justificativa: string;
    created_at: string;
    voluntario: Pessoa;
    administrador: Pessoa;
    dados_anteriores?: { presenca?: string };
    dados_posteriores: { presenca?: string };
};

export default function AjusteParticipacao({
    eventoId,
    voluntarios,
    ajustes,
}: {
    eventoId: number;
    voluntarios: Pessoa[];
    ajustes: Ajuste[];
}) {
    const [aberto, setAberto] = useState(false);
    const form = useForm({
        tipo: 'correcao_inscricao',
        voluntario_id: '',
        presenca: 'presente',
        justificativa: '',
    });
    const enviar = (event: FormEvent) => {
        event.preventDefault();
        form.post(`/eventos/${eventoId}/ajustes-participacao`, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                setAberto(false);
            },
        });
    };

    return (
        <section className="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
            <div className="flex flex-col gap-3 border-b border-amber-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div className="flex items-center gap-2">
                        <ShieldCheck className="size-4 text-amber-700" />
                        <h2 className="font-semibold text-amber-950">
                            Ajustes administrativos
                        </h2>
                    </div>
                    <p className="mt-1 text-xs text-amber-900/55">
                        Use somente para corrigir uma inscrição ou presença após
                        o início.
                    </p>
                </div>
                <Dialog open={aberto} onOpenChange={setAberto}>
                    <DialogTrigger asChild>
                        <button className="inline-flex cursor-pointer items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-50">
                            <UserRoundCog className="size-4" />
                            Novo ajuste
                        </button>
                    </DialogTrigger>
                    <DialogContent className="max-h-[90vh] overflow-y-auto border-amber-100 bg-white sm:max-w-lg">
                        <DialogHeader>
                            <DialogTitle>
                                Corrigir participação no evento
                            </DialogTitle>
                            <DialogDescription>
                                O ajuste será permanente no histórico e afetará
                                os indicadores.
                            </DialogDescription>
                        </DialogHeader>
                        <form onSubmit={enviar} className="space-y-4">
                            <div>
                                <label className="mb-1 block text-sm font-medium text-amber-950">
                                    Correção
                                </label>
                                <select
                                    className="w-full rounded-xl border border-amber-200 bg-white px-3 py-2.5 text-sm"
                                    value={form.data.tipo}
                                    onChange={(e) =>
                                        form.setData('tipo', e.target.value)
                                    }
                                >
                                    <option value="correcao_inscricao">
                                        Incluir ou reativar inscrição
                                    </option>
                                    <option value="correcao_presenca">
                                        Corrigir presença
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label className="mb-1 block text-sm font-medium text-amber-950">
                                    Voluntário
                                </label>
                                <select
                                    className="w-full rounded-xl border border-amber-200 bg-white px-3 py-2.5 text-sm"
                                    value={form.data.voluntario_id}
                                    onChange={(e) =>
                                        form.setData(
                                            'voluntario_id',
                                            e.target.value,
                                        )
                                    }
                                >
                                    <option value="">Selecione</option>
                                    {voluntarios.map((v) => (
                                        <option key={v.id} value={v.id}>
                                            {v.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError
                                    message={form.errors.voluntario_id}
                                />
                            </div>
                            {form.data.tipo === 'correcao_presenca' && (
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-amber-950">
                                        Presença correta
                                    </label>
                                    <select
                                        className="w-full rounded-xl border border-amber-200 bg-white px-3 py-2.5 text-sm"
                                        value={form.data.presenca}
                                        onChange={(e) =>
                                            form.setData(
                                                'presenca',
                                                e.target.value,
                                            )
                                        }
                                    >
                                        <option value="presente">
                                            Presente
                                        </option>
                                        <option value="ausente">Ausente</option>
                                    </select>
                                </div>
                            )}
                            <div>
                                <label className="mb-1 block text-sm font-medium text-amber-950">
                                    Justificativa
                                </label>
                                <textarea
                                    rows={4}
                                    className="w-full resize-none rounded-xl border border-amber-200 bg-white px-3 py-2.5 text-sm"
                                    placeholder="Descreva o motivo e a evidência da correção"
                                    value={form.data.justificativa}
                                    onChange={(e) =>
                                        form.setData(
                                            'justificativa',
                                            e.target.value,
                                        )
                                    }
                                />
                                <InputError
                                    message={form.errors.justificativa}
                                />
                            </div>
                            <button
                                disabled={form.processing}
                                className="w-full rounded-full border-2 border-amber-600 bg-white px-5 py-2.5 font-semibold text-amber-700 hover:bg-amber-50 disabled:opacity-50"
                            >
                                Registrar ajuste
                            </button>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
            <div className="p-6">
                <div className="mb-3 flex items-center gap-2 text-sm font-medium text-amber-950">
                    <History className="size-4" />
                    Histórico auditável
                </div>
                {ajustes.length === 0 ? (
                    <p className="text-sm text-amber-900/50">
                        Nenhum ajuste registrado.
                    </p>
                ) : (
                    <div className="space-y-3">
                        {ajustes.map((a) => (
                            <div
                                key={a.id}
                                className="rounded-xl border border-amber-100 bg-amber-50/30 p-4 text-sm"
                            >
                                <div className="flex flex-wrap justify-between gap-2">
                                    <strong className="text-amber-950">
                                        {a.tipo === 'correcao_inscricao'
                                            ? 'Inscrição corrigida'
                                            : `Presença corrigida para ${a.dados_posteriores.presenca}`}
                                    </strong>
                                    <span className="text-amber-900/45">
                                        {new Date(a.created_at).toLocaleString(
                                            'pt-BR',
                                        )}
                                    </span>
                                </div>
                                <p className="mt-1 text-amber-900/70">
                                    {a.voluntario.name} · por{' '}
                                    {a.administrador.name}
                                </p>
                                <p className="mt-2 text-amber-900/60">
                                    {a.justificativa}
                                </p>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </section>
    );
}
