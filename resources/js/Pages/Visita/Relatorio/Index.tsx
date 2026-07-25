import PainelLayout from '@/layouts/PainelLayout'
import {
    formatarDataHora,
    labelTipoRelatorio,
    podeEditarRelatorio,
} from '@/lib/visita'
import { index as visitasIndex } from '@/routes/visitas'
import { create, edit, pdf, show } from '@/routes/visitas/relatorios'
import type { SharedData } from '@/types'
import type { Visita, VisitaRelatorio } from '@/types/visita'
import { Link, router, usePage } from '@inertiajs/react'
import { ArrowLeft, Download, Eye, FileText, Pencil, Plus } from 'lucide-react'
import { type FC, useState } from 'react'

interface Props {
    visita: Visita
    relatorios: VisitaRelatorio[]
}

function resumoCurto(texto: string, limite = 120): string {
    if (texto.length <= limite) {
        return texto
    }

    return `${texto.slice(0, limite)}…`
}

const Index: FC<Props> = ({ visita, relatorios }) => {
    const { auth } = usePage<SharedData>().props
    const [baixandoId, setBaixandoId] = useState<number | null>(null)
    const visitaCancelada = visita.status === 'cancelada'

    const handleCriar = () => {
        router.visit(create.url({ visita: visita.id! }))
    }

    const handlePdf = (relatorio: VisitaRelatorio) => {
        if (!relatorio.id || baixandoId !== null) {
            return
        }

        setBaixandoId(relatorio.id)
        window.location.href = pdf({ visita: visita.id!, relatorio: relatorio.id }).url
        window.setTimeout(() => setBaixandoId(null), 3000)
    }

    return (
        <PainelLayout>
            <div className="mx-auto max-w-7xl px-5 py-8 sm:px-6 lg:px-8">
                <div className="mb-6">
                    <Link
                        href={visitasIndex().url}
                        className="inline-flex items-center gap-2 text-sm font-medium text-amber-800/70 transition hover:text-amber-900"
                    >
                        <ArrowLeft className="size-4" aria-hidden />
                        Voltar ao calendário
                    </Link>
                </div>

                <header className="mb-10 flex flex-col gap-4 sm:mb-12 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight text-amber-950 sm:text-3xl">
                            Relatórios da visita
                        </h1>
                        <p className="mt-1 max-w-xl text-sm text-amber-900/55">
                            {visita.hospital?.nome ?? 'Visita'}
                            {' · '}
                            {relatorios.length === 0
                                ? 'Nenhum relatório enviado ainda.'
                                : `${relatorios.length} ${relatorios.length === 1 ? 'relatório' : 'relatórios'}`}
                        </p>
                    </div>
                    {!visitaCancelada && (
                        <button
                            onClick={handleCriar}
                            type="button"
                            className="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 text-sm font-semibold text-amber-700 shadow-sm transition hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 sm:w-auto"
                        >
                            <Plus className="size-5" strokeWidth={2} aria-hidden />
                            Novo relatório
                        </button>
                    )}
                </header>

                {relatorios.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-amber-200 bg-white px-6 py-20 text-center">
                        <FileText className="mb-4 size-10 text-amber-300" aria-hidden />
                        <p className="text-sm text-amber-900/50">
                            Nenhum relatório cadastrado para esta visita.
                        </p>
                        {!visitaCancelada && (
                            <button
                                type="button"
                                onClick={handleCriar}
                                className="mt-6 inline-flex items-center gap-2 rounded-full border-2 border-amber-600 bg-white px-5 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-50"
                            >
                                <Plus className="size-4" aria-hidden />
                                Criar relatório
                            </button>
                        )}
                    </div>
                ) : (
                    <ul className="flex w-full flex-col gap-5">
                        {relatorios.map((relatorio) => {
                            const podeEditar = podeEditarRelatorio(auth.user, visita, relatorio)

                            return (
                                <li key={relatorio.id} className="w-full">
                                    <article className="w-full overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm transition duration-300 hover:border-amber-200 hover:shadow-md">
                                        <div className="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:gap-6 sm:p-6">
                                            <div className="min-w-0 flex-1">
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <span className="inline-flex items-center rounded-full border border-amber-200/80 bg-amber-50/80 px-2.5 py-0.5 text-[11px] font-medium uppercase tracking-wide text-amber-900">
                                                        {labelTipoRelatorio(relatorio.tipo_relatorio)}
                                                    </span>
                                                    {relatorio.fora_do_prazo && (
                                                        <span className="inline-flex items-center rounded-full border border-orange-200 bg-orange-50 px-2.5 py-0.5 text-[11px] font-medium uppercase tracking-wide text-orange-800">
                                                            Fora do prazo
                                                        </span>
                                                    )}
                                                </div>
                                                <p className="mt-3 text-sm leading-relaxed text-amber-950/80">
                                                    {resumoCurto(relatorio.resumo)}
                                                </p>
                                                <dl className="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                                                    <div>
                                                        <dt className="text-xs font-medium uppercase text-amber-900/45">Autor</dt>
                                                        <dd className="text-amber-900">{relatorio.autor?.name ?? '—'}</dd>
                                                    </div>
                                                    <div>
                                                        <dt className="text-xs font-medium uppercase text-amber-900/45">Enviado em</dt>
                                                        <dd className="text-amber-900">{formatarDataHora(relatorio.enviado_em)}</dd>
                                                    </div>
                                                </dl>
                                            </div>
                                            <div className="flex shrink-0 flex-wrap items-center gap-2 border-t border-amber-50 pt-4 sm:border-t-0 sm:border-l sm:border-amber-50 sm:pl-6 sm:pt-0">
                                                <Link
                                                    href={show({ visita: visita.id!, relatorio: relatorio.id! }).url}
                                                    className="inline-flex items-center gap-1.5 rounded-full border border-amber-200 px-3 py-1.5 text-xs font-semibold text-amber-800 transition hover:bg-amber-50"
                                                >
                                                    <Eye className="size-3.5" aria-hidden />
                                                    Ver
                                                </Link>
                                                {podeEditar && (
                                                    <Link
                                                        href={edit({ visita: visita.id!, relatorio: relatorio.id! }).url}
                                                        className="inline-flex items-center gap-1.5 rounded-full border border-amber-200 px-3 py-1.5 text-xs font-semibold text-amber-800 transition hover:bg-amber-50"
                                                    >
                                                        <Pencil className="size-3.5" aria-hidden />
                                                        Editar
                                                    </Link>
                                                )}
                                                <button
                                                    type="button"
                                                    onClick={() => handlePdf(relatorio)}
                                                    disabled={baixandoId === relatorio.id}
                                                    className="inline-flex items-center gap-1.5 rounded-full border border-amber-200 px-3 py-1.5 text-xs font-semibold text-amber-800 transition hover:bg-amber-50 disabled:opacity-60"
                                                >
                                                    <Download className="size-3.5" aria-hidden />
                                                    {baixandoId === relatorio.id ? 'Baixando...' : 'PDF'}
                                                </button>
                                            </div>
                                        </div>
                                    </article>
                                </li>
                            )
                        })}
                    </ul>
                )}
            </div>
        </PainelLayout>
    )
}

export default Index
