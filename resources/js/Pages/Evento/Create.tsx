import EventoForm, { type EventoFormValues } from '@/components/Painel/Evento/Formulario/Form'
import BotaoSalvar from '@/components/Painel/Forms/BotaoSalvar/Show'
import FormularioRodape from '@/components/Painel/Forms/FormularioRodape/Show'
import PainelLayout from '@/layouts/PainelLayout'
import { montarDatetime } from '@/lib/evento'
import { hojeLocal } from '@/lib/visita'
import { index, store } from '@/routes/eventos'
import type { Cidade, SharedData, User } from '@/types'
import { useForm, usePage } from '@inertiajs/react'
import { type FC } from 'react'
import { toast } from 'react-toastify'

interface Props {
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

const Create: FC<Props> = ({ responsaveis, cidades }) => {
    const { auth } = usePage<SharedData>().props
    const userCidadeId = String(auth?.user?.cidade_base_id ?? auth?.user?.voluntario?.cidade_base_id ?? '')
    const hoje = hojeLocal()

    const { data, setData, transform, post, processing, errors } = useForm<EventoFormValues>({
        titulo: '',
        tipo: '',
        descricao: '',
        local: '',
        cidade_id: userCidadeId,
        data: hoje,
        hora_inicio: '',
        data_fim: hoje,
        sem_hora_fim: true,
        hora_fim: '',
        limite_inscricao_data: '',
        limite_inscricao_hora: '',
        limite_participantes: '',
        responsavel_id: '',
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
        post(store().url)
    }

    return (
        <PainelLayout>
            <section className="mx-auto w-full max-w-8xl px-4 py-16">
                <div className="flex justify-center">
                    <div className="w-full max-w-7xl">
                        <div className="overflow-hidden rounded-3xl border bg-white">
                            <div className="p-8 md:p-12">
                                <h2 className="mb-8 text-3xl font-bold text-amber-800 md:text-4xl">
                                    Cadastrar evento
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

                            <FormularioRodape
                                voltarHref={index().url}
                                salvar={(
                                    <BotaoSalvar
                                        type="submit"
                                        form="evento-form"
                                        disabled={processing}
                                        salvando={processing}
                                    />
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