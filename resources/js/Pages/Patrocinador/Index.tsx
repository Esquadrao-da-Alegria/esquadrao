import PainelLayout from '@/layouts/PainelLayout'
import { create, edit } from '@/routes/patrocinadores'
import { Link, router } from '@inertiajs/react'
import { Handshake, Pencil, Plus } from 'lucide-react'
import React from 'react'

interface Patrocinador {
    id?: string
    nome: string
    site?: string
    categoria?: string
    logo_path?: string
    ativo: boolean
    ordem_exibicao: number
}

interface Props {
    patrocinadores: Patrocinador[]
}

const Index: React.FC<Props> = ({ patrocinadores }) => {
    const handleCriarClick = () => {
        router.visit(create.url())
    }

    return (
        <PainelLayout>
            <div className="mx-auto max-w-7xl px-5 py-8 sm:px-6 lg:px-8">
                <header className="mb-10 flex flex-col gap-4 sm:mb-12 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight text-amber-950 sm:text-3xl">
                            Patrocinadores
                        </h1>
                        <p className="mt-1 max-w-md text-sm text-amber-900/55">
                            {patrocinadores.length === 0
                                ? 'Cadastre o primeiro patrocinador exibido no site.'
                                : `${patrocinadores.length} ${patrocinadores.length === 1 ? 'cadastro' : 'cadastros'}`}
                        </p>
                    </div>
                    <button
                        onClick={handleCriarClick}
                        type="button"
                        className="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 text-sm font-semibold text-amber-700 shadow-sm transition hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 sm:w-auto"
                    >
                        <Plus className="size-5" strokeWidth={2} aria-hidden />
                        Novo patrocinador
                    </button>
                </header>

                {patrocinadores.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-amber-200 bg-white px-6 py-20 text-center">
                        <p className="text-sm text-amber-900/50">
                            Nenhum patrocinador cadastrado ainda.
                        </p>
                        <button
                            type="button"
                            onClick={handleCriarClick}
                            className="mt-6 inline-flex items-center gap-2 rounded-full border-2 border-amber-600 bg-white px-5 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-50"
                        >
                            <Plus className="size-4" aria-hidden />
                            Adicionar patrocinador
                        </button>
                    </div>
                ) : (
                    <ul className="flex w-full flex-col gap-5">
                        {patrocinadores.map((patrocinador) => (
                            <li key={patrocinador.id} className="w-full">
                                <article className="w-full overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm transition duration-300 hover:border-amber-200 hover:shadow-md">
                                    <div className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:gap-6 sm:p-6">
                                        <div className="shrink-0">
                                            {patrocinador.logo_path ? (
                                                <img
                                                    src={patrocinador.logo_path}
                                                    alt={patrocinador.nome}
                                                    className="size-[4.5rem] rounded-2xl object-cover ring-1 ring-amber-100"
                                                />
                                            ) : (
                                                <div className="flex size-[4.5rem] items-center justify-center rounded-2xl bg-amber-100 ring-1 ring-amber-100">
                                                    <Handshake
                                                        className="size-9 text-amber-800/80"
                                                        strokeWidth={1.5}
                                                        aria-hidden
                                                    />
                                                </div>
                                            )}
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <h2 className="text-base font-semibold leading-snug text-amber-950 sm:text-lg">
                                                {patrocinador.nome}
                                            </h2>
                                            {patrocinador.categoria ? (
                                                <p className="mt-1 text-sm text-amber-900/55">
                                                    {patrocinador.categoria}
                                                </p>
                                            ) : null}
                                            <div className="mt-3 flex flex-wrap gap-1.5">
                                                <span className="inline-flex items-center rounded-full border border-amber-200/80 bg-amber-50/80 px-2.5 py-0.5 text-[11px] font-medium uppercase tracking-wide text-amber-900">
                                                    Ordem {patrocinador.ordem_exibicao}
                                                </span>
                                                <span
                                                    className={`inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-medium uppercase tracking-wide ${
                                                        patrocinador.ativo
                                                            ? 'border-amber-200/80 bg-amber-50/80 text-amber-900'
                                                            : 'border-amber-100 bg-white text-amber-900/45'
                                                    }`}
                                                >
                                                    {patrocinador.ativo ? 'Ativo' : 'Inativo'}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="flex shrink-0 items-center justify-end border-t border-amber-50 pt-4 sm:w-auto sm:border-t-0 sm:border-l sm:border-amber-50 sm:pl-6 sm:pt-0">
                                            <Link
                                                href={edit.url(patrocinador.id!)}
                                                prefetch
                                                className="inline-flex size-9 items-center justify-center rounded-full text-amber-700 opacity-80 transition hover:bg-amber-50 hover:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500"
                                                aria-label={`Editar ${patrocinador.nome}`}
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
