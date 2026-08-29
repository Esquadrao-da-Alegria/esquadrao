import PainelLayout from '@/layouts/PainelLayout'
import BotaoSalvar from '@/components/Painel/Forms/BotaoSalvar/Show'
import FormularioRodape from '@/components/Painel/Forms/FormularioRodape/Show'
import { useImageCompressor } from '@/hooks/use-image-compressor'
import { painelInputClass, painelLabelClass } from '@/lib/painelFormFieldClasses'
import { index, store } from '@/routes/patrocinadores'
import { useForm } from '@inertiajs/react'
import React from 'react'
import { toast } from 'react-toastify'

interface CamposFormulario {
    nome: string
    site: string
    categoria: string
    ativo: boolean
    logotipo: File | null
    ordem_exibicao: number | string
}

const Create: React.FC = () => {
    const { data, setData, post, processing, errors } = useForm<CamposFormulario>({
        nome: '',
        site: '',
        categoria: '',
        ativo: true,
        logotipo: null,
        ordem_exibicao: 1,
    })

    const { processImage, isCompressing } = useImageCompressor()

    const handleDataChange = (campo: keyof CamposFormulario, valor: CamposFormulario[keyof CamposFormulario]) => {
        setData((prevData) => ({
            ...prevData,
            [campo]: valor,
        }))
    }

    const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0] || null

        if (!file) {
            handleDataChange('logotipo', null)
            return
        }

        const compressedFile = await processImage(file)
        handleDataChange('logotipo', compressedFile)
    }

    const handleSubmit = () => {
        if (!data.nome) {
            toast.error('O campo nome é obrigatório!')
            return
        }

        if (data.ordem_exibicao === '' || Number(data.ordem_exibicao) < 0) {
            toast.error('Informe uma ordem de exibição válida!')
            return
        }

        post(store().url)
    }

    const inputClass = `${painelInputClass} border-amber-200 focus:ring-amber-500`
    const fileInputClass = `${inputClass} file:mr-4 file:rounded-lg file:border file:border-amber-200 file:bg-white file:px-4 file:py-2 file:text-sm file:font-medium file:text-amber-800 hover:file:bg-amber-50`

    return (
        <PainelLayout>
            <section className="mx-auto w-full max-w-8xl px-4 py-16">
                <div className="flex justify-center">
                    <div className="w-full max-w-7xl">
                        <div className="overflow-hidden rounded-3xl border bg-white">
                            <div className="p-8 md:p-12">
                                <h2 className="mb-8 text-3xl font-bold text-amber-800 md:text-4xl">
                                    Cadastrar patrocinador
                                </h2>

                                {errors && Object.keys(errors).length > 0 && (
                                    <div className="mb-4 rounded-lg border border-amber-200 bg-white p-4 text-amber-800">
                                        <ul>
                                            {Object.entries(errors).map(([campo, mensagem]) => (
                                                <li key={campo}>{mensagem}</li>
                                            ))}
                                        </ul>
                                    </div>
                                )}

                                <form
                                    id="patrocinador-form"
                                    onSubmit={(e) => {
                                        e.preventDefault()
                                        handleSubmit()
                                    }}
                                    className="space-y-6"
                                >
                                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                        <div className="md:col-span-2">
                                            <label htmlFor="nome" className={painelLabelClass}>
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
                                                className={inputClass}
                                            />
                                        </div>

                                        <div>
                                            <label htmlFor="categoria" className={painelLabelClass}>
                                                Categoria
                                            </label>
                                            <input
                                                type="text"
                                                name="categoria"
                                                id="categoria"
                                                placeholder="Ex: Diamante, Ouro, Prata"
                                                value={data.categoria}
                                                onChange={(e) => handleDataChange('categoria', e.target.value)}
                                                className={inputClass}
                                            />
                                        </div>

                                        <div>
                                            <label htmlFor="ordem_exibicao" className={painelLabelClass}>
                                                Ordem de exibição *
                                            </label>
                                            <input
                                                type="number"
                                                name="ordem_exibicao"
                                                id="ordem_exibicao"
                                                required
                                                min="0"
                                                value={data.ordem_exibicao}
                                                onChange={(e) => handleDataChange('ordem_exibicao', e.target.value)}
                                                className={inputClass}
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label htmlFor="site" className={painelLabelClass}>
                                            Site
                                        </label>
                                        <input
                                            type="url"
                                            name="site"
                                            id="site"
                                            placeholder="https://www.exemplo.com.br"
                                            value={data.site}
                                            onChange={(e) => handleDataChange('site', e.target.value)}
                                            className={inputClass}
                                        />
                                    </div>

                                    <div>
                                        <label htmlFor="logotipo" className={painelLabelClass}>
                                            Logotipo da empresa
                                        </label>
                                        <input
                                            type="file"
                                            name="logotipo"
                                            id="logotipo"
                                            accept="image/*"
                                            onChange={handleFileChange}
                                            className={fileInputClass}
                                        />
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            name="ativo"
                                            id="ativo"
                                            checked={data.ativo}
                                            onChange={(e) => handleDataChange('ativo', e.target.checked)}
                                            className="h-4 w-4 rounded border-amber-300 text-amber-600 focus:ring-amber-500"
                                        />
                                        <label htmlFor="ativo" className="text-sm font-medium text-amber-900">
                                            Ativo (visível no site)
                                        </label>
                                    </div>
                                </form>
                            </div>

                            <FormularioRodape
                                voltarHref={index().url}
                                salvar={(
                                    <BotaoSalvar
                                        type="submit"
                                        form="patrocinador-form"
                                        disabled={processing || isCompressing}
                                        salvando={processing || isCompressing}
                                    >
                                        {isCompressing
                                            ? 'Otimizando imagem...'
                                            : processing
                                              ? 'Salvando...'
                                              : 'Salvar'}
                                    </BotaoSalvar>
                                )}
                            />
                        </div>
                    </div>
                </div>
            </section>
        </PainelLayout>
    )
}

export default Create
