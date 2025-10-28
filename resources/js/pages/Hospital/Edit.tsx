import React, { useEffect } from 'react'
import AppLayout from '@/layouts/AppLayout'
import { useForm } from '@inertiajs/react'
import { toast } from 'react-toastify'
import { Hospital } from '@/types'
import { store } from '@/routes/hospitais'

interface Props {
    hospital: Hospital
}

const Edit: React.FC<Props> = ({ hospital }) => {
    const { data, setData, post, processing } = useForm<Hospital>({
        ...hospital, // preenche o formulário com os dados existentes
    })

    const handleChange = (
        e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>
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


    const handleSubmit = () => {
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
                                        Editar Hospital
                                    </h2>

                                    <form
                                        onSubmit={(e) => {
                                            e.preventDefault()
                                            handleSubmit()
                                        }}
                                        className="space-y-6"
                                    >
                                        {/** Campos principais */}
                                        {[
                                            { label: 'Nome', name: 'nome', type: 'text', required: true },
                                            { label: 'CNPJ', name: 'cnpj', type: 'text', required: true, maxLength: 14 },
                                            { label: 'Email', name: 'email', type: 'email', required: true },
                                            { label: 'Telefone', name: 'telefone', type: 'text', required: true },
                                            { label: 'Endereço', name: 'endereco', type: 'text', required: true },
                                            { label: 'ID da Cidade', name: 'cidade_id', type: 'number', required: true },
                                        ].map(({ label, name, type, required, maxLength }) => (
                                            <div key={name}>
                                                <label htmlFor={name} className="mb-2 block text-sm font-medium text-gray-700">
                                                    {label} {required ? '*' : ''}
                                                </label>
                                                <input
                                                    type={type}
                                                    name={name}
                                                    id={name}
                                                    required={required}
                                                    maxLength={maxLength}
                                                    value={data[name as keyof Hospital] as any}
                                                    onChange={handleChange}
                                                    className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm text-gray-900 focus:ring-2 focus:ring-pink-500"
                                                />
                                            </div>
                                        ))}

                                        {/** Observações */}
                                        <div>
                                            <label htmlFor="observacoes" className="mb-2 block text-sm font-medium text-gray-700">
                                                Observações
                                            </label>
                                            <textarea
                                                name="observacoes"
                                                id="observacoes"
                                                rows={4}
                                                value={data.observacoes}
                                                onChange={handleChange}
                                                className="w-full resize-none rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm text-gray-900 focus:ring-2 focus:ring-pink-500"
                                            />
                                        </div>

                                        {/** Status */}
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

                                        {/** Botão */}
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="w-full rounded-full bg-gradient-to-r from-pink-500 to-blue-500 px-6 py-4 font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 disabled:opacity-70"
                                        >
                                            {processing ? 'Salvando...' : 'Salvar Alterações'}
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

export default Edit
