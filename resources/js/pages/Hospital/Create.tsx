import AppLayout from '@/layouts/AppLayout'
import { store } from '@/routes/hospitais'
import { toast } from 'react-toastify'
import React from 'react'
import { useForm } from '@inertiajs/react'
import { Hospital } from '@/types'

const Create: React.FC = () => {
    const { data, setData, post, processing } = useForm<Hospital>({
        cidade_id: 4309407,
        nome: 'Teste',
        cnpj: '12312312312333',
        endereco: 'teste endereço',
        telefone: '5499439439',
        email: 'teste@gmail.com',
        ativo: true,
        observacoes: 'uauauauua',
    })

    const handleChange = (
        e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>,
    ) => {
        const { name, value, type } = e.target
        if (!(name in data)) return

        if (type === 'checkbox' && e.target instanceof HTMLInputElement) {
            setData(name as keyof Hospital, e.target.checked as any)
        } else if (type === 'number') {
            setData(name as keyof Hospital, Number(value) as any)
        } else {
            setData(name as keyof Hospital, value as any)
        }
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
            <section className="mx-auto w-full max-w-6xl px-4 py-16">
                <div className="flex justify-center">
                    <div className="w-full max-w-4xl">
                        <div className="overflow-hidden rounded-3xl bg-gradient-to-br from-pink-50 to-blue-50 shadow-lg">
                            <div className="flex flex-col lg:flex-row">
                                {/* Formulário */}
                                <div className="flex-1 p-8 md:p-12">
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
                                                onChange={handleChange}
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
                                                onChange={handleChange}
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
                                                onChange={handleChange}
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
                                                onChange={handleChange}
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
                                                onChange={handleChange}
                                                className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm text-gray-900 focus:ring-2 focus:ring-pink-500"
                                            />
                                        </div>

                                        {/* Cidade ID */}
                                        <div>
                                            <label htmlFor="cidade_id" className="mb-2 block text-sm font-medium text-gray-700">
                                                ID da Cidade *
                                            </label>
                                            <input
                                                type="number"
                                                name="cidade_id"
                                                id="cidade_id"
                                                required
                                                placeholder="Digite o ID da cidade"
                                                value={data.cidade_id}
                                                onChange={handleChange}
                                                className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm text-gray-900 focus:ring-2 focus:ring-pink-500"
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
                                                onChange={handleChange}
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
                                                onChange={handleChange}
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
                                        <span className="text-8xl">🏥</span>
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
