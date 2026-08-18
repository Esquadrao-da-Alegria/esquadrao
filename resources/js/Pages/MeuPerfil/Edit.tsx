import PainelLayout from '@/layouts/PainelLayout';
import {
    painelInputClass,
    painelLabelClass,
} from '@/lib/painelFormFieldClasses';
import { update } from '@/routes/meu-perfil';
import {
    destroy as destroyFoto,
    update as updateFoto,
} from '@/routes/meu-perfil/foto';
import { type SharedData } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Check, Trash2, Upload, User } from 'lucide-react';
import { type FC, useRef, useState } from 'react';

interface VoluntarioPerfil {
    id: number;
    nome_completo: string;
    nome_doutor: string | null;
    telefone: string | null;
    data_nascimento: string | null;
    status: string;
    data_entrada_ong: string | null;
    url_foto: string | null;
    cidade_base: { nome: string } | null;
}

interface Props {
    voluntario: VoluntarioPerfil | null;
}

const LABEL_STATUS: Record<string, string> = {
    ativo: 'Ativo',
    inativo: 'Inativo',
    convite_enviado: 'Convite enviado',
};

const Edit: FC<Props> = ({ voluntario }) => {
    const { auth } = usePage<SharedData>().props;
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [fotoPreview, setFotoPreview] = useState<string | null>(
        voluntario?.url_foto ?? null,
    );
    const [uploadando, setUploadando] = useState(false);

    const ehArtista = (auth.user.cargos ?? []).some(
        (c) => c.slug === 'artista',
    );
    const cargos = auth.user.cargos ?? [];

    const { data, setData, patch, processing, errors, recentlySuccessful } =
        useForm({
            nome_completo: voluntario?.nome_completo ?? '',
            nome_doutor: voluntario?.nome_doutor ?? '',
            telefone: voluntario?.telefone ?? '',
            data_nascimento: voluntario?.data_nascimento ?? '',
        });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(update.url());
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (ev) => setFotoPreview(ev.target?.result as string);
        reader.readAsDataURL(file);

        setUploadando(true);
        router.post(
            updateFoto.url(),
            { foto: file },
            {
                forceFormData: true,
                onError: () => setFotoPreview(voluntario?.url_foto ?? null),
                onFinish: () => setUploadando(false),
            },
        );
        e.target.value = '';
    };

    const handleRemoverFoto = () => {
        router.delete(destroyFoto.url(), {
            onSuccess: () => setFotoPreview(null),
        });
    };

    if (!voluntario) {
        return (
            <PainelLayout>
                <Head title="Meu perfil" />
                <section className="mx-auto max-w-3xl px-4 py-16">
                    <p className="text-gray-600">
                        Perfil de voluntário não encontrado para este usuário.
                    </p>
                </section>
            </PainelLayout>
        );
    }

    return (
        <PainelLayout>
            <Head title="Meu perfil" />
            <section className="mx-auto w-full max-w-3xl px-4 py-16">
                <h1 className="mb-8 text-3xl font-bold text-amber-800">
                    Meu perfil
                </h1>

                {/* Foto de perfil */}
                <div className="mb-6 rounded-3xl border bg-white p-8">
                    <h2 className="mb-4 text-lg font-semibold text-gray-800">
                        Foto de perfil
                    </h2>
                    <div className="flex flex-wrap items-center gap-6">
                        <div className="relative h-24 w-24 flex-shrink-0 overflow-hidden rounded-full border-2 border-amber-200 bg-gray-100">
                            {fotoPreview ? (
                                <img
                                    src={fotoPreview}
                                    alt="Foto de perfil"
                                    className="h-full w-full object-cover"
                                />
                            ) : (
                                <div className="flex h-full w-full items-center justify-center text-gray-400">
                                    <User className="size-10" />
                                </div>
                            )}
                        </div>
                        <div className="flex flex-col gap-2">
                            <button
                                type="button"
                                disabled={uploadando}
                                onClick={() => fileInputRef.current?.click()}
                                className="inline-flex items-center gap-2 rounded-full border-2 border-amber-600 bg-white px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-50 disabled:opacity-60"
                            >
                                <Upload className="size-4" />
                                {uploadando
                                    ? 'Enviando...'
                                    : fotoPreview
                                      ? 'Alterar foto'
                                      : 'Adicionar foto'}
                            </button>
                            {fotoPreview && (
                                <button
                                    type="button"
                                    onClick={handleRemoverFoto}
                                    className="inline-flex items-center gap-2 rounded-full border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                                >
                                    <Trash2 className="size-4" />
                                    Remover foto
                                </button>
                            )}
                            <p className="text-xs text-gray-500">
                                JPG, PNG ou WebP. Máximo 2 MB.
                            </p>
                        </div>
                        <input
                            ref={fileInputRef}
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            className="hidden"
                            onChange={handleFileChange}
                        />
                    </div>
                </div>

                {/* Dados pessoais */}
                <form
                    onSubmit={handleSubmit}
                    className="mb-6 rounded-3xl border bg-white p-8"
                >
                    <h2 className="mb-4 text-lg font-semibold text-gray-800">
                        Dados pessoais
                    </h2>
                    <div className="space-y-4">
                        <div>
                            <label
                                htmlFor="nome_completo"
                                className={painelLabelClass}
                            >
                                Nome completo *
                            </label>
                            <input
                                id="nome_completo"
                                required
                                value={data.nome_completo}
                                onChange={(e) =>
                                    setData('nome_completo', e.target.value)
                                }
                                className={painelInputClass}
                            />
                            {errors.nome_completo && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.nome_completo}
                                </p>
                            )}
                        </div>

                        {ehArtista && (
                            <div>
                                <label
                                    htmlFor="nome_doutor"
                                    className={painelLabelClass}
                                >
                                    Nome doutor
                                </label>
                                <input
                                    id="nome_doutor"
                                    value={data.nome_doutor}
                                    onChange={(e) =>
                                        setData('nome_doutor', e.target.value)
                                    }
                                    className={painelInputClass}
                                    placeholder="Ex: Dr. Alegria"
                                />
                                {errors.nome_doutor && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.nome_doutor}
                                    </p>
                                )}
                            </div>
                        )}

                        <div>
                            <label
                                htmlFor="telefone"
                                className={painelLabelClass}
                            >
                                Telefone
                            </label>
                            <input
                                id="telefone"
                                value={data.telefone}
                                onChange={(e) =>
                                    setData('telefone', e.target.value)
                                }
                                className={painelInputClass}
                                placeholder="(00) 00000-0000"
                            />
                            {errors.telefone && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.telefone}
                                </p>
                            )}
                        </div>

                        <div>
                            <label
                                htmlFor="data_nascimento"
                                className={painelLabelClass}
                            >
                                Data de nascimento
                            </label>
                            <input
                                type="date"
                                id="data_nascimento"
                                value={data.data_nascimento}
                                onChange={(e) =>
                                    setData('data_nascimento', e.target.value)
                                }
                                className={painelInputClass}
                            />
                            {errors.data_nascimento && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.data_nascimento}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="mt-6 flex items-center gap-4">
                        <button
                            type="submit"
                            disabled={processing}
                            className="inline-flex items-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 font-semibold text-amber-700 transition hover:bg-amber-50 disabled:opacity-70"
                        >
                            <Check className="size-4" />
                            {processing ? 'Salvando...' : 'Salvar'}
                        </button>
                        {recentlySuccessful && (
                            <p className="text-sm text-green-600">Salvo!</p>
                        )}
                    </div>
                </form>

                {/* Informações da ONG — somente leitura */}
                <div className="rounded-3xl border bg-white p-8">
                    <h2 className="mb-4 text-lg font-semibold text-gray-800">
                        Informações da ONG
                    </h2>
                    <dl className="space-y-3 text-sm">
                        <div>
                            <dt className="font-medium text-gray-500">
                                Cidade base
                            </dt>
                            <dd className="mt-0.5 text-gray-900">
                                {voluntario.cidade_base?.nome ?? '—'}
                            </dd>
                        </div>
                        <div>
                            <dt className="font-medium text-gray-500">
                                Status
                            </dt>
                            <dd className="mt-0.5 text-gray-900">
                                {LABEL_STATUS[voluntario.status] ??
                                    voluntario.status}
                            </dd>
                        </div>
                        {voluntario.data_entrada_ong && (
                            <div>
                                <dt className="font-medium text-gray-500">
                                    Membro desde
                                </dt>
                                <dd className="mt-0.5 text-gray-900">
                                    {new Date(
                                        voluntario.data_entrada_ong +
                                            'T00:00:00',
                                    ).toLocaleDateString('pt-BR')}
                                </dd>
                            </div>
                        )}
                        {cargos.length > 0 && (
                            <div>
                                <dt className="font-medium text-gray-500">
                                    Cargos
                                </dt>
                                <dd className="mt-0.5 text-gray-900">
                                    {cargos.map((c) => c.nome).join(', ')}
                                </dd>
                            </div>
                        )}
                    </dl>
                    <p className="mt-4 text-xs text-gray-400">
                        Essas informações são gerenciadas pela administração.
                    </p>
                </div>
            </section>
        </PainelLayout>
    );
};

export default Edit;
