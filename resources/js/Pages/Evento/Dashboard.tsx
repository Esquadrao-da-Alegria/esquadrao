import PainelLayout from '@/layouts/PainelLayout'
import { router, useForm } from '@inertiajs/react'
import { ArrowLeft, Search } from 'lucide-react'
import React from 'react'

interface Participante {
  id: number
  name: string
  total_presencas: number
}

interface Props {
  participantes: Participante[]
  semestres: string[]
  filtros: { semestre?: string; nome?: string }
}

const Dashboard: React.FC<Props> = ({ participantes, semestres, filtros }) => {
  const { data, setData, get } = useForm({
    semestre: filtros.semestre ?? '',
    nome: filtros.nome ?? '',
  })

  const aplicarFiltros = () => {
    get('/eventos/dashboard', { preserveState: true, replace: true })
  }

  const limparFiltros = () => {
    router.visit('/eventos/dashboard')
  }

  return (
    <PainelLayout>
      <section className="mx-auto w-full max-w-5xl px-4 py-10">
        <div className="mb-6 flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-amber-800">Dashboard de participação</h1>
            <p className="mt-1 text-sm text-gray-500">Presenças confirmadas por voluntário</p>
          </div>
          <button
            onClick={() => router.visit('/eventos')}
            className="inline-flex items-center gap-2 rounded-full border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50"
          >
            <ArrowLeft size={14} /> Voltar para eventos
          </button>
        </div>

        {/* Filtros */}
        <div className="mb-6 flex flex-wrap gap-3 rounded-2xl border bg-white p-4">
          {semestres.length > 0 && (
            <select
              value={data.semestre}
              onChange={(e) => setData('semestre', e.target.value)}
              className="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500"
            >
              <option value="">Todos os semestres</option>
              {semestres.map(s => (
                <option key={s} value={s}>{s}</option>
              ))}
            </select>
          )}

          <div className="flex flex-1 items-center gap-2">
            <input
              type="text"
              value={data.nome}
              onChange={(e) => setData('nome', e.target.value)}
              onKeyDown={(e) => e.key === 'Enter' && aplicarFiltros()}
              placeholder="Buscar por nome..."
              className="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500"
            />
            <button
              onClick={aplicarFiltros}
              className="inline-flex items-center gap-1 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700"
            >
              <Search size={14} /> Filtrar
            </button>
            {(data.semestre || data.nome) && (
              <button
                onClick={limparFiltros}
                className="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50"
              >
                Limpar
              </button>
            )}
          </div>
        </div>

        {/* Tabela */}
        {participantes.length === 0 ? (
          <div className="rounded-2xl border bg-white p-10 text-center text-gray-400">
            Nenhum registro encontrado para os filtros selecionados.
          </div>
        ) : (
          <div className="overflow-hidden rounded-2xl border bg-white shadow-sm">
            <table className="w-full text-sm">
              <thead className="bg-gray-50 text-xs font-semibold uppercase text-gray-500">
                <tr>
                  <th className="px-6 py-3 text-left">#</th>
                  <th className="px-6 py-3 text-left">Voluntário</th>
                  <th className="px-6 py-3 text-right">Presenças</th>
                </tr>
              </thead>
              <tbody className="divide-y">
                {participantes.map((p, index) => (
                  <tr key={p.id} className="hover:bg-gray-50">
                    <td className="px-6 py-3 text-gray-400">{index + 1}</td>
                    <td className="px-6 py-3 font-medium text-gray-800">{p.name}</td>
                    <td className="px-6 py-3 text-right">
                      <span className={`inline-flex items-center justify-center rounded-full px-3 py-0.5 text-sm font-bold ${
                        p.total_presencas > 0
                          ? 'bg-amber-100 text-amber-800'
                          : 'bg-gray-100 text-gray-400'
                      }`}>
                        {p.total_presencas}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
              <tfoot className="bg-gray-50">
                <tr>
                  <td colSpan={2} className="px-6 py-3 text-xs font-semibold uppercase text-gray-500">Total de voluntários</td>
                  <td className="px-6 py-3 text-right font-bold text-gray-700">{participantes.length}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        )}
      </section>
    </PainelLayout>
  )
}

export default Dashboard
