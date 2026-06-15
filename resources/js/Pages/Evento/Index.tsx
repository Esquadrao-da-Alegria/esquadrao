import PainelLayout from '@/layouts/PainelLayout'
import { router, useForm } from '@inertiajs/react'
import { CheckSquare, Eye, Pencil, UserPlus, XCircle } from 'lucide-react'
import React from 'react'
import Swal from 'sweetalert2'

interface Evento {
  id: number
  titulo: string
  tipo?: string
  data_inicio?: string
  data_fim?: string
  cidade?: { nome: string }
  status?: string
  limite_vagas?: number | null
  total_inscritos?: number
  inscrito?: boolean
  pode_editar?: boolean
}

interface Filtros {
  tipo?: string
  data?: string
  hora_inicio?: string
  hora_fim?: string
  minha_relacao?: string
}

interface Props {
  eventos: Evento[]
  filtros?: Filtros
}

const Index: React.FC<Props> = ({ eventos, filtros }) => {
  const { data, setData, get } = useForm({
    tipo:          filtros?.tipo ?? '',
    data:          filtros?.data ?? '',
    hora_inicio:   filtros?.hora_inicio ?? '',
    hora_fim:      filtros?.hora_fim ?? '',
    minha_relacao: filtros?.minha_relacao ?? '',
  })

  const formatDate = (value?: string) => {
    if (!value) return '-'
    return new Date(value).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' })
  }

  const formatTime = (value?: string) => {
    if (!value) return '-'
    return new Date(value).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
  }

  const aplicarFiltros = () => {
    get('/eventos', { preserveState: true, replace: true })
  }

  const handleInscrever = (id: number) => {
    router.post(`/eventos/${id}/inscrever`)
  }

  const handleCancelarEvento = (id: number) => {
    Swal.fire({
      title: 'Cancelar evento?',
      text: 'Esta ação não pode ser desfeita.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sim, cancelar',
      cancelButtonText: 'Voltar',
      confirmButtonColor: '#dc2626',
    }).then((result) => {
      if (result.isConfirmed) {
        router.post(`/eventos/${id}/cancelar`)
      }
    })
  }

  const inscritos = (evento: Evento) => evento.total_inscritos ?? 0

  return (
    <PainelLayout>
      <div className="mx-auto max-w-6xl p-6">
        <div className="mb-6 flex items-center justify-between">
          <h1 className="text-2xl font-extrabold">Eventos</h1>
          <div className="flex gap-2">
            <button
              onClick={() => router.visit('/eventos/dashboard')}
              className="rounded-full border border-amber-600 px-4 py-2 text-sm font-semibold text-amber-700 shadow-sm hover:bg-amber-50"
            >
              Dashboard
            </button>
            <button
              onClick={() => router.visit('/eventos/create')}
              className="rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700"
            >
              Novo
            </button>
          </div>
        </div>

        {/* Filtros */}
        <div className="mb-4 flex flex-wrap gap-3">
          <select
            value={data.tipo}
            onChange={(e) => setData('tipo', e.target.value)}
            className="rounded-md border border-gray-200 px-3 py-2 text-sm"
          >
            <option value="">Todos os tipos</option>
            <option value="OFICINA">Oficina</option>
            <option value="REUNIAO">Reunião</option>
          </select>

          <input
            type="date"
            value={data.data}
            onChange={(e) => setData('data', e.target.value)}
            className="rounded-md border border-gray-200 px-3 py-2 text-sm"
          />

          <div className="flex items-center gap-1">
            <span className="text-sm text-gray-500">Começa em</span>
            <input
              type="time"
              value={data.hora_inicio}
              onChange={(e) => setData('hora_inicio', e.target.value)}
              className="rounded-md border border-gray-200 px-3 py-2 text-sm"
            />
          </div>

          <div className="flex items-center gap-1">
            <span className="text-sm text-gray-500">Termina em</span>
            <input
              type="time"
              value={data.hora_fim}
              onChange={(e) => setData('hora_fim', e.target.value)}
              className="rounded-md border border-gray-200 px-3 py-2 text-sm"
            />
          </div>

          <select
            value={data.minha_relacao}
            onChange={(e) => setData('minha_relacao', e.target.value)}
            className="rounded-md border border-gray-200 px-3 py-2 text-sm"
          >
            <option value="">Todos os eventos</option>
            <option value="responsavel">Meus eventos</option>
            <option value="inscrito">Eventos inscritos</option>
          </select>

          <button
            onClick={aplicarFiltros}
            className="rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700"
          >
            Filtrar
          </button>
        </div>

        {/* Tabela */}
        <div className="overflow-hidden rounded-2xl shadow-md ring-1 ring-gray-200 bg-white">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Início</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fim</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cidade</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vagas</th>
                <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ações</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100 bg-white">
              {eventos.map((evento) => (
                <tr key={evento.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 text-sm text-gray-700 font-medium">{evento.titulo}</td>
                  <td className="px-6 py-4 text-sm text-gray-600">{formatDate(evento.data_inicio)}</td>
                  <td className="px-6 py-4 text-sm text-gray-600">{formatTime(evento.data_inicio)}</td>
                  <td className="px-6 py-4 text-sm text-gray-600">{formatTime(evento.data_fim)}</td>
                  <td className="px-6 py-4 text-sm text-gray-600">{evento.cidade?.nome ?? '-'}</td>
                  <td className="px-6 py-4 text-sm text-gray-600">{evento.tipo ?? '-'}</td>
                  <td className="px-6 py-4 text-sm text-gray-600">
                    {evento.limite_vagas != null
                      ? `${inscritos(evento)}/${evento.limite_vagas}`
                      : `${inscritos(evento)} inscritos`}
                  </td>
                  <td className="px-6 py-4">
                    <div className="flex items-center justify-center gap-1">
                      <button
                        onClick={() => router.visit(`/eventos/${evento.id}`)}
                        title="Ver detalhes"
                        className="p-2 text-blue-500 hover:bg-blue-50 rounded-md"
                      >
                        <Eye size={16} />
                      </button>

                      {!evento.inscrito && (
                        <button
                          onClick={() => handleInscrever(evento.id)}
                          title="Inscrever-se"
                          className="p-2 text-green-600 hover:bg-green-50 rounded-md"
                        >
                          <UserPlus size={16} />
                        </button>
                      )}

                      {!!evento.pode_editar && (
                        <button
                          onClick={() => router.visit(`/eventos/${evento.id}/edit`)}
                          title="Editar"
                          className="p-2 text-gray-500 hover:bg-gray-100 rounded-md"
                        >
                          <Pencil size={16} />  
                        </button>
                      )}

                      {!!evento.pode_editar && (
                        <button
                          onClick={() => router.visit(`/eventos/${evento.id}/finalizar`)}
                          title="Finalizar evento"
                          className="p-2 text-amber-600 hover:bg-amber-50 rounded-md"
                        >
                          <CheckSquare size={16} />
                        </button>
                      )}

                      {!!evento.pode_editar && (
                        <button
                          onClick={() => handleCancelarEvento(evento.id)}
                          title="Cancelar evento"
                          className="p-2 text-red-500 hover:bg-red-50 rounded-md"
                        >
                          <XCircle size={16} />
                        </button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}

              {eventos.length === 0 && (
                <tr>
                  <td colSpan={8} className="py-8 text-center text-gray-500">
                    Nenhum evento cadastrado.
                  </td>
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
