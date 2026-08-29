import VoluntarioFormShow, {
    type VoluntarioFormValues,
} from '@/components/Painel/Voluntario/Form/Show'
import BotaoSalvar from '@/components/Painel/Forms/BotaoSalvar/Show'
import FormularioRodape from '@/components/Painel/Forms/FormularioRodape/Show'
import PainelLayout from '@/layouts/PainelLayout'
import { Cargo } from '@/types'
import { useForm } from '@inertiajs/react'
import { toast } from 'react-toastify'
import React from 'react'
import { index, store } from '@/routes/voluntarios'

interface Props {
    cargos: Cargo[]
}

const Create: React.FC<Props> = ({ cargos }) => {
    const { data, setData, post, processing, errors } = useForm<VoluntarioFormValues>({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        cargo_ids: [],
    })

    const handleFieldChange = <K extends keyof VoluntarioFormValues>(
        campo: K,
        valor: VoluntarioFormValues[K],
    ) => {
        setData((prev) => ({
            ...prev,
            [campo]: valor,
        }))
    }

    const handleSubmit = () => {
        if (!data.name?.trim() || !data.email?.trim()) {
            toast.error('Preencha nome e e-mail.')
            return
        }
        if (!data.password) {
            toast.error('Informe a senha.')
            return
        }
        if (data.cargo_ids.length === 0) {
            toast.error('Selecione pelo menos um cargo.')
            return
        }

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
                                    Cadastrar voluntário
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
                                    id="voluntario-form"
                                    onSubmit={(e) => {
                                        e.preventDefault()
                                        handleSubmit()
                                    }}
                                    className="space-y-6"
                                >
                                    <VoluntarioFormShow
                                        data={data}
                                        errors={errors}
                                        cargos={cargos}
                                        mode="create"
                                        onFieldChange={handleFieldChange}
                                    />
                                </form>
                            </div>

                            <FormularioRodape
                                voltarHref={index().url}
                                salvar={(
                                    <BotaoSalvar
                                        type="submit"
                                        form="voluntario-form"
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
