import PainelLayout from '@/layouts/PainelLayout'
import { type Evento, type SharedData } from '@/types'
import { Link, usePage } from '@inertiajs/react'
import { CalendarDays, MapPin, Plus, Settings, Users } from 'lucide-react'

interface Props { abertas: Evento[]; encerradas: Evento[]; demais: Evento[] }

const fmt = (v: string) => new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(v))

const badgeStatus: Record<string, string> = {
  agendado: 'border-amber-200/80 bg-white text-amber-800',
  cancelado: 'border-red-200/80 bg-red-50 text-red-700',
  finalizado: 'border-gray-200/80 bg-gray-50 text-gray-600',
}

const labelTipo: Record<string, string> = {
  oficina: 'Oficina',
  reuniao: 'Reunião',
}

function vagasLabel(count: number, limite: number | null): string {
  if (limite === null) return `${count} inscritos`
  const livres = limite - count
  return `${count} de ${limite} inscritos · ${livres} ${livres === 1 ? 'vaga' : 'vagas'} disponível`
}

function EventoCard({ e, admin, subdued }: { e: Evento; admin: boolean; subdued?: boolean }) {
  const cidadeLocal = [e.cidade?.nome, e.local].filter(Boolean).join(' — ')
  return (
    <li>
      <article className={`w-full overflow-hidden rounded-2xl border bg-white shadow-sm transition duration-300 ${subdued ? 'border-gray-100 opacity-75 hover:opacity-100 hover:border-gray-200 hover:shadow' : 'border-amber-100 hover:border-amber-200 hover:shadow-md'}`}>
        <div className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:gap-6 sm:p-6">
          <div className="min-w-0 flex-1">
            <div className="flex items-center gap-2 flex-wrap">
              <h2 className={`text-base font-semibold leading-snug sm:text-lg ${subdued ? 'text-gray-600' : 'text-amber-950'}`}>{e.titulo}</h2>
              <span className={`inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-medium uppercase tracking-wide ${badgeStatus[e.status] ?? ''}`}>
                {e.status}
              </span>
              <span className={`inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-medium uppercase tracking-wide ${subdued ? 'border-gray-200 bg-gray-50 text-gray-500' : 'border-amber-100 bg-amber-50 text-amber-700'}`}>
                {labelTipo[e.tipo] ?? e.tipo}
              </span>
            </div>
            <div className={`mt-2 flex flex-wrap gap-4 text-sm ${subdued ? 'text-gray-400' : 'text-amber-900/55'}`}>
              <span className="flex items-center gap-1.5">
                <CalendarDays className="size-3.5" strokeWidth={2} aria-hidden />
                {fmt(e.data_inicio)}
              </span>
              {cidadeLocal && (
                <span className="flex items-center gap-1.5">
                  <MapPin className="size-3.5" strokeWidth={2} aria-hidden />
                  {cidadeLocal}
                </span>
              )}
              <span className="flex items-center gap-1.5">
                <Users className="size-3.5" strokeWidth={2} aria-hidden />
                {vagasLabel(e.participantes_ativos_count ?? 0, e.limite_participantes)}
              </span>
            </div>
            {!subdued && e.limite_inscricao && (
              <p className="mt-1.5 text-xs text-amber-700/60">
                Inscrições até {fmt(e.limite_inscricao)}
              </p>
            )}
          </div>
          <div className={`flex shrink-0 items-center gap-2 border-t pt-4 sm:border-t-0 sm:border-l sm:pl-6 sm:pt-0 ${subdued ? 'border-gray-100' : 'border-amber-50'}`}>
            <Link
              href={`/eventos/${e.id}`}
              className={`cursor-pointer inline-flex items-center justify-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition ${subdued ? 'border-gray-200 text-gray-500 hover:bg-gray-50' : 'border-amber-200 text-amber-700 hover:bg-amber-50'}`}
            >
              {admin && <Settings size={14} strokeWidth={1.75} aria-hidden />}
              Ver detalhes
            </Link>
          </div>
        </div>
      </article>
    </li>
  )
}

export default function Index({ abertas, encerradas, demais }: Props) {
  const { props } = usePage<SharedData>()
  const admin = !!props.eh_administrador
  const total = abertas.length + encerradas.length + demais.length

  return (
    <PainelLayout>
      <div className="mx-auto max-w-7xl px-5 py-8 sm:px-6 lg:px-8">
        <header className="mb-10 flex flex-col gap-4 sm:mb-12 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <h1 className="text-2xl font-semibold tracking-tight text-amber-950 sm:text-3xl">Eventos</h1>
            <p className="mt-1 max-w-md text-sm text-amber-900/55">
              {total === 0 ? 'Nenhum evento cadastrado ainda.' : `${total} ${total === 1 ? 'evento' : 'eventos'}`}
            </p>
          </div>
          <div className="flex gap-3 flex-wrap">
            <Link
              href="/meus-eventos"
              className="inline-flex cursor-pointer w-full shrink-0 items-center justify-center gap-2 rounded-full border border-amber-300 bg-white px-5 py-3 text-sm font-semibold text-amber-700 shadow-sm transition hover:bg-amber-50 sm:w-auto"
            >
              Meus eventos
            </Link>
            {admin && (
              <Link
                href="/eventos/create"
                className="inline-flex cursor-pointer w-full shrink-0 items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 text-sm font-semibold text-amber-700 shadow-sm transition hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 sm:w-auto"
              >
                <Plus className="size-5" strokeWidth={2} aria-hidden />
                Novo evento
              </Link>
            )}
          </div>
        </header>

        {total === 0 ? (
          <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-amber-200 bg-white px-6 py-20 text-center">
            <p className="text-sm text-amber-900/50">Nenhum evento na lista.</p>
            {admin && (
              <Link
                href="/eventos/create"
                className="mt-6 inline-flex cursor-pointer items-center gap-2 rounded-full border-2 border-amber-600 bg-white px-5 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-50"
              >
                <Plus className="size-4" aria-hidden />
                Criar evento
              </Link>
            )}
          </div>
        ) : (
          <div className="space-y-12">
            {abertas.length > 0 && (
              <section>
                <h2 className="mb-5 text-sm font-semibold uppercase tracking-widest text-amber-700">
                  Inscrições abertas
                </h2>
                <ul className="flex w-full flex-col gap-5">
                  {abertas.map((e) => (
                    <EventoCard key={e.id} e={e} admin={admin} />
                  ))}
                </ul>
              </section>
            )}

            {encerradas.length > 0 && (
              <section>
                <h2 className="mb-5 text-sm font-semibold uppercase tracking-widest text-orange-500">
                  Inscrições encerradas
                </h2>
                <p className="mb-4 -mt-2 text-xs text-orange-400/80">O evento ainda vai acontecer, mas não aceita mais inscrições.</p>
                <ul className="flex w-full flex-col gap-4">
                  {encerradas.map((e) => (
                    <EventoCard key={e.id} e={e} admin={admin} subdued />
                  ))}
                </ul>
              </section>
            )}

            {demais.length > 0 && (
              <section>
                <h2 className="mb-5 text-sm font-semibold uppercase tracking-widest text-gray-400">
                  Encerrados e finalizados
                </h2>
                <ul className="flex w-full flex-col gap-4">
                  {demais.map((e) => (
                    <EventoCard key={e.id} e={e} admin={admin} subdued />
                  ))}
                </ul>
              </section>
            )}
          </div>
        )}
      </div>
    </PainelLayout>
  )
}