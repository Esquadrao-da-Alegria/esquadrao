import { useImageCompressor } from '@/hooks/use-image-compressor';
import PainelLayout from '@/layouts/PainelLayout';
import {
    painelInputClass,
    painelLabelClass,
} from '@/lib/painelFormFieldClasses';
import { destroy, index, update } from '@/routes/patrocinadores';
import { Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Check } from 'lucide-react';
import React from 'react';
import { toast } from 'react-toastify';

interface Patrocinador {
    id?: string;
    nome: string;
    site?: string;
    categoria?: string;
    logo_path?: string;
    ativo: boolean;
    ordem_exibicao: number;
}

interface Props {
    patrocinador: Patrocinador;
}

interface CamposFormulario {
    nome: string;
    site: string;
    categoria: string;
    ativo: boolean;
    logotipo: File | null;
    ordem_exibicao: number | string;
}

const Edit: React.FC<Props> = ({ patrocinador }) => {
    const { data, setData, processing, errors } = useForm<CamposFormulario>({
        nome: patrocinador.nome,
        site: patrocinador.site || '',
        categoria: patrocinador.categoria || '',
        ativo: patrocinador.ativo,
        logotipo: null,
        ordem_exibicao: patrocinador.ordem_exibicao ?? 1,
    });

    const { processImage, isCompressing } = useImageCompressor();

    const handleDataChange = (
        campo: keyof CamposFormulario,
        valor: CamposFormulario[keyof CamposFormulario],
    ) => {
        setData((prevData) => ({
            ...prevData,
            [campo]: valor,
        }));
    };

    const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0] || null;

        if (!file) {
            handleDataChange('logotipo', null);
            return;
        }

        const compressedFile = await processImage(file);
        handleDataChange('logotipo', compressedFile);
    };

    const handleSubmit = () => {
        if (!data.nome) {
            toast.error('O campo nome é obrigatório!');
            return;
        }

        if (data.ordem_exibicao === '' || Number(data.ordem_exibicao) < 0) {
            toast.error('Informe uma ordem de exibição válida!');
            return;
        }

        router.post(update({ patrocinador: patrocinador.id! }).url, {
            ...data,
            _method: 'put',
        });
    };

    const handleDelete = () => {
        if (
            confirm(
                'Tem certeza que deseja excluir este patrocinador? Esta ação não pode ser desfeita.',
            )
        ) {
            router.delete(destroy({ patrocinador: patrocinador.id! }).url, {
                onSuccess: () =>
                    toast.success('Patrocinador excluído com sucesso!'),
                onError: () => toast.error('Erro ao excluir o patrocinador.'),
            });
        }
    };

    const inputClass = `${painelInputClass} border-amber-200 focus:ring-amber-500`;
    const fileInputClass = `${inputClass} file:mr-4 file:rounded-lg file:border file:border-amber-200 file:bg-white file:px-4 file:py-2 file:text-sm file:font-medium file:text-amber-800 hover:file:bg-amber-50`;

    return (
        <PainelLayout>
            <section className="max-w-8xl mx-auto w-full px-4 py-16">
                <div className="flex justify-center">
                    <div className="w-full max-w-7xl">
                        <div className="overflow-hidden rounded-3xl border bg-white">
                            <div className="p-8 md:p-12">
                                <h2 className="mb-8 text-3xl font-bold text-amber-800 md:text-4xl">
                                    Alterar patrocinador
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
                                    id="patrocinador-form"
                                    onSubmit={(e) => {
                                        e.preventDefault();
                                        handleSubmit();
                                    }}
                                    className="space-y-6"
                                >
                                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                        <div className="md:col-span-2">
                                            <label
                                                htmlFor="nome"
                                                className={painelLabelClass}
                                            >
                                                Nome *
                                            </label>
                                            <input
                                                type="text"
                                                name="nome"
                                                id="nome"
                                                required
                                                placeholder="Digite o nome do patrocinador"
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
                                                htmlFor="categoria"
                                                className={painelLabelClass}
                                            >
                                                Categoria
                                            </label>
                                            <input
                                                type="text"
                                                name="categoria"
                                                id="categoria"
                                                placeholder="Ex: Diamante, Ouro, Prata"
                                                value={data.categoria}
                                                onChange={(e) =>
                                                    handleDataChange(
                                                        'categoria',
                                                        e.target.value,
                                                    )
                                                }
                                                className={inputClass}
                                            />
                                        </div>

                                        <div>
                                            <label
                                                htmlFor="ordem_exibicao"
                                                className={painelLabelClass}
                                            >
                                                Ordem de exibição *
                                            </label>
                                            <input
                                                type="number"
                                                name="ordem_exibicao"
                                                id="ordem_exibicao"
                                                required
                                                min="0"
                                                value={data.ordem_exibicao}
                                                onChange={(e) =>
                                                    handleDataChange(
                                                        'ordem_exibicao',
                                                        e.target.value,
                                                    )
                                                }
                                                className={inputClass}
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            htmlFor="site"
                                            className={painelLabelClass}
                                        >
                                            Site
                                        </label>
                                        <input
                                            type="url"
                                            name="site"
                                            id="site"
                                            placeholder="https://www.exemplo.com.br"
                                            value={data.site}
                                            onChange={(e) =>
                                                handleDataChange(
                                                    'site',
                                                    e.target.value,
                                                )
                                            }
                                            className={inputClass}
                                        />
                                    </div>

                                    <div>
                                        <label
                                            htmlFor="logotipo"
                                            className={painelLabelClass}
                                        >
                                            Atualizar logotipo
                                        </label>
                                        <input
                                            type="file"
                                            name="logotipo"
                                            id="logotipo"
                                            accept="image/*"
                                            onChange={handleFileChange}
                                            className={fileInputClass}
                                        />
                                        {patrocinador.logo_path ? (
                                            <img
                                                src={patrocinador.logo_path}
                                                alt={`Logotipo atual de ${patrocinador.nome}`}
                                                className="mt-3 h-20 w-20 rounded-2xl object-cover ring-1 ring-amber-100"
                                            />
                                        ) : null}
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
                                            Ativo (visível no site)
                                        </label>
                                    </div>
                                </form>
                            </div>

                            <div className="flex flex-col gap-3 border-t bg-white px-8 py-6 sm:flex-row sm:items-center sm:justify-between md:px-12">
                                <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <Link
                                        href={index().url}
                                        className="inline-flex items-center justify-center gap-2 rounded-full border px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                                    >
                                        <ArrowLeft
                                            className="size-4"
                                            aria-hidden
                                        />
                                        Voltar
                                    </Link>

                                    <button
                                        type="button"
                                        onClick={handleDelete}
                                        disabled={processing}
                                        className="inline-flex items-center justify-center rounded-full border-2 border-red-500 px-5 py-3 font-semibold text-red-600 transition hover:bg-red-50 disabled:opacity-70"
                                    >
                                        Excluir
                                    </button>
                                </div>

                                <button
                                    type="submit"
                                    form="patrocinador-form"
                                    disabled={processing || isCompressing}
                                    className="inline-flex items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 font-semibold text-amber-700 transition hover:bg-amber-50 disabled:opacity-70"
                                >
                                    <Check className="size-4" aria-hidden />
                                    {isCompressing
                                        ? 'Otimizando imagem...'
                                        : processing
                                          ? 'Salvando...'
                                          : 'Salvar'}
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
