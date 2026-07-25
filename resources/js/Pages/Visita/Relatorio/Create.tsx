// REACT
import { type FC } from 'react'
import { Link, useForm } from '@inertiajs/react'

// UI
import RelatorioForm, { type RelatorioFormErrors } from '@/components/Painel/Visita/Relatorio/Formulario/Form'
import PainelLayout from '@/layouts/PainelLayout'
import { ArrowLeft, Check } from 'lucide-react'
import { toast } from 'react-toastify'

// TIPOS
import type { Visita } from '@/types'
import type { DadosFormulario } from '@/types/relatorio'

// ROTAS
import { index, store } from '@/routes/visitas/relatorios'

// SERVICES
import { Service } from '@/Services/Visita/Relatorio/Service'

interface Props {
    visita: Visita
    foraDoPrazoAviso: boolean
}

const Create: FC<Props> = ({ visita, foraDoPrazoAviso }) => {
    const { data, setData, transform, post, processing, errors } = useForm<DadosFormulario>({
        tipo_relatorio: '',
        ala_unidade_id: null,
        resumo: '',
        feedback: '',
        quartos_visitados: '',
        pessoas_impactadas: '',
        observacao_visitantes_externos: '',
        observacoes_gerais: '',
    })
    const erroGeral = (errors as RelatorioFormErrors).geral

    const handleFieldChange = <K extends keyof DadosFormulario>(campo: K, valor: DadosFormulario[K]) => {
        setData((prev) => ({ ...prev, [campo]: valor }))
    }

    const handleSubmit = () => {
        if (!data.tipo_relatorio || !data.resumo.trim()) {
            toast.error('Preencha o tipo e o resumo do relatório.')
            return
        }

        transform(() => Service.montarPayload(data))
        post(store.url({ visita: visita.id! }))
    }

    return (
        <PainelLayout>
            <section className="mx-auto w-full max-w-8xl px-4 py-16">
                <div className="flex justify-center">
                    <div className="w-full max-w-4xl">
                        <div className="overflow-hidden rounded-3xl border bg-white">
                            <div className="p-8 md:p-12">
                                <h2 className="mb-8 text-3xl font-bold text-amber-800 md:text-4xl">
                                    Novo relatório
                                </h2>

                                {erroGeral && (
                                    <div className="mb-4 rounded-lg border border-amber-200 bg-white p-4 text-amber-800">
                                        {erroGeral}
                                    </div>
                                )}

                                <form
                                    id="relatorio-form"
                                    onSubmit={(e) => {
                                        e.preventDefault()
                                        handleSubmit()
                                    }}
                                    className="space-y-6"
                                >
                                    <RelatorioForm
                                        visita={visita}
                                        data={data}
                                        errors={errors}
                                        foraDoPrazoAviso={foraDoPrazoAviso}
                                        onFieldChange={handleFieldChange}
                                    />
                                </form>
                            </div>

                            <div className="flex flex-col gap-3 border-t bg-white px-8 py-6 sm:flex-row sm:items-center sm:justify-between md:px-12">
                                <Link
                                    href={index.url({ visita: visita.id! })}
                                    className="inline-flex items-center justify-center gap-2 rounded-full border px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                                >
                                    <ArrowLeft className="size-4" aria-hidden />
                                    Voltar
                                </Link>

                                <button
                                    type="submit"
                                    form="relatorio-form"
                                    disabled={processing}
                                    className="inline-flex items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 font-semibold text-amber-700 transition hover:bg-amber-50 disabled:opacity-70"
                                >
                                    <Check className="size-4" aria-hidden />
                                    {processing ? 'Salvando...' : 'Salvar relatório'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </PainelLayout>
    )
}

export default Create
