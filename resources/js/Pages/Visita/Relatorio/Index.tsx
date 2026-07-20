import PainelLayout from '@/layouts/PainelLayout'
import type { Visita } from '@/types'
import { Link } from '@inertiajs/react'
import type { FC } from 'react'

interface Relatorio {
    id: number
    autor_id: number
    tipo_relatorio: 'artista' | 'paisana' | 'geral'
    resumo: string
    enviado_em: string
    fora_do_prazo: boolean
    autor?: { name: string }
}

interface Props { visita: Visita; relatorios: Relatorio[] }

const labels = { artista: 'Artista', paisana: 'Paisana', geral: 'Geral' }

const Index: FC<Props> = ({ visita, relatorios }) => (
    <PainelLayout>
        <section className="mx-auto max-w-5xl px-4 py-12">
            <div className="mb-8 flex items-center justify-between gap-4">
                <div><h1 className="text-3xl font-bold text-amber-800">Relatórios da visita</h1><p className="text-sm text-gray-500">{visita.hospital?.nome ?? 'Visita selecionada'}</p></div>
                <Link href={`/visitas/${visita.id}/relatorios/create`} className="rounded-full border-2 border-amber-600 px-5 py-3 font-semibold text-amber-700">Criar relatório</Link>
            </div>
            {relatorios.length === 0 ? <div className="rounded-2xl border border-dashed p-12 text-center text-gray-500">Nenhum relatório cadastrado.</div> : (
                <div className="space-y-4">{relatorios.map((relatorio) => <article key={relatorio.id} className="rounded-2xl border bg-white p-5"><div className="flex flex-wrap items-start justify-between gap-3"><div><div className="flex gap-2"><span className="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">{labels[relatorio.tipo_relatorio]}</span>{relatorio.fora_do_prazo && <span className="rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-800">Fora do prazo</span>}</div><p className="mt-3 text-sm text-gray-700">{relatorio.resumo.length > 140 ? `${relatorio.resumo.slice(0, 140)}…` : relatorio.resumo}</p><p className="mt-2 text-xs text-gray-500">{relatorio.autor?.name ?? '—'} · {new Date(relatorio.enviado_em).toLocaleString('pt-BR')}</p></div><div className="flex gap-2"><Link href={`/visitas/${visita.id}/relatorios/${relatorio.id}`} className="rounded-full border px-4 py-2 text-sm">Ver</Link><Link href={`/visitas/${visita.id}/relatorios/${relatorio.id}/edit`} className="rounded-full border px-4 py-2 text-sm">Editar</Link></div></div></article>)}</div>
            )}
        </section>
    </PainelLayout>
)

export default Index
