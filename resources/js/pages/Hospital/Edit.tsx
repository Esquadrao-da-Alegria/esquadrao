import AppLayout from '@/layouts/AppLayout'
import { update } from '@/routes/hospitais'
import { toast } from 'react-toastify'
import React from 'react'
import { router, useForm } from '@inertiajs/react'
import { AlaHospital, Cidade, Hospital } from '@/types'
import { Check } from 'lucide-react'

interface Props {
    cidades: Cidade[]
    hospital: Hospital
}

interface CamposFormulario {
    cidade_id: number
    nome: string
    cnpj: string
    endereco: string
    telefone: string
    email: string
    ativo: boolean
    foto: File | null
    alas: AlaHospital[]
    observacoes?: string
}

const Edit: React.FC<Props> = ({ hospital, cidades }) => {
    const [novaAla, setNovaAla] = React.useState('')

    const { data, setData, processing } = useForm<CamposFormulario>({
        cidade_id: hospital.cidade_id,
        nome: hospital.nome,
        cnpj: hospital.cnpj,
        endereco: hospital.endereco,
        telefone: hospital.telefone,
        email: hospital.email,
        ativo: hospital.ativo,
        foto: null,
        alas: hospital.alas || [],
        observacoes: hospital.observacoes
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

    const adicionarAla = () => {
        const ala = novaAla.trim()

        if (!ala) return

        if (data.alas.some((a) => a.nome === ala)) {
            toast.warning('Essa ala ja foi adicionada.')
            return
        }

        handleDataChange('alas', [...data.alas, { nome: ala }])

        setNovaAla('')
    }

    const removerAla = (nome: string) => {

        handleDataChange('alas', data.alas.filter((ala) => ala.nome !== nome))
    }

    const handleSubmit = async () => {
        if (!data.nome || !data.cnpj || !data.email || !data.telefone) {
            toast.error('Preencha todos os campos obrigatórios!')
            return
        }

        const url = update({ hospital: hospital.id! }).url;

        router.post(url, { ...data as {}, _method: 'put' })
    }

    const buscarUrlFoto = () => {

        if (data.foto) return URL.createObjectURL(data.foto);

        if (hospital.url_foto) return hospital.url_foto;

        return null;
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
                                        Alterar Hospital
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

                                        {/* Alas */}
                                        <div>
                                            <label htmlFor="nova_ala" className="mb-2 block text-sm font-medium text-gray-700">
                                                Alas do hospital
                                            </label>

                                            <div className="flex items-center gap-2">
                                                <input
                                                    type="text"
                                                    name="nova_ala"
                                                    id="nova_ala"
                                                    placeholder="Digite o nome da ala"
                                                    value={novaAla}
                                                    onChange={(e) => setNovaAla(e.target.value)}
                                                    onKeyDown={(e) => {
                                                        if (e.key === 'Enter') {
                                                            e.preventDefault()
                                                            adicionarAla()
                                                        }
                                                    }}
                                                    className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm text-gray-900 focus:ring-2 focus:ring-pink-500"
                                                />

                                                <button
                                                    type="button"
                                                    onClick={adicionarAla}
                                                    className="rounded-xl bg-pink-500 px-4 py-3 font-semibold text-white transition-colors hover:bg-pink-600"
                                                >
                                                    Adicionar
                                                </button>
                                            </div>

                                            <ul className="mt-3 space-y-2">
                                                {data.alas.map((ala) => (
                                                    <li
                                                        key={ala.nome}
                                                        className="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm text-gray-800"
                                                    >
                                                        <span>{ala.nome}</span>

                                                        <button
                                                            type="button"
                                                            onClick={() => removerAla(ala.nome)}
                                                            className="text-xs font-semibold text-red-500 hover:text-red-700"
                                                        >
                                                            Remover
                                                        </button>
                                                    </li>
                                                ))}
                                            </ul>
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
                                            Salvar
                                        </button>
                                    </form>
                                </div>

                                {/* Imagem lateral */}
                                <div className="hidden flex-1 items-center justify-center bg-gradient-to-br from-pink-100 to-blue-100 p-8 lg:flex">
                                    <div className="relative flex h-64 w-64 items-center justify-center rounded-2xl bg-white shadow-lg">
                                        {buscarUrlFoto() ? (
                                            <img
                                                src={buscarUrlFoto()!}
                                                alt={`Foto do ${hospital.nome}`}
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

export default Edit
