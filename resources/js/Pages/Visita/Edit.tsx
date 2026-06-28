import VisitaForm, { type VisitaFormValues } from '@/components/Painel/Visita/Formulario/Form'
import PainelLayout from '@/layouts/PainelLayout'
import { extrairData, extrairHora } from '@/lib/visita'
import { index, update } from '@/routes/visitas'
import type { Hospital, User, Visita } from '@/types'
import { Link, router, useForm } from '@inertiajs/react'
import { ArrowLeft, Check } from 'lucide-react'
import { type FC } from 'react'
import { toast } from 'react-toastify'

interface Props {
    hospitais: Hospital[]
    lideres: User[]
    visita: Visita
}

const Edit: FC<Props> = ({ hospitais, lideres, visita }) => {
    const { data, setData, processing, errors } = useForm<VisitaFormValues>({
        hospital_id: visita.hospital_id,
        ala_unidade_id: visita.ala_unidade_id ?? null,
        data: extrairData(visita.inicio_em),
        hora_inicio: extrairHora(visita.inicio_em),
        hora_fim: extrairHora(visita.fim_em),
        tipo: visita.tipo,
        lider_id: visita.lider_id ?? '',
        status: visita.status,
        observacoes: visita.observacoes ?? '',
    })

    const handleFieldChange = <K extends keyof VisitaFormValues>(campo: K, valor: VisitaFormValues[K]) => {
        setData((prev) => ({ ...prev, [campo]: valor }))
    }

    const handleSubmit = () => {
        if (!data.data || !data.hora_inicio || !data.hora_fim || !data.tipo || !data.lider_id || !data.status) {
            toast.error('Preencha todos os campos obrigatórios.')
            return
        }

        const url = update({ visita: visita.id! }).url
        router.post(url, { ...data, _method: 'put' })
    }

    return (
        <PainelLayout>
            <section className="mx-auto w-full max-w-8xl px-4 py-16">
                <div className="flex justify-center">
                    <div className="w-full max-w-7xl">
                        <div className="overflow-hidden rounded-3xl border bg-white">
                            <div className="p-8 md:p-12">
                                <h2 className="mb-8 text-3xl font-bold text-amber-800 md:text-4xl">
                                    Alterar visita
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
                                    id="visita-form"
                                    onSubmit={(e) => {
                                        e.preventDefault()
                                        handleSubmit()
                                    }}
                                    className="space-y-6"
                                >
                                    <VisitaForm
                                        data={data}
                                        errors={errors}
                                        mode="edit"
                                        hospitais={hospitais}
                                        lideres={lideres}
                                        onFieldChange={handleFieldChange}
                                    />
                                </form>
                            </div>

                            <div className="flex flex-col gap-3 border-t bg-white px-8 py-6 sm:flex-row sm:items-center sm:justify-between md:px-12">
                                <Link
                                    href={index().url}
                                    className="inline-flex items-center justify-center gap-2 rounded-full border px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                                >
                                    <ArrowLeft className="size-4" aria-hidden />
                                    Voltar
                                </Link>

                                <button
                                    type="submit"
                                    form="visita-form"
                                    disabled={processing}
                                    className="inline-flex items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 font-semibold text-amber-700 transition hover:bg-amber-50 disabled:opacity-70"
                                >
                                    <Check className="size-4" aria-hidden />
                                    {processing ? 'Salvando...' : 'Salvar'}
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
