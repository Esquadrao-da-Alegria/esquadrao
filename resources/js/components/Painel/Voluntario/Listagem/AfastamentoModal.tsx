import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    painelInputClass,
    painelLabelClass,
} from '@/lib/painelFormFieldClasses';
import { MotivoAfastamento, User } from '@/types';
import { useForm } from '@inertiajs/react';
import {
    AlertTriangle,
    Calendar,
    CalendarPlus,
    CheckCircle2,
    Clock,
    FileText,
    History,
    XCircle,
} from 'lucide-react';
import React, { useEffect, useState } from 'react';
import { formatarData, getMotivoLabel } from './status';
import { VoluntarioListagem } from './types';

interface Props {
    aberto: boolean;
    voluntario: VoluntarioListagem | User | null;
    onOpenChange: (aberto: boolean) => void;
}

type TabType = 'novo' | 'prorrogar' | 'encerrar' | 'historico';

const AfastamentoModal: React.FC<Props> = ({
    aberto,
    voluntario,
    onOpenChange,
}) => {
    if (!voluntario) return null;

    const voluntarioId = (voluntario.voluntario?.id || voluntario.id) as number;
    const afastamentoAtual = voluntario.afastamento_atual;
    const temAfastamentoAtivo = Boolean(voluntario.esta_afastado || afastamentoAtual);

    const [aba, setAba] = useState<TabType>(temAfastamentoAtivo ? 'prorrogar' : 'novo');

    useEffect(() => {
        if (temAfastamentoAtivo) {
            setAba('prorrogar');
        } else {
            setAba('novo');
        }
    }, [voluntario, temAfastamentoAtivo]);

    // Formulário Novo Afastamento
    const hoje = new Date().toISOString().split('T')[0];
    const dataFimPadrao = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)
        .toISOString()
        .split('T')[0];

    const novoForm = useForm({
        data_inicio: hoje,
        data_fim: dataFimPadrao,
        motivo: 'atestado_medico' as MotivoAfastamento,
        observacoes: '',
    });

    // Formulário Prorrogar
    const dataFimAtual = afastamentoAtual?.data_fim
        ? new Date(afastamentoAtual.data_fim)
        : new Date();
    const dataFimProrrogadaPadrao = new Date(dataFimAtual.getTime() + 15 * 24 * 60 * 60 * 1000)
        .toISOString()
        .split('T')[0];

    const prorrogarForm = useForm({
        nova_data_fim: dataFimProrrogadaPadrao,
        observacoes: '',
    });

    // Formulário Encerrar
    const encerrarForm = useForm({
        observacoes: '',
    });

    const setNovoPrazoDias = (dias: number) => {
        const dataInicio = new Date(novoForm.data.data_inicio || Date.now());
        const novaFim = new Date(dataInicio.getTime() + dias * 24 * 60 * 60 * 1000)
            .toISOString()
            .split('T')[0];
        novoForm.setData('data_fim', novaFim);
    };

    const setProrrogarPrazoDias = (dias: number) => {
        const base = afastamentoAtual?.data_fim
            ? new Date(afastamentoAtual.data_fim)
            : new Date();
        const novaFim = new Date(base.getTime() + dias * 24 * 60 * 60 * 1000)
            .toISOString()
            .split('T')[0];
        prorrogarForm.setData('nova_data_fim', novaFim);
    };

    const handleNovoSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        novoForm.post(`/voluntarios/${voluntarioId}/afastamentos`, {
            preserveScroll: true,
            onSuccess: () => {
                onOpenChange(false);
                novoForm.reset();
            },
        });
    };

    const handleProrrogarSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!afastamentoAtual) return;

        prorrogarForm.post(
            `/voluntarios/${voluntarioId}/afastamentos/${afastamentoAtual.id}/prorrogar`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    onOpenChange(false);
                    prorrogarForm.reset();
                },
            }
        );
    };

    const handleEncerrarSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!afastamentoAtual) return;

        encerrarForm.post(
            `/voluntarios/${voluntarioId}/afastamentos/${afastamentoAtual.id}/encerrar`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    onOpenChange(false);
                    encerrarForm.reset();
                },
            }
        );
    };

    const listaHistorico = voluntario.afastamentos || [];

    return (
        <Dialog open={aberto} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto border-amber-100 bg-white sm:max-w-xl">
                <DialogHeader>
                    <DialogTitle className="text-xl font-bold text-amber-950">
                        Afastamentos & Atestados — {voluntario.name}
                    </DialogTitle>
                </DialogHeader>

                {/* Banner de status atual se estiver afastado */}
                {temAfastamentoAtivo && afastamentoAtual && (
                    <div className="rounded-2xl border border-rose-200 bg-rose-50/70 p-4 text-rose-950">
                        <div className="flex items-start gap-3">
                            <AlertTriangle className="size-5 shrink-0 text-rose-600 mt-0.5" />
                            <div className="flex-1 text-sm">
                                <p className="font-semibold text-rose-900">
                                    Voluntário atualmente afastado ({getMotivoLabel(afastamentoAtual.motivo)})
                                </p>
                                <p className="mt-1 text-rose-800/90">
                                    Período:{' '}
                                    <strong>
                                        {formatarData(afastamentoAtual.data_inicio)} até{' '}
                                        {formatarData(afastamentoAtual.data_fim)}
                                    </strong>
                                </p>
                                {afastamentoAtual.observacoes && (
                                    <p className="mt-2 whitespace-pre-line rounded-lg bg-white/60 p-2 text-xs text-rose-900 border border-rose-100">
                                        {afastamentoAtual.observacoes}
                                    </p>
                                )}
                            </div>
                        </div>
                    </div>
                )}

                {/* Navegação de Abas do Modal */}
                <div className="flex flex-wrap gap-2 border-b border-gray-100 pb-3">
                    {temAfastamentoAtivo ? (
                        <>
                            <button
                                type="button"
                                onClick={() => setAba('prorrogar')}
                                className={`inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-semibold transition ${
                                    aba === 'prorrogar'
                                        ? 'bg-amber-600 text-white shadow-sm'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                }`}
                            >
                                <CalendarPlus className="size-3.5" />
                                Prorrogar Prazo
                            </button>
                            <button
                                type="button"
                                onClick={() => setAba('encerrar')}
                                className={`inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-semibold transition ${
                                    aba === 'encerrar'
                                        ? 'bg-red-600 text-white shadow-sm'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                }`}
                            >
                                <XCircle className="size-3.5" />
                                Encerrar Antecipadamente
                            </button>
                            <button
                                type="button"
                                onClick={() => setAba('novo')}
                                className={`inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-semibold transition ${
                                    aba === 'novo'
                                        ? 'bg-amber-600 text-white shadow-sm'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                }`}
                            >
                                <Calendar className="size-3.5" />
                                Novo Afastamento
                            </button>
                        </>
                    ) : (
                        <button
                            type="button"
                            onClick={() => setAba('novo')}
                            className="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm"
                        >
                            <Calendar className="size-3.5" />
                            Cadastrar Afastamento
                        </button>
                    )}

                    {listaHistorico.length > 0 && (
                        <button
                            type="button"
                            onClick={() => setAba('historico')}
                            className={`inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-semibold transition ${
                                aba === 'historico'
                                    ? 'bg-amber-600 text-white shadow-sm'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            }`}
                        >
                            <History className="size-3.5" />
                            Histórico ({listaHistorico.length})
                        </button>
                    )}
                </div>

                {/* Conteúdo Aba: NOVO AFASTAMENTO */}
                {aba === 'novo' && (
                    <form onSubmit={handleNovoSubmit} className="space-y-4 pt-2">
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label htmlFor="data_inicio" className={painelLabelClass}>
                                    Data de Início *
                                </label>
                                <input
                                    id="data_inicio"
                                    type="date"
                                    value={novoForm.data.data_inicio}
                                    onChange={(e) => novoForm.setData('data_inicio', e.target.value)}
                                    className={painelInputClass}
                                    required
                                />
                                {novoForm.errors.data_inicio && (
                                    <p className="mt-1 text-xs text-red-600">{novoForm.errors.data_inicio}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="data_fim" className={painelLabelClass}>
                                    Data de Fim *
                                </label>
                                <input
                                    id="data_fim"
                                    type="date"
                                    value={novoForm.data.data_fim}
                                    onChange={(e) => novoForm.setData('data_fim', e.target.value)}
                                    className={painelInputClass}
                                    required
                                />
                                {novoForm.errors.data_fim && (
                                    <p className="mt-1 text-xs text-red-600">{novoForm.errors.data_fim}</p>
                                )}
                            </div>
                        </div>

                        {/* Botões de atalho de prazo */}
                        <div className="flex items-center gap-2 text-xs text-amber-900">
                            <span className="font-medium text-gray-500">Atalhos de duração:</span>
                            <button
                                type="button"
                                onClick={() => setNovoPrazoDias(15)}
                                className="rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 font-semibold text-amber-800 hover:bg-amber-100"
                            >
                                +15 dias
                            </button>
                            <button
                                type="button"
                                onClick={() => setNovoPrazoDias(30)}
                                className="rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 font-semibold text-amber-800 hover:bg-amber-100"
                            >
                                +30 dias
                            </button>
                            <button
                                type="button"
                                onClick={() => setNovoPrazoDias(60)}
                                className="rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 font-semibold text-amber-800 hover:bg-amber-100"
                            >
                                +60 dias
                            </button>
                        </div>

                        <div>
                            <label htmlFor="motivo" className={painelLabelClass}>
                                Motivo do Afastamento *
                            </label>
                            <select
                                id="motivo"
                                value={novoForm.data.motivo}
                                onChange={(e) =>
                                    novoForm.setData('motivo', e.target.value as MotivoAfastamento)
                                }
                                className={painelInputClass}
                                required
                            >
                                <option value="atestado_medico">Atestado Médico / Tratamento de Saúde</option>
                                <option value="licenca_pessoal">Licença Pessoal / Viagem</option>
                                <option value="estudos">Estudos / Provas / Concurso</option>
                                <option value="outro">Outro Motivo</option>
                            </select>
                            {novoForm.errors.motivo && (
                                <p className="mt-1 text-xs text-red-600">{novoForm.errors.motivo}</p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="observacoes" className={painelLabelClass}>
                                Observações (Opcional)
                            </label>
                            <textarea
                                id="observacoes"
                                rows={3}
                                value={novoForm.data.observacoes}
                                onChange={(e) => novoForm.setData('observacoes', e.target.value)}
                                placeholder="Detalhes adicionais, recomendações médicas ou informações da licença..."
                                className={painelInputClass}
                            />
                            {novoForm.errors.observacoes && (
                                <p className="mt-1 text-xs text-red-600">{novoForm.errors.observacoes}</p>
                            )}
                        </div>

                        {/* Aviso sobre cancelamento de visitas */}
                        <div className="rounded-xl border border-amber-200 bg-amber-50/80 p-3 text-xs text-amber-950 flex items-start gap-2">
                            <AlertTriangle className="size-4 shrink-0 text-amber-700 mt-0.5" />
                            <span>
                                <strong>Aviso:</strong> Todas as inscrições deste voluntário em visitas agendadas dentro do período serão automaticamente canceladas e novas inscrições ficarão bloqueadas até o término da licença.
                            </span>
                        </div>

                        <DialogFooter className="pt-3">
                            <button
                                type="button"
                                onClick={() => onOpenChange(false)}
                                className="inline-flex items-center justify-center rounded-full border px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                disabled={novoForm.processing}
                                className="inline-flex items-center justify-center gap-2 rounded-full bg-amber-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 disabled:opacity-60"
                            >
                                <CheckCircle2 className="size-4" />
                                {novoForm.processing ? 'Registrando...' : 'Registrar Afastamento'}
                            </button>
                        </DialogFooter>
                    </form>
                )}

                {/* Conteúdo Aba: PRORROGAR AFASTAMENTO */}
                {aba === 'prorrogar' && afastamentoAtual && (
                    <form onSubmit={handleProrrogarSubmit} className="space-y-4 pt-2">
                        <div>
                            <label className={painelLabelClass}>
                                Data Final Atual
                            </label>
                            <div className="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-700">
                                {formatarData(afastamentoAtual.data_fim)}
                            </div>
                        </div>

                        <div>
                            <label htmlFor="nova_data_fim" className={painelLabelClass}>
                                Nova Data Final *
                            </label>
                            <input
                                id="nova_data_fim"
                                type="date"
                                value={prorrogarForm.data.nova_data_fim}
                                onChange={(e) => prorrogarForm.setData('nova_data_fim', e.target.value)}
                                min={afastamentoAtual.data_fim}
                                className={painelInputClass}
                                required
                            />
                            {prorrogarForm.errors.nova_data_fim && (
                                <p className="mt-1 text-xs text-red-600">{prorrogarForm.errors.nova_data_fim}</p>
                            )}
                        </div>

                        {/* Atalhos para prorrogação */}
                        <div className="flex items-center gap-2 text-xs text-amber-900">
                            <span className="font-medium text-gray-500">Adicionar dias:</span>
                            <button
                                type="button"
                                onClick={() => setProrrogarPrazoDias(15)}
                                className="rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 font-semibold text-amber-800 hover:bg-amber-100"
                            >
                                +15 dias
                            </button>
                            <button
                                type="button"
                                onClick={() => setProrrogarPrazoDias(30)}
                                className="rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 font-semibold text-amber-800 hover:bg-amber-100"
                            >
                                +30 dias
                            </button>
                            <button
                                type="button"
                                onClick={() => setProrrogarPrazoDias(60)}
                                className="rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 font-semibold text-amber-800 hover:bg-amber-100"
                            >
                                +60 dias
                            </button>
                        </div>

                        <div>
                            <label htmlFor="prorrogar_observacoes" className={painelLabelClass}>
                                Justificativa / Observações da Prorrogação
                            </label>
                            <textarea
                                id="prorrogar_observacoes"
                                rows={3}
                                value={prorrogarForm.data.observacoes}
                                onChange={(e) => prorrogarForm.setData('observacoes', e.target.value)}
                                placeholder="Informe o motivo da prorrogação ou novo laudo médico..."
                                className={painelInputClass}
                            />
                            {prorrogarForm.errors.observacoes && (
                                <p className="mt-1 text-xs text-red-600">{prorrogarForm.errors.observacoes}</p>
                            )}
                        </div>

                        <DialogFooter className="pt-3">
                            <button
                                type="button"
                                onClick={() => onOpenChange(false)}
                                className="inline-flex items-center justify-center rounded-full border px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                disabled={prorrogarForm.processing}
                                className="inline-flex items-center justify-center gap-2 rounded-full bg-amber-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 disabled:opacity-60"
                            >
                                <CalendarPlus className="size-4" />
                                {prorrogarForm.processing ? 'Prorrogando...' : 'Confirmar Prorrogação'}
                            </button>
                        </DialogFooter>
                    </form>
                )}

                {/* Conteúdo Aba: ENCERRAR ANTECIPADAMENTE */}
                {aba === 'encerrar' && afastamentoAtual && (
                    <form onSubmit={handleEncerrarSubmit} className="space-y-4 pt-2">
                        <div className="rounded-2xl border border-red-200 bg-red-50/70 p-4 text-sm text-red-950">
                            <div className="flex items-start gap-3">
                                <XCircle className="size-5 shrink-0 text-red-600 mt-0.5" />
                                <div>
                                    <h4 className="font-bold text-red-900">
                                        Deseja realmente encerrar este afastamento hoje?
                                    </h4>
                                    <p className="mt-1 text-red-800 text-xs leading-relaxed">
                                        Ao encerrar, o status do afastamento passará para <strong>Encerrado</strong>, a data final será ajustada para hoje e o voluntário poderá voltar a se inscrever em visitas normalmente.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label htmlFor="encerrar_observacoes" className={painelLabelClass}>
                                Motivo do Encerramento Antecipado (Opcional)
                            </label>
                            <textarea
                                id="encerrar_observacoes"
                                rows={3}
                                value={encerrarForm.data.observacoes}
                                onChange={(e) => encerrarForm.setData('observacoes', e.target.value)}
                                placeholder="Ex: Alta médica concedida antes do prazo previsto..."
                                className={painelInputClass}
                            />
                            {encerrarForm.errors.observacoes && (
                                <p className="mt-1 text-xs text-red-600">{encerrarForm.errors.observacoes}</p>
                            )}
                        </div>

                        <DialogFooter className="pt-3">
                            <button
                                type="button"
                                onClick={() => onOpenChange(false)}
                                className="inline-flex items-center justify-center rounded-full border px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                disabled={encerrarForm.processing}
                                className="inline-flex items-center justify-center gap-2 rounded-full bg-red-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 disabled:opacity-60"
                            >
                                <XCircle className="size-4" />
                                {encerrarForm.processing ? 'Encerrando...' : 'Encerrar Afastamento'}
                            </button>
                        </DialogFooter>
                    </form>
                )}

                {/* Conteúdo Aba: HISTÓRICO */}
                {aba === 'historico' && (
                    <div className="space-y-3 pt-2">
                        {listaHistorico.length === 0 ? (
                            <p className="py-6 text-center text-sm text-gray-500">
                                Nenhum afastamento registrado para este voluntário.
                            </p>
                        ) : (
                            listaHistorico.map((afast) => (
                                <div
                                    key={afast.id}
                                    className="rounded-xl border border-gray-200 bg-gray-50/50 p-3.5 text-sm"
                                >
                                    <div className="flex items-center justify-between gap-2">
                                        <span className="font-semibold text-gray-900">
                                            {getMotivoLabel(afast.motivo)}
                                        </span>
                                        <span
                                            className={`rounded-full px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide ${
                                                afast.status === 'ativo'
                                                    ? 'bg-rose-100 text-rose-800'
                                                    : afast.status === 'encerrado'
                                                    ? 'bg-gray-200 text-gray-700'
                                                    : 'bg-amber-100 text-amber-800'
                                            }`}
                                        >
                                            {afast.status}
                                        </span>
                                    </div>
                                    <div className="mt-1 flex items-center gap-1.5 text-xs text-gray-600">
                                        <Clock className="size-3 text-gray-400" />
                                        <span>
                                            {formatarData(afast.data_inicio)} até {formatarData(afast.data_fim)}
                                        </span>
                                    </div>
                                    {afast.observacoes && (
                                        <p className="mt-2 whitespace-pre-line rounded-lg bg-white p-2 text-xs text-gray-700 border border-gray-100">
                                            {afast.observacoes}
                                        </p>
                                    )}
                                </div>
                            ))
                        )}
                        <DialogFooter className="pt-3">
                            <button
                                type="button"
                                onClick={() => onOpenChange(false)}
                                className="inline-flex items-center justify-center rounded-full border px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Fechar
                            </button>
                        </DialogFooter>
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
};

export default AfastamentoModal;
