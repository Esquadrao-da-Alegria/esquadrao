import AppLayout from '@/layouts/AppLayout'
import { store } from '@/routes/hospitais'
import { toast } from 'react-toastify'
import React from 'react'
import { useForm } from '@inertiajs/react'
import { Cidade, Estado } from '@/types'
import { Queries } from '@/Queries/Cidade/Queries'

interface Props {
    cidades: Cidade[]
}

interface CamposFormulario {
    cidade_id: number|string
    nome: string
    cnpj: string
    endereco: string
    telefone: string
    email: string
    ativo: boolean
    foto: File | null
    observacoes?: string
}

const Create: React.FC<Props> = ({ cidades }) => {
    const { data, setData, post, processing } = useForm<CamposFormulario>({
        cidade_id: '',
        nome: 'Teste',
        cnpj: '12312312312333',
        endereco: 'teste endereço',
        telefone: '5499439439',
        email: 'teste@gmail.com',
        ativo: true,
        foto: null,
        observacoes: 'uauauauua',
    })

    const handleDataChange = (campo: keyof CamposFormulario, valor: any) => {

        setData((prevData) => ({
            ...prevData,
            [campo]: valor
        }))
    }

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0] || null

        handleDataChange('foto', file)
    }

    const handleSubmit = async () => {
        if (!data.nome || !data.cnpj || !data.email || !data.telefone) {
            toast.error('Preencha todos os campos obrigatórios!')
            return
        }

        post(store().url)
    }

    return (
        <AppLayout>
            <section className="mx-auto w-full max-w-8xl px-4 py-16">
                <div className="flex justify-center">
                    <div className="w-full max-w-7xl">
                        <div className="overflow-hidden rounded-3xl bg-gradient-to-br from-pink-50 to-blue-50 shadow-lg">
                            <div className="flex flex-col lg:flex-row">
                                {/* Formulário */}
                                <div className="flex-2 p-8 md:p-12">
                                    <h2 className="mb-8 text-3xl font-bold text-gray-900 md:text-4xl">
                                        Cadastrar Hospital
                                    </h2>

                                    <form
                                        onSubmit={(e) => {
                                            e.preventDefault()
                                            handleSubmit()
                                        }}
                                        className="space-y-6"
                                    >
                                        {/* Nome */}
                                        <div>
                                            <label htmlFor="nome" className="mb-2 block text-sm font-medium text-gray-700">
                                                Nome *
                                            </label>
                                            <input
                                                type="text"
                                                name="nome"
                                                id="nome"
                                                required
                                                placeholder="Digite o nome do hospital"
                                                value={data.nome}
                                                onChange={(e) => handleDataChange('nome', e.target.value)}
                                                className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm text-gray-900 focus:ring-2 focus:ring-pink-500"
                                            />
                                        </div>

                                        {/* CNPJ */}
                                        <div>
                                            <label htmlFor="cnpj" className="mb-2 block text-sm font-medium text-gray-700">
                                                CNPJ *
                                            </label>
                                            <input
                                                type="text"
                                                name="cnpj"
                                                id="cnpj"
                                                required
                                                placeholder="Digite o CNPJ (apenas números)"
                                                value={data.cnpj}
                                                onChange={(e) => handleDataChange('cnpj', e.target.value)}
                                                maxLength={14}
                                                className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm text-gray-900 focus:ring-2 focus:ring-pink-500"
                                            />
                                        </div>

                                        {/* Email */}
                                        <div>
                                            <label htmlFor="email" className="mb-2 block text-sm font-medium text-gray-700">
                                                Email *
                                            </label>
                                            <input
                                                type="email"
                                                name="email"
                                                id="email"
                                                required
                                                placeholder="Digite o email"
                                                value={data.email}
                                                onChange={(e) => handleDataChange('email', e.target.value)}
                                                className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm text-gray-900 focus:ring-2 focus:ring-pink-500"
                                            />
                                        </div>

                                        {/* Telefone */}
                                        <div>
                                            <label htmlFor="telefone" className="mb-2 block text-sm font-medium text-gray-700">
                                                Telefone *
                                            </label>
                                            <input
                                                type="text"
                                                name="telefone"
                                                id="telefone"
                                                required
                                                placeholder="Digite o telefone"
                                                value={data.telefone}
                                                onChange={(e) => handleDataChange('telefone', e.target.value)}
                                                className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm text-gray-900 focus:ring-2 focus:ring-pink-500"
                                            />
                                        </div>

                                        {/* Endereço */}
                                        <div>
                                            <label htmlFor="endereco" className="mb-2 block text-sm font-medium text-gray-700">
                                                Endereço *
                                            </label>
                                            <input
                                                type="text"
                                                name="endereco"
                                                id="endereco"
                                                required
                                                placeholder="Ex: R. Prof. Dr. Araújo, 538 - Centro, Pelotas - RS"
                                                value={data.endereco}
                                                onChange={(e) => handleDataChange('endereco', e.target.value)}
                                                className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm text-gray-900 focus:ring-2 focus:ring-pink-500"
                                            />
                                        </div>

                                        {/* Cidade */}
                                        <div>
                                            <label
                                                htmlFor="cidade_id"
                                                className="mb-2 block text-sm font-medium text-gray-700"
                                            >
                                                Cidade *
                                            </label>

                                            <select
                                                name="cidade_id"
                                                id="cidade_id"
                                                required
                                                value={data.cidade_id}
                                                onChange={(e) => handleDataChange('cidade_id', Number(e.target.value))}
                                                className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm text-gray-900 focus:ring-2 focus:ring-pink-500"
                                            >
                                                <option value="">Selecione uma cidade...</option>

                                                {cidades.map((cidade: Cidade) => (
                                                    <option key={cidade.id} value={cidade.id}>
                                                        {cidade.nome}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>

                                        {/* Foto */}
                                        <div>
                                            <label htmlFor="foto" className="mb-2 block text-sm font-medium text-gray-700">
                                                Foto
                                            </label>
                                            <input
                                                type="file"
                                                name="foto"
                                                id="foto"
                                                accept="image/*"
                                                onChange={handleFileChange}
                                                className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm text-gray-900 focus:ring-2 focus:ring-pink-500 file:mr-4 file:rounded-lg file:border-0 file:bg-pink-50 file:px-4 file:py-2 file:text-pink-700 hover:file:bg-pink-100"
                                            />
                                        </div>

                                        {/* Observações */}
                                        <div>
                                            <label htmlFor="observacoes" className="mb-2 block text-sm font-medium text-gray-700">
                                                Observações
                                            </label>
                                            <textarea
                                                name="observacoes"
                                                id="observacoes"
                                                rows={4}
                                                placeholder="Digite observações adicionais"
                                                value={data.observacoes}
                                                onChange={(e) => handleDataChange('observacoes', e.target.value)}
                                                className="w-full resize-none rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm text-gray-900 focus:ring-2 focus:ring-pink-500"

                                            />
                                        </div>

                                        {/* Status */}
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="checkbox"
                                                name="ativo"
                                                id="ativo"
                                                checked={data.ativo}
                                                onChange={(e) => handleDataChange('observacoes', e.target.checked)}
                                                className="h-4 w-4 rounded border-gray-300 text-pink-500 focus:ring-pink-500"
                                            />
                                            <label htmlFor="ativo" className="text-sm font-medium text-gray-700">
                                                Ativo
                                            </label>
                                        </div>

                                        {/* Botão Salvar */}
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="w-full rounded-full bg-gradient-to-r from-pink-500 to-blue-500 px-6 py-4 font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 disabled:opacity-70"
                                        >
                                            {processing ? 'Salvando...' : 'Salvar Hospital'}
                                        </button>
                                    </form>
                                </div>

                                {/* Imagem lateral */}
                                <div className="hidden flex-1 items-center justify-center bg-gradient-to-br from-pink-100 to-blue-100 p-8 lg:flex">
                                    <div className="relative flex h-64 w-64 items-center justify-center rounded-2xl bg-white shadow-lg">
                                        {data.foto ? (
                                            <img
                                                src={URL.createObjectURL(data.foto)}
                                                alt="Pré-visualização da foto"
                                                className="h-full w-full rounded-2xl object-cover"
                                            />
                                        ) : (
                                            <span className="text-8xl">🏥</span>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </AppLayout>
    )
}

export default Create
