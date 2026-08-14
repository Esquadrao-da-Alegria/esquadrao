import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import PainelLayout from '@/layouts/PainelLayout';
import { Head } from '@inertiajs/react';
import { ChartNoAxesCombined } from 'lucide-react';

interface Props {
    titulo: string;
    descricao: string;
}

export default function Gerencial({ titulo, descricao }: Props) {
    return (
        <PainelLayout>
            <Head title={titulo} />

            <div className="mx-auto max-w-7xl px-6 pb-16">
                <section className="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-8 shadow-sm md:p-12">
                    <PlaceholderPattern className="pointer-events-none absolute inset-0 size-full stroke-neutral-900/[0.05] dark:stroke-neutral-100/[0.05]" />
                    <div className="relative max-w-2xl">
                        <span className="inline-flex rounded-xl bg-amber-100 p-3 text-amber-800">
                            <ChartNoAxesCombined className="size-6" aria-hidden />
                        </span>
                        <h1 className="mt-5 text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
                            {titulo}
                        </h1>
                        <p className="mt-3 text-base leading-relaxed text-gray-600 md:text-lg">
                            {descricao}
                        </p>
                        <p className="mt-6 text-sm font-medium text-gray-500">
                            Dashboard em construção.
                        </p>
                    </div>
                </section>
            </div>
        </PainelLayout>
    );
}
