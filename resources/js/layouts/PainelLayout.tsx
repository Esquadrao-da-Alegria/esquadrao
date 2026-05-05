import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { dashboard, home, login, logout } from '@/routes'
import { create, index } from '@/routes/hospitais'
import { edit } from '@/routes/profile'
import { type SharedData } from '@/types'
import { toastErro, toastSucesso } from '@/lib/utils/toast'
import { Link, router, usePage } from '@inertiajs/react'
import { Building2, ChevronDown, LayoutGrid, LogOut, Plus, User } from 'lucide-react'
import { useEffect, useState } from 'react'

interface Props {
    children: React.ReactNode
}

const PainelLayout: React.FC<Props> = ({ children }) => {
    const [mobileOpen, setMobileOpen] = useState(false)
    const { props, url } = usePage<SharedData>()
    const pathname = (url.split('?')[0] ?? '').replace(/\/$/, '') || '/'

    const user = props.auth?.user

    useEffect(() => {
        const mensagemSucesso = props.mensagem_sucesso
        const mensagemErro = props.mensagem_erro

        if (mensagemSucesso) toastSucesso(mensagemSucesso)
        if (mensagemErro) toastErro(mensagemErro)
    }, [props.mensagem_sucesso, props.mensagem_erro])

    const closeMobile = () => setMobileOpen(false)

    const isDashboard = pathname === '/dashboard'
    const isHospitaisCreate = pathname === '/hospitais/create'
    const isHospitaisNav =
        pathname === '/hospitais' ||
        /^\/hospitais\/[^/]+\/edit$/.test(pathname)

    const handleLogout = () => {
        router.post(logout.url())
    }

    const navLinkClass =
        'rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900'
    const navLinkActive = (active: boolean) =>
        active
            ? 'bg-red-50 text-red-800 ring-1 ring-red-100'
            : ''

    return (
        <div className="flex min-h-screen flex-col bg-slate-50/80 text-gray-900">
            <header className="sticky top-0 z-50 border-b border-gray-200/80 bg-white/95 shadow-sm backdrop-blur-md">
                <nav className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
                    <div className="flex min-w-0 flex-1 items-center gap-6">
                        <Link
                            href={dashboard()}
                            className="flex shrink-0 items-center gap-2"
                            onClick={closeMobile}
                        >
                            <img
                                src="/assets/images/logo-colorida.png"
                                alt="Esquadrão da Alegria"
                                className="h-12 w-auto sm:h-14"
                            />
                            <span className="hidden border-l border-gray-200 pl-4 text-sm font-semibold tracking-tight text-gray-500 sm:inline">
                                Painel
                            </span>
                        </Link>

                        <div
                            className={`${mobileOpen
                                ? 'absolute left-0 right-0 top-full z-50 mx-4 mt-2 flex flex-col rounded-xl border border-gray-200 bg-white p-2 shadow-lg sm:static sm:mx-0 sm:mt-0 sm:flex sm:flex-row sm:items-center sm:gap-0.5 sm:border-0 sm:bg-transparent sm:p-0 sm:shadow-none'
                                : 'hidden sm:flex sm:flex-row sm:items-center sm:gap-0.5'
                                }`}
                        >
                            <Link
                                href={dashboard()}
                                className={`${navLinkClass} ${navLinkActive(
                                    isDashboard,
                                )} flex items-center gap-2`}
                                onClick={closeMobile}
                            >
                                <LayoutGrid className="size-4 shrink-0 opacity-70" aria-hidden />
                                Início
                            </Link>
                            <Link
                                href={index()}
                                className={`${navLinkClass} ${navLinkActive(
                                    isHospitaisNav && !isHospitaisCreate,
                                )} flex items-center gap-2`}
                                onClick={closeMobile}
                            >
                                <Building2 className="size-4 shrink-0 opacity-70" aria-hidden />
                                Hospitais
                            </Link>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        <Link
                            href={home()}
                            className="hidden text-sm font-medium text-gray-500 transition hover:text-gray-800 md:inline"
                        >
                            Ver site
                        </Link>

                        {user ? (
                            <DropdownMenu>
                                <DropdownMenuTrigger
                                    className="flex max-w-[14rem] items-center gap-2 rounded-full border border-gray-200 bg-white py-1.5 pl-2 pr-2 text-left text-sm shadow-sm transition hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500/20"
                                    aria-label="Menu da conta"
                                >
                                    <span className="flex size-8 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-800">
                                        <User className="size-4" aria-hidden />
                                    </span>
                                    <span className="hidden min-w-0 flex-1 truncate sm:block">
                                        <span className="block truncate font-medium text-gray-900">
                                            {user.name}
                                        </span>
                                        <span className="block truncate text-xs text-gray-500">
                                            {user.email}
                                        </span>
                                    </span>
                                    <ChevronDown className="hidden size-4 shrink-0 text-gray-400 sm:block" />
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" className="w-56">
                                    <DropdownMenuLabel className="font-normal">
                                        Conta
                                    </DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem asChild>
                                        <Link
                                            href={edit()}
                                            className="flex cursor-pointer items-center gap-2"
                                        >
                                            <User className="size-4 shrink-0" />
                                            Perfil
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        className="flex cursor-pointer items-center gap-2 text-red-700 focus:text-red-800"
                                        onSelect={() => {
                                            handleLogout()
                                        }}
                                    >
                                        <LogOut className="size-4 shrink-0" />
                                        Sair
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        ) : (
                            <Link
                                href={login()}
                                className="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-800 shadow-sm transition hover:border-red-200 hover:bg-red-50"
                            >
                                Entrar
                            </Link>
                        )}

                        <button
                            type="button"
                            onClick={() => setMobileOpen(!mobileOpen)}
                            className="flex items-center justify-center rounded-lg p-2 text-gray-600 transition hover:bg-gray-100 focus:outline-none sm:hidden"
                            aria-expanded={mobileOpen}
                            aria-label={mobileOpen ? 'Fechar menu' : 'Abrir menu'}
                        >
                            {mobileOpen ? (
                                <svg
                                    className="size-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            ) : (
                                <svg
                                    className="size-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                </svg>
                            )}
                        </button>
                    </div>
                </nav>
            </header>

            <main className="flex-1">{children}</main>

            <footer className="border-t border-gray-200 bg-white py-4 text-center text-xs text-gray-500">
                <p>© {new Date().getFullYear()} Esquadrão da Alegria — Painel administrativo</p>
            </footer>
        </div>
    )
}

export default PainelLayout
