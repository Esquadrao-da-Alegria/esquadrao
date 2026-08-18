import PainelLayout from '@/layouts/PainelLayout';
import { create, edit } from '@/routes/hospitais';
import { Hospital } from '@/types';
import { Link, router } from '@inertiajs/react';
import { MapPin, Pencil, Plus } from 'lucide-react';

const placeholderImg =
    'https://media.istockphoto.com/id/1147544807/vector/thumbnail-image-vector-graphic.jpg?s=612x612&w=0&k=20&c=rnCKVbdxqkjlcs3xH87-9gocETqpspHFXu5dIGB4wuM=';

interface Props {
    hospitais: Hospital[];
}

const Index: React.FC<Props> = ({ hospitais }) => {
    const handleCriarClick = () => {
        router.visit(create.url());
    };

    return (
        <PainelLayout>
            <div className="mx-auto max-w-7xl px-5 py-8 sm:px-6 lg:px-8">
                <header className="mb-10 flex flex-col gap-4 sm:mb-12 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight text-amber-950 sm:text-3xl">
                            Hospitais
                        </h1>
                        <p className="mt-1 max-w-md text-sm text-amber-900/55">
                            {hospitais.length === 0
                                ? 'Cadastre o primeiro hospital para começar.'
                                : `${hospitais.length} ${hospitais.length === 1 ? 'cadastro' : 'cadastros'}`}
                        </p>
                    </div>
                    <button
                        onClick={handleCriarClick}
                        type="button"
                        className="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 text-sm font-semibold text-amber-700 shadow-sm transition hover:bg-amber-50 focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:outline-none sm:w-auto"
                    >
                        <Plus className="size-5" strokeWidth={2} aria-hidden />
                        Novo hospital
                    </button>
                </header>

                {hospitais.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-amber-200 bg-white px-6 py-20 text-center">
                        <p className="text-sm text-amber-900/50">
                            Nenhum hospital na lista.
                        </p>
                        <button
                            type="button"
                            onClick={handleCriarClick}
                            className="mt-6 inline-flex items-center gap-2 rounded-full border-2 border-amber-600 bg-white px-5 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-50"
                        >
                            <Plus className="size-4" aria-hidden />
                            Adicionar hospital
                        </button>
                    </div>
                ) : (
                    <ul className="flex w-full flex-col gap-5">
                        {hospitais.map((hospital) => (
                            <li key={hospital.id} className="w-full">
                                <article className="w-full overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm transition duration-300 hover:border-amber-200 hover:shadow-md">
                                    <div className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:gap-6 sm:p-6">
                                        <div className="shrink-0">
                                            <img
                                                src={
                                                    hospital.url_foto ??
                                                    placeholderImg
                                                }
                                                alt={hospital.nome}
                                                className="size-[4.5rem] rounded-2xl object-cover ring-1 ring-amber-100"
                                            />
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <h2 className="text-base leading-snug font-semibold text-amber-950 sm:text-lg">
                                                {hospital.nome}
                                            </h2>
                                            <p className="mt-1.5 flex gap-1.5 text-sm leading-relaxed text-amber-900/50">
                                                <MapPin
                                                    className="mt-0.5 size-3.5 shrink-0 text-amber-700/40"
                                                    strokeWidth={2}
                                                    aria-hidden
                                                />
                                                <span>{hospital.endereco}</span>
                                            </p>
                                        </div>
                                        <div className="flex shrink-0 items-center justify-between gap-4 border-t border-amber-50 pt-4 sm:w-auto sm:justify-end sm:border-t-0 sm:border-l sm:border-amber-50 sm:pt-0 sm:pl-6">
                                            <span
                                                className={`inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-medium tracking-wide uppercase ${
                                                    hospital.ativo
                                                        ? 'border-amber-200/80 bg-white text-amber-800'
                                                        : 'border-transparent bg-amber-50 text-amber-700'
                                                }`}
                                            >
                                                {hospital.ativo
                                                    ? 'Ativo'
                                                    : 'Inativo'}
                                            </span>
                                            <Link
                                                href={edit.url(hospital.id!)}
                                                prefetch
                                                className="inline-flex size-9 items-center justify-center rounded-full text-amber-700 opacity-80 transition hover:bg-amber-50 hover:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500"
                                                aria-label={`Editar ${hospital.nome}`}
                                            >
                                                <Pencil
                                                    size={17}
                                                    strokeWidth={1.75}
                                                />
                                            </Link>
                                        </div>
                                    </div>
                                </article>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </PainelLayout>
    );
};

export default Index;
