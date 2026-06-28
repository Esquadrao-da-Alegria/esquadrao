import PainelLayout from '@/layouts/PainelLayout'
import { create } from '@/routes/eventos'
import { type Evento, type SharedData } from '@/types'
import { Link, usePage } from '@inertiajs/react'
import { Plus } from 'lucide-react'

interface Props { eventos: Evento[] }
const fmt = (v: string) => new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(v))

export default function Index({ eventos }: Props) {
  const { props } = usePage<SharedData>()
  return <PainelLayout><div className="mx-auto max-w-6xl p-6 space-y-6">
    <div className="flex items-center justify-between"><h1 className="text-2xl font-bold">Eventos</h1>{props.eh_administrador ? <Link href={create().url} className="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 text-sm font-semibold text-amber-700 shadow-sm transition hover:bg-amber-50 focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:outline-none sm:w-auto"><Plus className="size-5" strokeWidth={2} aria-hidden />Novo evento</Link> : null}</div>
    <div className="overflow-hidden rounded-xl border bg-white shadow-sm"><table className="w-full text-sm"><thead className="bg-gray-50 text-left"><tr><th className="p-3">Título</th><th>Tipo</th><th>Data/hora</th><th>Local</th><th>Status</th><th>Vagas</th><th></th></tr></thead><tbody>{eventos.map((e) => <tr key={e.id} className="border-t"><td className="p-3 font-medium">{e.titulo}</td><td>{e.tipo}</td><td>{fmt(e.data_inicio)}</td><td>{e.local ?? '-'}</td><td>{e.status}</td><td>{e.participantes_ativos_count ?? 0}/{e.limite_participantes ?? '∞'}</td><td><Link className="text-amber-700 underline" href={`/eventos/${e.id}`}>Detalhes</Link></td></tr>)}</tbody></table></div>
  </div></PainelLayout>
}
