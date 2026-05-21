import PainelLayout from '@/layouts/PainelLayout'
import { User } from '@/types'
import { Link, router } from '@inertiajs/react'
import { create, edit } from '@/routes/voluntarios'
import { IdCard, Pencil, Plus, UserRound } from 'lucide-react'
import React from 'react'

interface Props {
    voluntarios: User[]
}

const Index: React.FC<Props> = ({ voluntarios }) => {
    const handleCriarClick = () => {
        router.visit(create.url())
    }

    return (
        <PainelLayout>
            <div className="mx-auto max-w-7xl px-5 py-8 sm:px-6 lg:px-8">
                <header className="mb-10 flex flex-col gap-4 sm:mb-12 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight text-amber-950 sm:text-3xl">
                            Voluntários
                        </h1>
                        <p className="mt-1 max-w-md text-sm text-amber-900/55">
                            {voluntarios.length === 0
                                ? 'Cadastre o primeiro voluntário com cargos no sistema.'
                                : `${voluntarios.length} ${voluntarios.length === 1 ? 'cadastro' : 'cadastros'}`}
                        </p>
                    </div>
                    <button
                        onClick={handleCriarClick}
                        type="button"
                        className="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 text-sm font-semibold text-amber-700 shadow-sm transition hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 sm:w-auto"
                    >
                        <Plus className="size-5" strokeWidth={2} aria-hidden />
                        Novo voluntário
                    </button>
                </header>

                {voluntarios.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-amber-200 bg-white px-6 py-20 text-center">
                        <p className="text-sm text-amber-900/50">
                            Nenhum voluntário com cargo cadastrado ainda.
                        </p>
                        <button
                            type="button"
                            onClick={handleCriarClick}
                            className="mt-6 inline-flex items-center gap-2 rounded-full border-2 border-amber-600 bg-white px-5 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-50"
                        >
                            <Plus className="size-4" aria-hidden />
                            Adicionar voluntário
                        </button>
                    </div>
                ) : (
                    <ul className="flex w-full flex-col gap-5">
                        {voluntarios.map((voluntario) => (
                            <li key={voluntario.id} className="w-full">
                                <article className="w-full overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm transition duration-300 hover:border-amber-200 hover:shadow-md">
                                    <div className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:gap-6 sm:p-6">
                                        <div className="shrink-0">
                                            <div className="flex size-[4.5rem] items-center justify-center rounded-2xl bg-amber-100 ring-1 ring-amber-100">
                                                <UserRound
                                                    className="size-9 text-amber-800/80"
                                                    strokeWidth={1.5}
                                                    aria-hidden
                                                />
                                            </div>
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <h2 className="text-base font-semibold leading-snug text-amber-950 sm:text-lg">
                                                {voluntario.name}
                                            </h2>
                                            <p className="mt-1 text-sm text-amber-900/55">{voluntario.email}</p>
                                            <div className="mt-3 flex flex-wrap gap-1.5">
                                                {(voluntario.cargos ?? []).map((cargo) => (
                                                    <span
                                                        key={cargo.id}
                                                        className="inline-flex items-center gap-1 rounded-full border border-amber-200/80 bg-amber-50/80 px-2.5 py-0.5 text-[11px] font-medium uppercase tracking-wide text-amber-900"
                                                    >
                                                        <IdCard
                                                            className="size-3 opacity-70"
                                                            aria-hidden
                                                        />
                                                        {cargo.nome}
                                                    </span>
                                                ))}
                                            </div>
                                        </div>
                                        <div className="flex shrink-0 items-center justify-end border-t border-amber-50 pt-4 sm:w-auto sm:border-t-0 sm:border-l sm:border-amber-50 sm:pl-6 sm:pt-0">
                                            <Link
                                                href={edit.url(voluntario.id)}
                                                prefetch
                                                className="inline-flex size-9 items-center justify-center rounded-full text-amber-700 opacity-80 transition hover:bg-amber-50 hover:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500"
                                                aria-label={`Editar ${voluntario.name}`}
                                            >
                                                <Pencil size={17} strokeWidth={1.75} />
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
    )
}

export default Index
