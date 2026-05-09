import MarketingLayout from '@/layouts/MarketingLayout'
import { Pencil } from 'lucide-react'
import { Link, router } from '@inertiajs/react'
import React from 'react'

interface Patrocinador {
  id?: string
  nome: string
  site?: string
  categoria?: string
  logo_path?: string
  ativo: boolean
  ordem_exibicao: number
}

interface Props {
  patrocinadores: Patrocinador[]
}

const Index: React.FC<Props> = ({ patrocinadores }) => {
  const handleCriarClick = () => {
    router.visit('/patrocinadores/create')
  }

  const handleEditarClick = (patrocinador: Patrocinador) => {
    router.visit(`/patrocinadores/${patrocinador.id}/edit`)
  }

  return (
    <MarketingLayout>
      <div className="mx-auto max-w-6xl p-6">
        <div className="mb-6 flex items-center justify-between">
          <h1 className="bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 bg-clip-text text-3xl font-extrabold text-transparent">
            Patrocinadores Cadastrados
          </h1>

          <button
            onClick={handleCriarClick}
            type="button"
            className="rounded-full bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 px-6 py-2 text-sm font-semibold text-white shadow-md transition-all hover:scale-105 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-purple-400 focus:ring-offset-2"
          >
            Novo
          </button>
        </div>

        {/* Tabela responsiva */}
        <div className="mt-6 overflow-hidden rounded-2xl shadow-md ring-1 ring-gray-200">
          <table className="hidden min-w-full divide-y divide-gray-200 bg-white md:table">
            <thead className="bg-gradient-to-r from-pink-500 to-blue-500 text-white">
              <tr>
                <th className="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider w-20">
                  Ordem
                </th>
                <th className="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
                  Logotipo
                </th>
                <th className="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
                  Nome
                </th>
                <th className="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
                  Categoria
                </th>
                <th className="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
                  Status
                </th>
                <th className="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider">
                  Editar
                </th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {patrocinadores.map((patrocinador) => (
                <tr key={patrocinador.id} className="transition hover:bg-gray-50">
                  <td className="px-6 py-4 text-center text-sm font-bold text-gray-500">
                    {patrocinador.ordem_exibicao}
                  </td>
                  <td className="px-6 py-4">
                    {patrocinador.logo_path ? (
                      <img
                        src={patrocinador.logo_path}
                        alt={patrocinador.nome}
                        className="h-14 w-14 rounded-full object-cover shadow-sm ring-2 ring-pink-200"
                      />
                    ) : (
                      <div className="flex h-14 w-14 items-center justify-center rounded-full bg-pink-100 text-2xl shadow-sm ring-2 ring-pink-200">
                        🤝
                      </div>
                    )}
                  </td>
                  <td className="px-6 py-4 text-sm font-medium text-gray-900">
                    {patrocinador.nome}
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-600">
                    {patrocinador.categoria || '-'}
                  </td>
                  <td className="px-6 py-4">
                    <span
                      className={`inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${
                        patrocinador.ativo
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800'
                      }`}
                    >
                      {patrocinador.ativo ? 'Ativo' : 'Inativo'}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-center">
                    <button
                      onClick={() => handleEditarClick(patrocinador)}
                      type="button"
                      className="inline-flex items-center justify-center rounded-md p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700"
                      aria-label="Editar patrocinador"
                    >
                      <Pencil size={16} strokeWidth={1.5} />
                    </button>
                  </td>
                </tr>
              ))}
              
              {patrocinadores.length === 0 && (
                <tr>
                    <td colSpan={6} className="py-8 text-center text-gray-500">
                        Nenhum patrocinador cadastrado.
                    </td>
                </tr>
              )}
            </tbody>
          </table>

          {/* Versão mobile (cards) */}
          <div className="space-y-4 bg-white p-4 md:hidden">
            {patrocinadores.map((patrocinador) => (
              <div
                key={patrocinador.id}
                className="relative flex flex-col rounded-2xl border border-gray-100 bg-gradient-to-br from-pink-50 via-white to-blue-50 p-4 shadow-sm"
              >
                <div className="absolute top-4 right-4 text-xs font-bold text-gray-400">
                  Ordem: {patrocinador.ordem_exibicao}
                </div>
                <div className="flex items-center gap-4">
                  {patrocinador.logo_path ? (
                    <img
                      src={patrocinador.logo_path}
                      alt={patrocinador.nome}
                      className="h-14 w-14 rounded-full object-cover ring-2 ring-pink-200"
                    />
                  ) : (
                    <div className="flex h-14 w-14 items-center justify-center rounded-full bg-pink-100 text-2xl shadow-sm ring-2 ring-pink-200">
                      🤝
                    </div>
                  )}
                  <div className="flex-1 pr-12">
                    <h2 className="font-semibold text-gray-800">{patrocinador.nome}</h2>
                    <p className="text-sm text-gray-600">{patrocinador.categoria}</p>
                  </div>
                  <Link
                    href={`/patrocinadores/${patrocinador.id}/edit`}
                    className="rounded-md p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700"
                    aria-label="Editar patrocinador"
                  >
                    <Pencil size={16} strokeWidth={1.5} />
                  </Link>
                </div>
                <div className="mt-3">
                  <span
                    className={`inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${
                      patrocinador.ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                    }`}
                  >
                    {patrocinador.ativo ? 'Ativo' : 'Inativo'}
                  </span>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </MarketingLayout>
  )
}

export default Index