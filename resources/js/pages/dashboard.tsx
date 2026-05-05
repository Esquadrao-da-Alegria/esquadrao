import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import PainelLayout from '@/layouts/PainelLayout';
import { Head } from '@inertiajs/react';
import { HeartHandshake, LayoutDashboard, Sparkles } from 'lucide-react';

const placeholderCards = [
    {
        title: 'Visão geral',
        description: 'Resumo das suas informações aparecerá aqui.',
        icon: LayoutDashboard,
        accent: 'from-red-500/10 to-red-600/5 text-red-700',
    },
    {
        title: 'Atividades recentes',
        description: 'Últimas ações e atualizações do painel.',
        icon: Sparkles,
        accent: 'from-purple-500/10 to-purple-600/5 text-purple-700',
    },
    {
        title: 'Próximos passos',
        description: 'Sugestões e lembretes para você acompanhar.',
        icon: HeartHandshake,
        accent: 'from-green-500/10 to-green-600/5 text-green-700',
    },
] as const;

export default function Dashboard() {
    return (
        <PainelLayout>
            <Head title="Painel" />

            <div className="mx-auto max-w-7xl px-6 pb-16">
                <section className="relative overflow-hidden rounded-2xl border border-gray-200 bg-gradient-to-br from-red-50 via-white to-purple-50/50 p-8 shadow-sm md:p-12">
                    <PlaceholderPattern className="pointer-events-none absolute -right-12 -top-12 h-72 w-72 stroke-neutral-900/[0.06] dark:stroke-neutral-100/[0.06] md:h-96 md:w-96" />
                    <div className="relative max-w-2xl">
                        <p className="inline-flex items-center gap-2 rounded-full border border-red-100 bg-white/80 px-3 py-1 text-sm font-medium text-red-800 shadow-sm backdrop-blur-sm">
                            <Sparkles
                                className="size-4 shrink-0 text-red-600"
                                aria-hidden
                            />
                            Área do painel
                        </p>
                        <h1 className="mt-5 text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
                            Bem-vindo
                        </h1>
                        <p className="mt-3 text-base leading-relaxed text-gray-600 md:text-lg">
                            Este é um conteúdo de exemplo. Em breve, você poderá
                            acompanhar indicadores, avisos e atividades
                            importantes do Esquadrão da Alegria neste painel.
                        </p>
                        <div className="mt-8 flex flex-wrap gap-3">
                            <span className="inline-flex h-10 min-w-[8rem] items-center rounded-lg bg-gray-100/90 px-4 text-sm text-gray-400">
                                Placeholder
                            </span>
                            <span className="inline-flex h-10 min-w-[10rem] items-center rounded-lg border border-dashed border-gray-300 bg-white/60 px-4 text-sm text-gray-400">
                                Conteúdo em construção
                            </span>
                        </div>
                    </div>
                </section>

                <div className="mt-8 grid gap-4 md:grid-cols-3">
                    {placeholderCards.map(
                        ({ title, description, icon: Icon, accent }) => (
                            <article
                                key={title}
                                className="group relative overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-gray-300 hover:shadow-md"
                            >
                                <div
                                    className={`mb-4 inline-flex rounded-xl bg-gradient-to-br p-3 ${accent}`}
                                >
                                    <Icon className="size-6" aria-hidden />
                                </div>
                                <h2 className="font-semibold text-gray-900">
                                    {title}
                                </h2>
                                <p className="mt-2 text-sm leading-relaxed text-gray-600">
                                    {description}
                                </p>
                                <div className="mt-4 space-y-2">
                                    <div className="h-2 w-full rounded-full bg-gray-100" />
                                    <div className="h-2 w-4/5 rounded-full bg-gray-100" />
                                </div>
                            </article>
                        ),
                    )}
                </div>

                <section className="relative mt-8 overflow-hidden rounded-xl border border-gray-200 bg-gray-50/80 p-8 md:p-10">
                    <PlaceholderPattern className="pointer-events-none absolute inset-0 size-full stroke-neutral-900/[0.05] dark:stroke-neutral-100/[0.05]" />
                    <div className="relative text-center">
                        <p className="text-sm font-medium uppercase tracking-wide text-gray-500">
                            Em breve
                        </p>
                        <p className="mx-auto mt-2 max-w-lg text-gray-600">
                            Aqui entrará o bloco principal do painel — gráficos,
                            listas ou formulários, quando estiverem definidos.
                        </p>
                        <div className="mx-auto mt-6 flex max-w-md justify-center gap-2">
                            <div className="h-3 flex-1 rounded-full bg-gray-200/80" />
                            <div className="h-3 flex-1 rounded-full bg-gray-200/60" />
                            <div className="h-3 flex-1 rounded-full bg-gray-200/40" />
                        </div>
                    </div>
                </section>
            </div>
        </PainelLayout>
    );
}
