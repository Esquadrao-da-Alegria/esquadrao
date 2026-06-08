import PainelLayout from '@/layouts/PainelLayout'
import { Pencil } from 'lucide-react'
import { Link, router } from '@inertiajs/react'
import React from 'react'

interface Evento {
  id?: string
  titulo: string
  tipo?: string
  data_inicio?: string
  data_fim?: string
  cidade?: {
    nome: string
  }
  status?: string
}

interface Props {
  eventos: Evento[]
}

const Index: React.FC<Props> = ({ eventos }) => {
  const handleCriarClick = () => {
    router.visit('/eventos/create')
  }

  const handleEditarClick = (evento: Evento) => {
    router.visit(`/eventos/${evento.id}/edit`)
  }

  const formatDate = (value?: string) => {
    if (!value) return '-'
    return new Date(value).toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    })
  }

  const formatTime = (value?: string) => {
    if (!value) return '-'
    return new Date(value).toLocaleTimeString('pt-BR', {
      hour: '2-digit',
      minute: '2-digit',
    })
  }

  return (
    <PainelLayout>
      <div className="mx-auto max-w-6xl p-6">
        <div className="mb-6 flex items-center justify-between">
          <h1 className="text-2xl font-extrabold">Eventos</h1>

          <button
            onClick={handleCriarClick}
            type="button"
            className="rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm"
          >
            Novo
          </button>
        </div>

        <div className="overflow-hidden rounded-2xl shadow-md ring-1 ring-gray-200 bg-white">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora Início</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora Fim</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cidade</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Editar</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100 bg-white">
              {eventos.map((evento) => (
                <tr key={evento.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 text-sm text-gray-600">{evento.titulo}</td>
                  <td className="px-6 py-4 text-sm text-gray-600">{formatDate(evento.data_inicio)}</td>
                  <td className="px-6 py-4 text-sm text-gray-600">{formatTime(evento.data_inicio)}</td>
                  <td className="px-6 py-4 text-sm text-gray-600">{formatTime(evento.data_fim)}</td>
                  <td className="px-6 py-4 text-sm text-gray-600">{evento.cidade?.nome || '-'}</td>
                  <td className="px-6 py-4 text-sm text-gray-600">{evento.tipo || '-'}</td>
                  <td className="px-6 py-4 text-center">
                    <button onClick={() => handleEditarClick(evento)} className="p-2 text-gray-500 hover:bg-gray-100 rounded-md">
                      <Pencil size={16} />
                    </button>
                  </td>
                </tr>
              ))}

              {eventos.length === 0 && (
                <tr>
                  <td colSpan={5} className="py-8 text-center text-gray-500">Nenhum evento cadastrado.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </PainelLayout>
  )
}

export default Index
