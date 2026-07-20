import PainelLayout from '@/layouts/PainelLayout'
import type { Visita } from '@/types'
import { Link } from '@inertiajs/react'
import type { FC } from 'react'

interface Relatorio {
    id: number
    tipo_relatorio: 'artista' | 'paisana' | 'geral'
    resumo: string
    feedback?: string | null
    ala_unidade?: string | null
    quartos_visitados?: number | null
    pessoas_impactadas?: number | null
    observacao_visitantes_externos?: string | null
    observacoes_gerais?: string | null
    enviado_em: string
    fora_do_prazo: boolean
    autor?: { name: string }
}

interface Props { visita: Visita; relatorio: Relatorio; podeEditar: boolean }
const labels = { artista: 'Artista', paisana: 'Paisana', geral: 'Geral' }

const Campo = ({ titulo, valor }: { titulo: string; valor?: string | number | null }) => valor === null || valor === undefined || valor === '' ? null : <div><dt className="text-xs font-semibold uppercase text-gray-400">{titulo}</dt><dd className="mt-1 whitespace-pre-wrap text-sm text-gray-800">{valor}</dd></div>

const Show: FC<Props> = ({ visita, relatorio, podeEditar }) => (
    <PainelLayout><section className="mx-auto max-w-4xl px-4 py-12"><div className="rounded-3xl border bg-white p-8"><div className="mb-8 flex flex-wrap items-start justify-between gap-4"><div><h1 className="text-3xl font-bold text-amber-800">Relatório da visita</h1><p className="mt-1 text-sm text-gray-500">{labels[relatorio.tipo_relatorio]} · {relatorio.autor?.name ?? '—'} · {new Date(relatorio.enviado_em).toLocaleString('pt-BR')}</p>{relatorio.fora_do_prazo && <span className="mt-3 inline-flex rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-800">Enviado fora do prazo recomendado</span>}</div>{podeEditar && <Link href={`/visitas/${visita.id}/relatorios/${relatorio.id}/edit`} className="rounded-full border-2 border-amber-600 px-5 py-3 font-semibold text-amber-700">Editar</Link>}</div><dl className="space-y-6"><Campo titulo="Resumo" valor={relatorio.resumo} /><Campo titulo="Feedback" valor={relatorio.feedback} /><Campo titulo="Ala / unidade" valor={relatorio.ala_unidade} /><Campo titulo="Quartos visitados" valor={relatorio.quartos_visitados} /><Campo titulo="Pessoas impactadas" valor={relatorio.pessoas_impactadas} /><Campo titulo="Observação sobre visitantes externos" valor={relatorio.observacao_visitantes_externos} /><Campo titulo="Observações gerais" valor={relatorio.observacoes_gerais} /></dl><div className="mt-8"><Link href={`/visitas/${visita.id}/relatorios`} className="rounded-full border px-5 py-3">Voltar aos relatórios</Link></div></div></section></PainelLayout>
)

export default Show
