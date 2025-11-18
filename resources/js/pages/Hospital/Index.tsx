import AppLayout from '@/layouts/AppLayout'
import { Hospital } from '@/types'
import { Pencil } from 'lucide-react'
import { Link, router } from '@inertiajs/react'
import { create, edit } from '@/routes/hospitais';

interface Props {
  hospitais: Hospital[];
}

const Index: React.FC<Props> = ({ hospitais }) => {

  const handleCriarClick = () => {
    const url = create();

    router.visit(url);
  }

  const handleEditarClick = (hospital: Hospital) => {
    const url = edit({ id: hospital.id! });

    router.visit(url);
  }

  return (
    <AppLayout>
      <div className="mx-auto max-w-6xl p-6">
        <div className="mb-6 flex items-center justify-between">
          <h1 className="bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 bg-clip-text text-3xl font-extrabold text-transparent">
            Hospitais Cadastrados
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
                <th className="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
                  Imagem
                </th>
                <th className="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
                  Nome
                </th>
                <th className="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
                  Endereço
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
              {hospitais.map((hospital) => (
                <tr
                  key={hospital.id}
                  className="transition hover:bg-gray-50"
                >
                  <td className="px-6 py-4">
                    <img
                      src={hospital.url_foto ?? 'https://media.istockphoto.com/id/1147544807/vector/thumbnail-image-vector-graphic.jpg?s=612x612&w=0&k=20&c=rnCKVbdxqkjlcs3xH87-9gocETqpspHFXu5dIGB4wuM='}
                      alt={hospital.nome}
                      className="h-14 w-14 rounded-full object-cover shadow-sm ring-2 ring-pink-200"
                    />
                  </td>
                  <td className="px-6 py-4 text-sm font-medium text-gray-900">
                    {hospital.nome}
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-600">
                    {hospital.endereco}
                  </td>
                  <td className="px-6 py-4">
                    <span
                      className={`inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${hospital.ativo
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800'
                        }`}
                    >
                      {hospital.ativo ? 'Ativo' : 'Inativo'}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-center">
                    <button
                      onClick={() => handleEditarClick(hospital)}
                      type='button'
                      className="inline-flex items-center justify-center rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition"
                      aria-label="Editar hospital"
                    >
                      <Pencil size={16} strokeWidth={1.5} />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          {/* Versão mobile (cards) */}
          <div className="space-y-4 bg-white p-4 md:hidden">
            {hospitais.map((hospital) => (
              <div
                key={hospital.id}
                className="flex flex-col rounded-2xl border border-gray-100 bg-gradient-to-br from-pink-50 via-white to-blue-50 p-4 shadow-sm"
              >
                <div className="flex items-center gap-4">
                  <img
                    // src={hospital.imagem_url}
                    src='https://imagens.ebc.com.br/ezhL7QDLWeq77RcIBaA78AQ9RMc=/1170x700/smart/https://agenciabrasil.ebc.com.br/sites/default/files/thumbnails/image/2025/01/17/toms1434.jpg?itok=QxAcBaX6'
                    alt={hospital.nome}
                    className="h-14 w-14 rounded-full object-cover ring-2 ring-pink-200"
                  />
                  <div className="flex-1">
                    <h2 className="font-semibold text-gray-800">
                      {hospital.nome}
                    </h2>
                    <p className="text-sm text-gray-600">
                      {hospital.endereco}
                    </p>
                  </div>
                  <Link
                    href={`/hospitais/${hospital.id}/editar`}
                    className="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition"
                    aria-label="Editar hospital"
                  >
                    <Pencil size={16} strokeWidth={1.5} />
                  </Link>
                </div>
                <div className="mt-3">
                  <span
                    className={`inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${hospital.ativo
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800'
                      }`}
                  >
                    {hospital.ativo ? 'Ativo' : 'Inativo'}
                  </span>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </AppLayout>
  )
}

export default Index
