import PainelLayout from '@/layouts/PainelLayout'
import { type Evento, type SharedData } from '@/types'
import { Link, router, usePage } from '@inertiajs/react'
import { useState } from 'react'

interface Props { evento: Evento; inscrito: boolean }
const fmt = (v?: string | null) => v ? new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(v)) : '-'

export default function Show({ evento, inscrito }: Props) {
  const { props } = usePage<SharedData>()
  const [motivo, setMotivo] = useState('')
  const vagas = `${evento.participantes_ativos_count ?? 0}/${evento.limite_participantes ?? '∞'}`
  return <PainelLayout><div className="mx-auto max-w-4xl p-6 space-y-6">
    <Link href="/eventos" className="text-sm text-amber-700 underline">← Voltar</Link>
    <div className="rounded-xl border bg-white p-6 shadow-sm space-y-4"><div className="flex justify-between gap-4"><div><h1 className="text-2xl font-bold">{evento.titulo}</h1><p className="text-gray-500">{evento.tipo} • {evento.status}</p></div><div className="flex gap-2">{props.eh_administrador ? <Link className="rounded border px-3 py-2" href={`/eventos/${evento.id}/edit`}>Editar</Link> : null}{!inscrito ? <button className="rounded bg-amber-500 px-3 py-2 text-white" onClick={() => router.post(`/eventos/${evento.id}/inscricao`)}>Participar</button> : <button className="rounded bg-gray-700 px-3 py-2 text-white" onClick={() => router.delete(`/eventos/${evento.id}/inscricao`)}>Cancelar minha inscrição</button>}</div></div>
      <p>{evento.descricao ?? 'Sem descrição.'}</p><dl className="grid gap-3 sm:grid-cols-2"><div><dt className="font-medium">Local</dt><dd>{evento.local ?? '-'}</dd></div><div><dt className="font-medium">Início</dt><dd>{fmt(evento.data_inicio)}</dd></div><div><dt className="font-medium">Fim</dt><dd>{fmt(evento.data_fim)}</dd></div><div><dt className="font-medium">Responsável</dt><dd>{evento.responsavel?.name ?? '-'}</dd></div><div><dt className="font-medium">Vagas</dt><dd>{vagas}</dd></div></dl>
      {props.eh_administrador ? <div className="border-t pt-4"><label className="block text-sm font-medium">Motivo do cancelamento</label><textarea className="mt-1 w-full rounded border p-2" value={motivo} onChange={(e) => setMotivo(e.target.value)} /><button className="mt-2 rounded bg-red-600 px-3 py-2 text-white" onClick={() => router.post(`/eventos/${evento.id}/cancelar`, { motivo_cancelamento: motivo })}>Cancelar evento</button></div> : null}</div>
    <section className="rounded-xl border bg-white p-6"><h2 className="font-bold">Participantes inscritos</h2><ul className="mt-3 list-disc pl-5">{(evento.participantes_ativos ?? []).map((p) => <li key={p.id}>{p.name} ({p.email})</li>)}</ul></section>
  </div></PainelLayout>
}
