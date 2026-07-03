import PainelLayout from '@/layouts/PainelLayout'
import { type MeuEvento } from '@/types'
import { Link } from '@inertiajs/react'

interface Props { eventos: MeuEvento[] }

const fmt = (v?: string | null) =>
  v ? new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(v)) : '-'

const badgeStatus: Record<string, string> = {
  agendado: 'bg-blue-100 text-blue-800',
  cancelado: 'bg-red-100 text-red-800',
  finalizado: 'bg-gray-100 text-gray-800',
}

const badgeInscricao: Record<string, string> = {
  inscrito: 'bg-green-100 text-green-800',
  cancelado: 'bg-red-100 text-red-800',
}

const badgePresenca: Record<string, string> = {
  presente: 'bg-green-100 text-green-800',
  ausente: 'bg-red-100 text-red-800',
}

function EventoCard({ evento }: { evento: MeuEvento }) {
  return (
    <div className="rounded-xl border bg-white p-4 shadow-sm space-y-2">
      <div className="flex justify-between items-start gap-2 flex-wrap">
        <div>
          <Link href={`/eventos/${evento.id}`} className="font-semibold text-amber-700 hover:underline">
            {evento.titulo}
          </Link>
          <p className="text-sm text-gray-500">{evento.tipo} • {evento.local ?? 'Sem local'}</p>
        </div>
        <div className="flex gap-1 flex-wrap">
          <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${badgeStatus[evento.status] ?? ''}`}>
            {evento.status}
          </span>
          <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${badgeInscricao[evento.inscricao_status] ?? ''}`}>
            {evento.inscricao_status}
          </span>
          {evento.presenca && (
            <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${badgePresenca[evento.presenca] ?? ''}`}>
              {evento.presenca}
            </span>
          )}
        </div>
      </div>
      <div className="text-sm text-gray-600 flex gap-4">
        <span>Início: {fmt(evento.data_inicio)}</span>
        {evento.data_fim && <span>Fim: {fmt(evento.data_fim)}</span>}
      </div>
    </div>
  )
}

export default function MeusEventos({ eventos }: Props) {
  const agora = new Date()
  const proximos = eventos.filter((e) => new Date(e.data_inicio) >= agora && e.inscricao_status === 'inscrito')
  const passados = eventos.filter((e) => new Date(e.data_inicio) < agora || e.inscricao_status === 'cancelado')

  return (
    <PainelLayout>
      <div className="mx-auto max-w-3xl p-6 space-y-8">
        <div className="flex items-center justify-between">
          <h1 className="text-2xl font-bold">Meus eventos</h1>
          <Link href="/eventos" className="text-sm text-amber-700 underline">Ver todos os eventos</Link>
        </div>

        <section className="space-y-3">
          <h2 className="font-semibold text-gray-700">Próximos eventos</h2>
          {proximos.length === 0 ? (
            <p className="text-gray-500 text-sm">Você não possui eventos futuros.</p>
          ) : (
            proximos.map((e) => <EventoCard key={`${e.id}-${e.inscricao_status}`} evento={e} />)
          )}
        </section>

        <section className="space-y-3">
          <h2 className="font-semibold text-gray-700">Eventos passados e finalizados</h2>
          {passados.length === 0 ? (
            <p className="text-gray-500 text-sm">Nenhum evento passado encontrado.</p>
          ) : (
            passados.map((e) => <EventoCard key={`${e.id}-${e.inscricao_status}`} evento={e} />)
          )}
        </section>
      </div>
    </PainelLayout>
  )
}