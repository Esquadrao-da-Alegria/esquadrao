import MarketingLayout from '@/layouts/MarketingLayout'
import { toast } from 'react-toastify'
import React from 'react'
import { router, useForm } from '@inertiajs/react'

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
  patrocinador: Patrocinador
}

interface CamposFormulario {
  nome: string
  site: string
  categoria: string
  ativo: boolean
  logotipo: File | null
  ordem_exibicao: number | string
}

const Edit: React.FC<Props> = ({ patrocinador }) => {
  const { data, setData, processing } = useForm<CamposFormulario>({
    nome: patrocinador.nome,
    site: patrocinador.site || '',
    categoria: patrocinador.categoria || '',
    ativo: patrocinador.ativo,
    logotipo: null,
    ordem_exibicao: patrocinador.ordem_exibicao ?? 1,
  })

  const handleDataChange = (campo: keyof CamposFormulario, valor: any) => {
    setData((prevData) => ({
      ...prevData,
      [campo]: valor,
    }))
  }

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0] || null
    handleDataChange('logotipo', file)
  }

  const handleSubmit = async () => {
    if (!data.nome) {
      toast.error('O campo nome é obrigatório!')
      return
    }

    if (data.ordem_exibicao === '' || Number(data.ordem_exibicao) < 0) {
      toast.error('Informe uma ordem de exibição válida!')
      return
    }

    router.post(`/patrocinadores/${patrocinador.id}`, { ...data, _method: 'put' })
  }

  const handleDelete = () => {
    if (confirm('Tem certeza que deseja excluir este patrocinador? Esta ação não pode ser desfeita.')) {
      router.delete(`/patrocinadores/${patrocinador.id}`, {
        onSuccess: () => toast.success('Patrocinador excluído com sucesso!'),
        onError: () => toast.error('Erro ao excluir o patrocinador.'),
      })
    }
  }

  const buscarUrlLogo = () => {
    if (data.logotipo) return URL.createObjectURL(data.logotipo)
    if (patrocinador.logo_path) return patrocinador.logo_path
    return null
  }

  return (
    <MarketingLayout>
      <section className="mx-auto w-full max-w-8xl px-4 py-16">
        <div className="flex justify-center">
          <div className="w-full max-w-7xl">
            <div className="overflow-hidden rounded-3xl bg-gradient-to-br from-pink-50 to-blue-50 shadow-lg">
              <div className="flex flex-col lg:flex-row">
                
                {/* Formulário */}
                <div className="flex-2 p-8 md:p-12">
                  <h2 className="mb-8 text-3xl font-bold text-gray-900 md:text-4xl">
                    Alterar Patrocinador
                  </h2>

                  <form
                    onSubmit={(e) => {
                      e.preventDefault()
                      handleSubmit()
                    }}
                    className="space-y-6"
                  >
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                      {/* Nome */}
                      <div className="md:col-span-2">
                        <label htmlFor="nome" className="mb-2 block text-sm font-medium text-gray-700">
                          Nome *
                        </label>
                        <input
                          type="text"
                          name="nome"
                          id="nome"
                          required
                          placeholder="Digite o nome do patrocinador"
                          value={data.nome}
                          onChange={(e) => handleDataChange('nome', e.target.value)}
                          className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-900 shadow-sm focus:ring-2 focus:ring-pink-500"
                        />
                      </div>

                      {/* Categoria */}
                      <div>
                        <label htmlFor="categoria" className="mb-2 block text-sm font-medium text-gray-700">
                          Categoria
                        </label>
                        <input
                          type="text"
                          name="categoria"
                          id="categoria"
                          placeholder="Ex: Diamante, Ouro, Prata"
                          value={data.categoria}
                          onChange={(e) => handleDataChange('categoria', e.target.value)}
                          className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-900 shadow-sm focus:ring-2 focus:ring-pink-500"
                        />
                      </div>

                      {/* Ordem de Exibição */}
                      <div>
                        <label htmlFor="ordem_exibicao" className="mb-2 block text-sm font-medium text-gray-700">
                          Ordem de Exibição *
                        </label>
                        <input
                          type="number"
                          name="ordem_exibicao"
                          id="ordem_exibicao"
                          required
                          min="0"
                          value={data.ordem_exibicao}
                          onChange={(e) => handleDataChange('ordem_exibicao', e.target.value)}
                          className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-900 shadow-sm focus:ring-2 focus:ring-pink-500"
                        />
                      </div>
                    </div>

                    {/* Site */}
                    <div>
                      <label htmlFor="site" className="mb-2 block text-sm font-medium text-gray-700">
                        Site
                      </label>
                      <input
                        type="url"
                        name="site"
                        id="site"
                        placeholder="https://www.exemplo.com.br"
                        value={data.site}
                        onChange={(e) => handleDataChange('site', e.target.value)}
                        className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-900 shadow-sm focus:ring-2 focus:ring-pink-500"
                      />
                    </div>

                    {/* Logotipo */}
                    <div>
                      <label htmlFor="logotipo" className="mb-2 block text-sm font-medium text-gray-700">
                        Atualizar Logotipo
                      </label>
                      <input
                        type="file"
                        name="logotipo"
                        id="logotipo"
                        accept="image/*"
                        onChange={handleFileChange}
                        className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-900 shadow-sm hover:file:bg-pink-100 focus:ring-2 focus:ring-pink-500 file:mr-4 file:rounded-lg file:border-0 file:bg-pink-50 file:px-4 file:py-2 file:text-pink-700"
                      />
                    </div>

                    {/* Status */}
                    <div className="flex items-center gap-2">
                      <input
                        type="checkbox"
                        name="ativo"
                        id="ativo"
                        checked={data.ativo}
                        onChange={(e) => handleDataChange('ativo', e.target.checked)}
                        className="h-4 w-4 rounded border-gray-300 text-pink-500 focus:ring-pink-500"
                      />
                      <label htmlFor="ativo" className="text-sm font-medium text-gray-700">
                        Ativo (Visível no site)
                      </label>
                    </div>

                    {/* Botões de Ação (ATUALIZADO) */}
                    <div className="flex flex-col-reverse gap-4 sm:flex-row pt-2">
                      <button
                        type="button"
                        onClick={handleDelete}
                        disabled={processing}
                        className="w-full rounded-full border-2 border-red-500 px-6 py-4 font-semibold text-red-500 transition-all duration-300 hover:bg-red-50 hover:scale-105 disabled:opacity-70 sm:w-1/3"
                      >
                        Excluir
                      </button>

                      <button
                        type="submit"
                        disabled={processing}
                        className="w-full rounded-full bg-gradient-to-r from-pink-500 to-blue-500 px-6 py-4 font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 disabled:opacity-70 sm:w-2/3"
                      >
                        {processing ? 'Salvando...' : 'Atualizar Patrocinador'}
                      </button>
                    </div>
                  </form>
                </div>

                {/* Imagem lateral */}
                <div className="hidden flex-1 items-center justify-center bg-gradient-to-br from-pink-100 to-blue-100 p-8 lg:flex">
                  <div className="relative flex h-64 w-64 items-center justify-center rounded-2xl bg-white shadow-lg p-2">
                    {buscarUrlLogo() ? (
                      <img
                        src={buscarUrlLogo()!}
                        alt={`Logotipo do ${patrocinador.nome}`}
                        className="h-full w-full rounded-xl object-contain"
                      />
                    ) : (
                      <span className="text-8xl">🤝</span>
                    )}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </MarketingLayout>
  )
}

export default Edit