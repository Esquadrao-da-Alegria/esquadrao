import EventoForm, { type EventoFormValues } from '@/components/Painel/Evento/Formulario/Form'
import PainelLayout from '@/layouts/PainelLayout'
import { extrairData, extrairHora, montarDatetime } from '@/lib/evento'
import { show, update } from '@/routes/eventos'
import type { Cidade, Evento, User } from '@/types'
import { Link, useForm } from '@inertiajs/react'
import { ArrowLeft, Check } from 'lucide-react'
import { type FC } from 'react'
import { toast } from 'react-toastify'

interface Props {
    evento: Evento
    responsaveis: User[]
    cidades: Cidade[]
}

function montarPayload(data: EventoFormValues) {
    return {
        titulo: data.titulo,
        tipo: data.tipo,
        descricao: data.descricao || null,
        local: data.local || null,
        cidade_id: data.cidade_id ? Number(data.cidade_id) : null,
        data_inicio: montarDatetime(data.data, data.hora_inicio),
        data_fim: montarDatetime(data.data_fim, data.sem_hora_fim ? '23:59' : data.hora_fim),
        limite_inscricao: data.limite_inscricao_data
            ? montarDatetime(data.limite_inscricao_data, data.limite_inscricao_hora || '23:59')
            : null,
        limite_participantes: data.limite_participantes ? Number(data.limite_participantes) : null,
        responsavel_id: data.responsavel_id ? Number(data.responsavel_id) : null,
    }
}

const Edit: FC<Props> = ({ evento, responsaveis, cidades }) => {
    const { data, setData, transform, put, processing, errors } = useForm<EventoFormValues>({
        titulo: evento.titulo,
        tipo: evento.tipo,
        descricao: evento.descricao ?? '',
        local: evento.local ?? '',
        cidade_id: evento.cidade_id?.toString() ?? '',
        data: extrairData(evento.data_inicio),
        hora_inicio: extrairHora(evento.data_inicio),
        data_fim: evento.data_fim ? extrairData(evento.data_fim) : extrairData(evento.data_inicio),
        sem_hora_fim: !evento.data_fim || extrairHora(evento.data_fim) === '23:59',
        hora_fim: (evento.data_fim && extrairHora(evento.data_fim) !== '23:59') ? extrairHora(evento.data_fim) : '',
        limite_inscricao_data: evento.limite_inscricao ? extrairData(evento.limite_inscricao) : '',
        limite_inscricao_hora: evento.limite_inscricao ? extrairHora(evento.limite_inscricao) : '',
        limite_participantes: evento.limite_participantes?.toString() ?? '',
        responsavel_id: evento.responsavel_id ?? '',
    })

    const handleFieldChange = <K extends keyof EventoFormValues>(campo: K, valor: EventoFormValues[K]) => {
        setData((prev) => ({ ...prev, [campo]: valor }))
    }

    const handleSubmit = () => {
        if (!data.titulo || !data.tipo || !data.cidade_id || !data.data || !data.hora_inicio || !data.data_fim) {
            toast.error('Preencha todos os campos obrigatórios.')
            return
        }

        if (!data.sem_hora_fim && !data.hora_fim) {
            toast.error('Informe o horário de fim ou marque "Sem horário final".')
            return
        }

        if (data.limite_inscricao_hora && !data.limite_inscricao_data) {
            toast.error('Informe a data limite de inscrição junto com o horário.')
            return
        }

        transform(() => montarPayload(data))
        put(update({ evento: evento.id }).url)
    }

    return (
        <PainelLayout>
            <section className="mx-auto w-full max-w-8xl px-4 py-16">
                <div className="flex justify-center">
                    <div className="w-full max-w-7xl">
                        <div className="overflow-hidden rounded-3xl border bg-white">
                            <div className="p-8 md:p-12">
                                <h2 className="mb-8 text-3xl font-bold text-amber-800 md:text-4xl">
                                    Alterar evento
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
                                    id="evento-form"
                                    onSubmit={(e) => {
                                        e.preventDefault()
                                        handleSubmit()
                                    }}
                                    className="space-y-6"
                                >
                                    <EventoForm
                                        data={data}
                                        errors={errors}
                                        responsaveis={responsaveis}
                                        cidades={cidades}
                                        onFieldChange={handleFieldChange}
                                    />
                                </form>
                            </div>

                            <div className="flex flex-col gap-3 border-t bg-white px-8 py-6 sm:flex-row sm:items-center sm:justify-between md:px-12">
                                <Link
                                    href={show({ evento: evento.id }).url}
                                    className="inline-flex items-center justify-center gap-2 rounded-full border px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                                >
                                    <ArrowLeft className="size-4" aria-hidden />
                                    Voltar
                                </Link>

                                <button
                                    type="submit"
                                    form="evento-form"
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