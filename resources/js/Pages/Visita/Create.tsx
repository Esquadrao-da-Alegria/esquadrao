// REACT
import { type FC } from 'react'
import { useForm, usePage } from '@inertiajs/react'

// UI
import VisitaForm from '@/components/Painel/Visita/Formulario/Form'
import BotaoSalvar from '@/components/Painel/Forms/BotaoSalvar/Show'
import FormularioRodape from '@/components/Painel/Forms/FormularioRodape/Show'
import PainelLayout from '@/layouts/PainelLayout'
import { hojeLocal } from '@/lib/visita'
import { toast } from 'react-toastify'

// TIPOS
import type { Cidade, Hospital, SharedData, User } from '@/types'
import type { DadosFormulario } from '@/types/visita'

// ROTAS
import { index, store } from '@/routes/visitas'

// SERVICES
import { Service } from '@/Services/Visita/Service'

interface Props {
    hospitais: Hospital[]
    cidades?: Cidade[]
    lideres: User[]
    meses_liberados?: string[]
    meses_liberados_por_cidade?: Record<number, string[]>
    mes_selecionado: string
    cidade_selecionada_id: number
}

const Create: FC<Props> = ({ hospitais, cidades = [], lideres, meses_liberados = [], meses_liberados_por_cidade = {}, mes_selecionado, cidade_selecionada_id }) => {
    const { auth } = usePage<SharedData>().props
    const hoje = hojeLocal()
    const dataInicial = hoje.slice(0, 7) === mes_selecionado
        ? hoje
        : `${mes_selecionado}-01`

    const { data, setData, transform, post, processing, errors } = useForm<DadosFormulario>({
        hospital_id: '',
        ala_unidade_id: null,
        data: dataInicial,
        hora_inicio: '',
        hora_fim: '',
        tipo: 'hospital',
        limite_participantes: '',
        lider_id: auth.user.id,
        observacoes: '',
    })

    const handleCampoChange = <K extends keyof DadosFormulario>(campo: K, valor: DadosFormulario[K]) => {
        setData((prev) => ({ ...prev, [campo]: valor }))
    }

    const handleSubmit = () => {
        const exigeHospital = data.tipo === 'hospital' || data.tipo === 'residencia'
        if ((exigeHospital && !data.hospital_id) || !data.data || !data.hora_inicio || !data.hora_fim || !data.tipo || !data.lider_id) {
            toast.error('Preencha todos os campos obrigatórios.')
            return
        }

        transform(() => Service.montarPayload(data, 'criar'))
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
                                    Cadastrar visita
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
                                        mode="create"
                                        hospitais={hospitais}
                                        cidades={cidades}
                                        lideres={lideres}
                                        meses_liberados={meses_liberados}
                                        meses_liberados_por_cidade={meses_liberados_por_cidade}
                                        cidadeInicialId={cidade_selecionada_id}
                                        onCampoChange={handleCampoChange}
                                    />
                                </form>
                            </div>

                            <FormularioRodape
                                voltarHref={index().url}
                                salvar={(
                                    <BotaoSalvar
                                        type="submit"
                                        form="visita-form"
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
