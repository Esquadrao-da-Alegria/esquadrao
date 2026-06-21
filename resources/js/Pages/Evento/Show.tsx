import PainelLayout from '@/layouts/PainelLayout'
import { router } from '@inertiajs/react'
import { ArrowLeft, ExternalLink, MapPin, Navigation } from 'lucide-react'
import React from 'react'

interface Props {
  evento: {
    id: number
    titulo: string
    tipo?: string
    descricao?: string
    data_inicio?: string
    data_fim?: string
    complemento?: string
    local_latitude?: number | null
    local_longitude?: number | null
    status?: string
    limite_vagas?: number | null
    feedback_habilitado?: boolean
    cidade?: { nome: string }
    criadoPor?: { name: string }
    responsaveis?: Array<{ voluntario: { name: string } }>
    participantes?: any[]
  }
  inscrito: boolean
  podeEditar: boolean
  passouInicio: boolean
}

const Show: React.FC<Props> = ({ evento, inscrito, podeEditar, passouInicio }) => {
  const formatDateTime = (value?: string) => {
    if (!value) return '-'
    return new Date(value).toLocaleString('pt-BR', {
      day: '2-digit', month: '2-digit', year: 'numeric',
      hour: '2-digit', minute: '2-digit',
    })
  }

  const inscritos = evento.participantes?.filter(p => p.status === 'INSCRITO').length ?? 0

  const googleMapsUrl = evento.local_latitude && evento.local_longitude
    ? `https://maps.google.com/?q=${evento.local_latitude},${evento.local_longitude}`
    : null

  const wazeUrl = evento.local_latitude && evento.local_longitude
    ? `https://waze.com/ul?ll=${evento.local_latitude},${evento.local_longitude}&navigate=yes`
    : null

  const statusColor: Record<string, string> = {
    AGENDADO: 'bg-green-100 text-green-800',
    FINALIZADO: 'bg-gray-100 text-gray-700',
    CANCELADO: 'bg-red-100 text-red-800',
  }

  return (
    <PainelLayout>
      <div className="mx-auto max-w-3xl p-6">
        <button
          onClick={() => router.visit('/eventos')}
          className="mb-6 inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700"
        >
          <ArrowLeft size={16} /> Voltar para eventos
        </button>

        <div className="overflow-hidden rounded-3xl border bg-white shadow-sm">
          <div className="p-8">
            {/* Cabeçalho */}
            <div className="mb-6 flex items-start justify-between gap-4">
              <h1 className="text-3xl font-bold text-amber-800">{evento.titulo}</h1>
              <span className={`shrink-0 rounded-full px-3 py-1 text-xs font-semibold ${statusColor[evento.status ?? ''] ?? 'bg-gray-100 text-gray-700'}`}>
                {evento.status}
              </span>
            </div>

            {/* Tipo e Cidade */}
            <div className="mb-6 grid gap-4 sm:grid-cols-2">
              <div>
                <p className="text-xs font-medium uppercase text-gray-400">Tipo</p>
                <p className="mt-1 font-medium text-gray-700">{evento.tipo ?? '-'}</p>
              </div>
              <div>
                <p className="text-xs font-medium uppercase text-gray-400">Cidade</p>
                <p className="mt-1 font-medium text-gray-700">{evento.cidade?.nome ?? '-'}</p>
              </div>
              <div>
                <p className="text-xs font-medium uppercase text-gray-400">Início</p>
                <p className="mt-1 font-medium text-gray-700">{formatDateTime(evento.data_inicio)}</p>
              </div>
              <div>
                <p className="text-xs font-medium uppercase text-gray-400">Término</p>
                <p className="mt-1 font-medium text-gray-700">{formatDateTime(evento.data_fim)}</p>
              </div>
              <div>
                <p className="text-xs font-medium uppercase text-gray-400">Vagas</p>
                <p className="mt-1 font-medium text-gray-700">
                  {evento.limite_vagas != null ? `${inscritos}/${evento.limite_vagas}` : `${inscritos} inscritos (sem limite)`}
                </p>
              </div>
              <div>
                <p className="text-xs font-medium uppercase text-gray-400">Criado por</p>
                <p className="mt-1 font-medium text-gray-700">{evento.criadoPor?.name ?? '-'}</p>
              </div>
            </div>

            {/* Descrição */}
            {evento.descricao && (
              <div className="mb-6">
                <p className="text-xs font-medium uppercase text-gray-400">Descrição</p>
                <p className="mt-1 text-gray-700 leading-relaxed">{evento.descricao}</p>
              </div>
            )}

            {/* Localização */}
            <div className="mb-6">
              <p className="text-xs font-medium uppercase text-gray-400">Local</p>
              {evento.complemento
                ? (
                  <div className="mt-2 flex items-start gap-2 rounded-xl bg-amber-50 p-4">
                    <MapPin size={16} className="mt-0.5 shrink-0 text-amber-600" />
                    <p className="text-sm text-amber-900">{evento.complemento}</p>
                  </div>
                )
                : <p className="mt-1 font-medium text-gray-400">-</p>
              }
              {(googleMapsUrl || wazeUrl) && (
                <div className="mt-2 flex gap-3">
                  {googleMapsUrl && (
                    <a
                      href={googleMapsUrl}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-1 rounded-full border border-blue-300 bg-white px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50"
                    >
                      <ExternalLink size={14} /> Google Maps
                    </a>
                  )}
                  {wazeUrl && (
                    <a
                      href={wazeUrl}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-1 rounded-full border border-cyan-300 bg-white px-4 py-2 text-sm font-medium text-cyan-700 hover:bg-cyan-50"
                    >
                      <Navigation size={14} /> Waze
                    </a>
                  )}
                </div>
              )}
            </div>

            {/* Responsáveis */}
            <div className="mb-6">
              <p className="text-xs font-medium uppercase text-gray-400">Responsáveis</p>
              {(() => {
                const nomes: string[] = []
                if (evento.criadoPor?.name) nomes.push(evento.criadoPor.name)
                evento.responsaveis?.forEach(r => {
                  if (r.voluntario?.name && !nomes.includes(r.voluntario.name)) {
                    nomes.push(r.voluntario.name)
                  }
                })
                return nomes.length > 0
                  ? (
                    <ul className="mt-1 space-y-1">
                      {nomes.map((nome, i) => (
                        <li key={i} className="text-sm text-gray-700">{nome}</li>
                      ))}
                    </ul>
                  )
                  : <p className="mt-1 font-medium text-gray-400">-</p>
              })()}
            </div>
          </div>

          {/* Ações de inscrição */}
          {evento.status === 'AGENDADO' && (
            <div className="border-t bg-gray-50 px-8 py-5 flex gap-3">
              {!inscrito && !passouInicio && (
                <button
                  onClick={() => router.post(`/eventos/${evento.id}/inscrever`)}
                  className="rounded-full bg-green-600 px-5 py-2 text-sm font-semibold text-white hover:bg-green-700"
                >
                  Inscrever-se
                </button>
              )}
              {inscrito && (
                <button
                  onClick={() => router.delete(`/eventos/${evento.id}/inscricao`)}
                  className="rounded-full border border-red-300 bg-white px-5 py-2 text-sm font-semibold text-red-600 hover:bg-red-50"
                >
                  Cancelar inscrição
                </button>
              )}
              {podeEditar && (
                <button
                  onClick={() => router.visit(`/eventos/${evento.id}/edit`)}
                  className="rounded-full border border-gray-300 bg-white px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                  Editar
                </button>
              )}
            </div>
          )}
        </div>
      </div>
    </PainelLayout>
  )
}

export default Show
