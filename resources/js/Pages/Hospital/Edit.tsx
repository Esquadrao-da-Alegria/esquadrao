import PainelLayout from '@/layouts/PainelLayout';
import { index, update } from '@/routes/hospitais';
import { AlaHospital, Cidade, Hospital } from '@/types';
import { Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Check } from 'lucide-react';
import React from 'react';
import { toast } from 'react-toastify';

const inputClass =
    'w-full rounded-xl border bg-white px-4 py-3 focus:outline-none focus:ring-2';

const labelClass = 'mb-2 block text-sm font-medium text-amber-900';

interface Props {
    cidades: Cidade[];
    hospital: Hospital;
}

interface CamposFormulario {
    cidade_id: number;
    nome: string;
    cnpj: string;
    endereco: string;
    telefone: string;
    email: string;
    ativo: boolean;
    foto: File | null;
    alas: AlaHospital[];
    observacoes?: string;
}

const Edit: React.FC<Props> = ({ hospital, cidades }) => {
    const [novaAla, setNovaAla] = React.useState('');

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
        observacoes: hospital.observacoes,
    });

    const handleDataChange = (campo: keyof CamposFormulario, valor: any) => {
        setData((prevData) => ({
            ...prevData,
            [campo]: valor,
        }));
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0] || null;

        handleDataChange('foto', file);
    };

    const adicionarAla = () => {
        const ala = novaAla.trim();

        if (!ala) return;

        if (data.alas.some((a) => a.nome === ala)) {
            toast.warning('Essa ala ja foi adicionada.');
            return;
        }

        handleDataChange('alas', [...data.alas, { nome: ala }]);

        setNovaAla('');
    };

    const removerAla = (nome: string) => {
        handleDataChange(
            'alas',
            data.alas.filter((ala) => ala.nome !== nome),
        );
    };

    const handleSubmit = async () => {
        if (!data.nome || !data.cnpj || !data.email || !data.telefone) {
            toast.error('Preencha todos os campos obrigatórios!');
            return;
        }

        const url = update({ hospital: hospital.id! }).url;

        router.post(url, { ...(data as {}), _method: 'put' });
    };

    return (
        <PainelLayout>
            <section className="max-w-8xl mx-auto w-full px-4 py-16">
                <div className="flex justify-center">
                    <div className="w-full max-w-7xl">
                        <div className="overflow-hidden rounded-3xl border bg-white">
                            <div className="p-8 md:p-12">
                                <h2 className="mb-8 text-3xl font-bold text-amber-800 md:text-4xl">
                                    Alterar Hospital
                                </h2>

                                <form
                                    id="hospital-form"
                                    onSubmit={(e) => {
                                        e.preventDefault();
                                        handleSubmit();
                                    }}
                                    className="space-y-6"
                                >
                                    <div>
                                        <label
                                            htmlFor="nome"
                                            className={labelClass}
                                        >
                                            Nome *
                                        </label>
                                        <input
                                            type="text"
                                            name="nome"
                                            id="nome"
                                            required
                                            placeholder="Digite o nome do hospital"
                                            value={data.nome}
                                            onChange={(e) =>
                                                handleDataChange(
                                                    'nome',
                                                    e.target.value,
                                                )
                                            }
                                            className={inputClass}
                                        />
                                    </div>

                                    <div>
                                        <label
                                            htmlFor="cnpj"
                                            className={labelClass}
                                        >
                                            CNPJ *
                                        </label>
                                        <input
                                            type="text"
                                            name="cnpj"
                                            id="cnpj"
                                            required
                                            placeholder="Digite o CNPJ (apenas números)"
                                            value={data.cnpj}
                                            onChange={(e) =>
                                                handleDataChange(
                                                    'cnpj',
                                                    e.target.value,
                                                )
                                            }
                                            maxLength={14}
                                            className={inputClass}
                                        />
                                    </div>

                                    <div>
                                        <label
                                            htmlFor="email"
                                            className={labelClass}
                                        >
                                            Email *
                                        </label>
                                        <input
                                            type="email"
                                            name="email"
                                            id="email"
                                            required
                                            placeholder="Digite o email"
                                            value={data.email}
                                            onChange={(e) =>
                                                handleDataChange(
                                                    'email',
                                                    e.target.value,
                                                )
                                            }
                                            className={inputClass}
                                        />
                                    </div>

                                    <div>
                                        <label
                                            htmlFor="telefone"
                                            className={labelClass}
                                        >
                                            Telefone *
                                        </label>
                                        <input
                                            type="text"
                                            name="telefone"
                                            id="telefone"
                                            required
                                            placeholder="Digite o telefone"
                                            value={data.telefone}
                                            onChange={(e) =>
                                                handleDataChange(
                                                    'telefone',
                                                    e.target.value,
                                                )
                                            }
                                            className={inputClass}
                                        />
                                    </div>

                                    <div>
                                        <label
                                            htmlFor="endereco"
                                            className={labelClass}
                                        >
                                            Endereço *
                                        </label>
                                        <input
                                            type="text"
                                            name="endereco"
                                            id="endereco"
                                            required
                                            placeholder="Ex: R. Prof. Dr. Araújo, 538 - Centro, Pelotas - RS"
                                            value={data.endereco}
                                            onChange={(e) =>
                                                handleDataChange(
                                                    'endereco',
                                                    e.target.value,
                                                )
                                            }
                                            className={inputClass}
                                        />
                                    </div>

                                    <div>
                                        <label
                                            htmlFor="cidade_id"
                                            className={labelClass}
                                        >
                                            Cidade *
                                        </label>

                                        <select
                                            name="cidade_id"
                                            id="cidade_id"
                                            required
                                            value={data.cidade_id}
                                            onChange={(e) =>
                                                handleDataChange(
                                                    'cidade_id',
                                                    Number(e.target.value),
                                                )
                                            }
                                            className={inputClass}
                                        >
                                            <option value="">
                                                Selecione uma cidade...
                                            </option>

                                            {cidades.map((cidade: Cidade) => (
                                                <option
                                                    key={cidade.id}
                                                    value={cidade.id}
                                                >
                                                    {cidade.nome}
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div>
                                        <label
                                            htmlFor="nova_ala"
                                            className={labelClass}
                                        >
                                            Alas do hospital
                                        </label>

                                        <div className="flex items-center gap-2">
                                            <input
                                                type="text"
                                                name="nova_ala"
                                                id="nova_ala"
                                                placeholder="Digite o nome da ala"
                                                value={novaAla}
                                                onChange={(e) =>
                                                    setNovaAla(e.target.value)
                                                }
                                                onKeyDown={(e) => {
                                                    if (e.key === 'Enter') {
                                                        e.preventDefault();
                                                        adicionarAla();
                                                    }
                                                }}
                                                className={inputClass}
                                            />

                                            <button
                                                type="button"
                                                onClick={adicionarAla}
                                                className="shrink-0 rounded-full border-2 border-amber-600 bg-white px-5 py-3 font-semibold text-amber-700 transition hover:bg-amber-50"
                                            >
                                                Adicionar
                                            </button>
                                        </div>

                                        <ul className="mt-3 space-y-2">
                                            {data.alas.map((ala) => (
                                                <li
                                                    key={ala.nome}
                                                    className="flex items-center justify-between rounded-xl border border-amber-200 bg-white px-4 py-2 text-sm text-amber-950"
                                                >
                                                    <span>{ala.nome}</span>

                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            removerAla(ala.nome)
                                                        }
                                                        className="rounded-full px-3 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-50 hover:text-amber-900"
                                                    >
                                                        Remover
                                                    </button>
                                                </li>
                                            ))}
                                        </ul>
                                    </div>

                                    <div>
                                        <label
                                            htmlFor="foto"
                                            className={labelClass}
                                        >
                                            Foto
                                        </label>
                                        <input
                                            type="file"
                                            name="foto"
                                            id="foto"
                                            accept="image/*"
                                            onChange={handleFileChange}
                                            className={`${inputClass} file:mr-4 file:rounded-lg file:border file:border-amber-200 file:bg-white file:px-4 file:py-2 file:text-sm file:font-medium file:text-amber-800 hover:file:bg-amber-50`}
                                        />
                                    </div>

                                    <div>
                                        <label
                                            htmlFor="observacoes"
                                            className={labelClass}
                                        >
                                            Observações
                                        </label>
                                        <textarea
                                            name="observacoes"
                                            id="observacoes"
                                            rows={4}
                                            placeholder="Digite observações adicionais"
                                            value={data.observacoes}
                                            onChange={(e) =>
                                                handleDataChange(
                                                    'observacoes',
                                                    e.target.value,
                                                )
                                            }
                                            className={`${inputClass} resize-none`}
                                        />
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            name="ativo"
                                            id="ativo"
                                            checked={data.ativo}
                                            onChange={(e) =>
                                                handleDataChange(
                                                    'ativo',
                                                    e.target.checked,
                                                )
                                            }
                                            className="h-4 w-4 rounded border-amber-300 text-amber-600 focus:ring-amber-500"
                                        />
                                        <label
                                            htmlFor="ativo"
                                            className="text-sm font-medium text-amber-900"
                                        >
                                            Ativo
                                        </label>
                                    </div>
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
                                    form="hospital-form"
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

export default Edit;
