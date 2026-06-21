import PainelLayout from '@/layouts/PainelLayout'
import { router, useForm } from '@inertiajs/react'
import { ArrowLeft, CheckSquare, XSquare } from 'lucide-react'
import React from 'react'

interface Participante {
  id: number
  user_id: number
  status: string
  usuario: { name: string }
}

interface Props {
  evento: {
    id: number
    titulo: string
    data_inicio?: string
    data_fim?: string
  }
  participantes: Participante[]
}

const Finalizar: React.FC<Props> = ({ evento, participantes }) => {
  const inscritos = participantes.filter(p => p.status === 'INSCRITO' || p.status === 'PRESENTE' || p.status === 'AUSENTE')

  const { data, setData, post, processing } = useForm<{
    presencas: { user_id: number; status: string }[]
  }>({
    presencas: inscritos.map(p => ({ user_id: p.user_id, status: 'PRESENTE' })),
  })

  const toggleStatus = (userId: number) => {
    setData('presencas', data.presencas.map(p =>
      p.user_id === userId
        ? { ...p, status: p.status === 'PRESENTE' ? 'AUSENTE' : 'PRESENTE' }
        : p,
    ))
  }

  const getStatus = (userId: number) =>
    data.presencas.find(p => p.user_id === userId)?.status ?? 'PRESENTE'

  const formatDateTime = (value?: string) => {
    if (!value) return '-'
    return new Date(value).toLocaleString('pt-BR', {
      day: '2-digit', month: '2-digit', year: 'numeric',
      hour: '2-digit', minute: '2-digit',
    })
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    post(`/eventos/${evento.id}/finalizar`)
  }

  return (
    <PainelLayout>
      <div className="mx-auto max-w-2xl p-6">
        <button
          onClick={() => router.visit(`/eventos`)}
          className="mb-6 inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700"
        >
          <ArrowLeft size={16} /> Voltar
        </button>

        <div className="overflow-hidden rounded-3xl border bg-white shadow-sm">
          <div className="p-8">
            <h1 className="mb-2 text-2xl font-bold text-amber-800">Finalizar evento</h1>
            <p className="mb-1 text-sm text-gray-500">{evento.titulo}</p>
            <p className="mb-6 text-xs text-gray-400">
              {formatDateTime(evento.data_inicio)} → {formatDateTime(evento.data_fim)}
            </p>

            {inscritos.length === 0 ? (
              <p className="text-sm text-gray-500">Nenhum participante inscrito neste evento.</p>
            ) : (
              <form onSubmit={handleSubmit}>
                <p className="mb-4 text-sm font-medium text-gray-600">
                  Marque a presença de cada participante:
                </p>

                <ul className="mb-6 divide-y rounded-xl border">
                  {inscritos.map(p => {
                    const status = getStatus(p.user_id)
                    const presente = status === 'PRESENTE'

                    return (
                      <li key={p.user_id} className="flex items-center justify-between px-5 py-4">
                        <span className="font-medium text-gray-700">{p.usuario?.name}</span>
                        <button
                          type="button"
                          onClick={() => toggleStatus(p.user_id)}
                          className={`inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-semibold transition ${
                            presente
                              ? 'bg-green-100 text-green-700 hover:bg-green-200'
                              : 'bg-red-100 text-red-700 hover:bg-red-200'
                          }`}
                        >
                          {presente
                            ? <><CheckSquare size={14} /> Presente</>
                            : <><XSquare size={14} /> Ausente</>
                          }
                        </button>
                      </li>
                    )
                  })}
                </ul>

                <div className="flex justify-end gap-3">
                  <button
                    type="button"
                    onClick={() => router.visit('/eventos')}
                    className="rounded-full border border-gray-300 px-5 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50"
                  >
                    Cancelar
                  </button>
                  <button
                    type="submit"
                    disabled={processing}
                    className="rounded-full bg-amber-600 px-5 py-2 text-sm font-semibold text-white hover:bg-amber-700 disabled:opacity-70"
                  >
                    {processing ? 'Finalizando...' : 'Confirmar e finalizar'}
                  </button>
                </div>
              </form>
            )}
          </div>
        </div>
      </div>
    </PainelLayout>
  )
}

export default Finalizar
