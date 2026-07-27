import ContextoVisita from '@/components/Painel/Visita/Relatorio/Contexto/Show'
import PainelLayout from '@/layouts/PainelLayout'
import { formatarDataHora, labelTipoRelatorio } from '@/lib/visita'
import { edit, index, pdf } from '@/routes/visitas/relatorios'
import type { Visita, VisitaRelatorio } from '@/types/visita'
import { Link } from '@inertiajs/react'
import { ArrowLeft, Download, Pencil } from 'lucide-react'
import { type FC, useState } from 'react'

interface Props {
    visita: Visita
    relatorio: VisitaRelatorio
    podeEditar: boolean
}

const Campo: FC<{ label: string; valor?: string | null }> = ({ label, valor }) => {
    if (!valor) {
        return null
    }

    return (
        <div>
            <dt className="text-xs font-medium uppercase text-amber-900/45">{label}</dt>
            <dd className="mt-1 whitespace-pre-wrap text-sm text-amber-950">{valor}</dd>
        </div>
    )
}

const Show: FC<Props> = ({ visita, relatorio, podeEditar }) => {
    const [baixando, setBaixando] = useState(false)

    const handlePdf = () => {
        if (baixando) {
            return
        }

        setBaixando(true)
        window.location.href = pdf({ visita: visita.id!, relatorio: relatorio.id! }).url
        window.setTimeout(() => setBaixando(false), 3000)
    }

    return (
        <PainelLayout>
            <div className="mx-auto max-w-4xl px-5 py-8 sm:px-6 lg:px-8">
                <div className="mb-6">
                    <Link
                        href={index.url({ visita: visita.id! })}
                        className="inline-flex items-center gap-2 text-sm font-medium text-amber-800/70 transition hover:text-amber-900"
                    >
                        <ArrowLeft className="size-4" aria-hidden />
                        Voltar aos relatórios
                    </Link>
                </div>

                <div className="overflow-hidden rounded-3xl border border-amber-100 bg-white shadow-sm">
                    <div className="border-b border-amber-50 px-6 py-8 sm:px-8">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h1 className="text-2xl font-semibold text-amber-950 sm:text-3xl">
                                    Relatório da visita
                                </h1>
                                <p className="mt-2 text-sm text-amber-900/55">
                                    {labelTipoRelatorio(relatorio.tipo_relatorio)}
                                    {' · '}
                                    {relatorio.autor?.name ?? '—'}
                                    {' · '}
                                    {formatarDataHora(relatorio.enviado_em)}
                                </p>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                {podeEditar && (
                                    <Link
                                        href={edit.url({ visita: visita.id!, relatorio: relatorio.id! })}
                                        className="inline-flex items-center gap-2 rounded-full border-2 border-amber-600 bg-white px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-50"
                                    >
                                        <Pencil className="size-4" aria-hidden />
                                        Editar
                                    </Link>
                                )}
                                <button
                                    type="button"
                                    onClick={handlePdf}
                                    disabled={baixando}
                                    className="inline-flex items-center gap-2 rounded-full border-2 border-amber-600 bg-white px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-50 disabled:opacity-60"
                                >
                                    <Download className="size-4" aria-hidden />
                                    {baixando ? 'Baixando...' : 'Baixar PDF'}
                                </button>
                            </div>
                        </div>
                        {relatorio.fora_do_prazo && (
                            <p className="mt-4 inline-flex rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-xs font-medium uppercase tracking-wide text-orange-800">
                                Enviado fora do prazo recomendado
                            </p>
                        )}
                    </div>

                    <div className="space-y-8 px-6 py-8 sm:px-8">
                        <ContextoVisita visita={visita} />

                        <dl className="space-y-5">
                            {relatorio.alaUnidade ? (
                                <div>
                                    <dt className="text-xs font-medium uppercase text-amber-900/45">Ala / Unidade</dt>
                                    <dd className="mt-1 text-sm text-amber-950">{relatorio.alaUnidade.nome}</dd>
                                </div>
                            ) : null}
                            <Campo label="Resumo" valor={relatorio.resumo} />
                            <Campo label="Feedback" valor={relatorio.feedback} />
                            {relatorio.quartos_visitados != null && (
                                <div>
                                    <dt className="text-xs font-medium uppercase text-amber-900/45">Quartos visitados</dt>
                                    <dd className="mt-1 text-sm text-amber-950">{relatorio.quartos_visitados}</dd>
                                </div>
                            )}
                            {relatorio.pessoas_impactadas != null && (
                                <div>
                                    <dt className="text-xs font-medium uppercase text-amber-900/45">Pessoas impactadas</dt>
                                    <dd className="mt-1 text-sm text-amber-950">{relatorio.pessoas_impactadas}</dd>
                                </div>
                            )}
                            <Campo label="Observação sobre visitantes externos" valor={relatorio.observacao_visitantes_externos} />
                            <Campo label="Observações gerais" valor={relatorio.observacoes_gerais} />
                        </dl>
                    </div>
                </div>
            </div>
        </PainelLayout>
    )
}

export default Show
