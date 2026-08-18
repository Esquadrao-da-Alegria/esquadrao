// REACT
import { Link, useForm, usePage } from '@inertiajs/react';
import { type FC } from 'react';

// UI
import VisitaForm from '@/components/Painel/Visita/Formulario/Form';
import PainelLayout from '@/layouts/PainelLayout';
import { hojeLocal } from '@/lib/visita';
import { ArrowLeft, Check } from 'lucide-react';
import { toast } from 'react-toastify';

// TIPOS
import type { Cidade, Hospital, SharedData, User } from '@/types';
import type { DadosFormulario } from '@/types/visita';

// ROTAS
import { index, store } from '@/routes/visitas';

// SERVICES
import { Service } from '@/Services/Visita/Service';

interface Props {
    hospitais: Hospital[];
    cidades?: Cidade[];
    lideres: User[];
}

const Create: FC<Props> = ({ hospitais, cidades = [], lideres }) => {
    const { auth } = usePage<SharedData>().props;

    const { data, setData, transform, post, processing, errors } =
        useForm<DadosFormulario>({
            hospital_id: '',
            ala_unidade_id: null,
            data: hojeLocal(),
            hora_inicio: '',
            hora_fim: '',
            tipo: 'hospital',
            limite_participantes: '',
            lider_id: auth.user.id,
            observacoes: '',
        });

    const handleCampoChange = <K extends keyof DadosFormulario>(
        campo: K,
        valor: DadosFormulario[K],
    ) => {
        setData((prev) => ({ ...prev, [campo]: valor }));
    };

    const handleSubmit = () => {
        const exigeHospital =
            data.tipo === 'hospital' || data.tipo === 'residencia';
        if (
            (exigeHospital && !data.hospital_id) ||
            !data.data ||
            !data.hora_inicio ||
            !data.hora_fim ||
            !data.tipo ||
            !data.lider_id
        ) {
            toast.error('Preencha todos os campos obrigatórios.');
            return;
        }

        transform(() => Service.montarPayload(data, 'criar'));
        post(store().url);
    };

    return (
        <PainelLayout>
            <section className="max-w-8xl mx-auto w-full px-4 py-16">
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
                                            {Object.entries(errors).map(
                                                ([campo, mensagem]) => (
                                                    <li key={campo}>
                                                        {mensagem}
                                                    </li>
                                                ),
                                            )}
                                        </ul>
                                    </div>
                                )}

                                <form
                                    id="visita-form"
                                    onSubmit={(e) => {
                                        e.preventDefault();
                                        handleSubmit();
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
                                        onCampoChange={handleCampoChange}
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
    );
};

export default Create;
