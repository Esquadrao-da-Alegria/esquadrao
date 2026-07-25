import RelatorioForm, { type RelatorioFormErrors, type RelatorioFormValues } from '@/components/Painel/Visita/Relatorio/Formulario/Form'
import PainelLayout from '@/layouts/PainelLayout'
import { show, update } from '@/routes/visitas/relatorios'
import type { Visita, VisitaRelatorio } from '@/types'
import { Link, router, useForm } from '@inertiajs/react'
import { ArrowLeft, Check } from 'lucide-react'
import { type FC } from 'react'
import { toast } from 'react-toastify'

interface Props {
    visita: Visita
    relatorio: VisitaRelatorio
    foraDoPrazoAviso: boolean
}

const Edit: FC<Props> = ({ visita, relatorio, foraDoPrazoAviso }) => {
    const { data, setData, processing, errors } = useForm<RelatorioFormValues>({
        tipo_relatorio: relatorio.tipo_relatorio,
        ala_unidade_id: relatorio.ala_unidade_id ?? null,
        resumo: relatorio.resumo,
        feedback: relatorio.feedback ?? '',
        quartos_visitados: relatorio.quartos_visitados ?? '',
        pessoas_impactadas: relatorio.pessoas_impactadas ?? '',
        observacao_visitantes_externos: relatorio.observacao_visitantes_externos ?? '',
        observacoes_gerais: relatorio.observacoes_gerais ?? '',
    })
    const erroGeral = (errors as RelatorioFormErrors).geral

    const handleFieldChange = <K extends keyof RelatorioFormValues>(campo: K, valor: RelatorioFormValues[K]) => {
        setData((prev) => ({ ...prev, [campo]: valor }))
    }

    const handleSubmit = () => {
        if (!data.tipo_relatorio || !data.resumo.trim()) {
            toast.error('Preencha o tipo e o resumo do relatório.')
            return
        }

        router.post(update.url({ visita: visita.id!, relatorio: relatorio.id! }), {
            ...data,
            _method: 'put',
            ala_unidade_id: data.ala_unidade_id || null,
            quartos_visitados: data.quartos_visitados === '' ? null : data.quartos_visitados,
            pessoas_impactadas: data.pessoas_impactadas === '' ? null : data.pessoas_impactadas,
            feedback: data.feedback || null,
            observacao_visitantes_externos: data.observacao_visitantes_externos || null,
            observacoes_gerais: data.observacoes_gerais || null,
        })
    }

    return (
        <PainelLayout>
            <section className="mx-auto w-full max-w-8xl px-4 py-16">
                <div className="flex justify-center">
                    <div className="w-full max-w-4xl">
                        <div className="overflow-hidden rounded-3xl border bg-white">
                            <div className="p-8 md:p-12">
                                <h2 className="mb-8 text-3xl font-bold text-amber-800 md:text-4xl">
                                    Editar relatório
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
                                    href={show.url({ visita: visita.id!, relatorio: relatorio.id! })}
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
                                    {processing ? 'Salvando...' : 'Salvar alterações'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </PainelLayout>
    )
}

export default Edit
