// REACT/INERTIA
import { Link, router, usePage } from '@inertiajs/react'

// UI
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { toastErro, toastSucesso } from '@/lib/utils/toast'
import { cn } from '@/lib/utils'

// ROTAS
import { edit } from '@/routes/profile'
import { index } from '@/routes/hospitais'
import { index as eventosIndex } from '@/routes/eventos'
import { index as voluntariosIndex } from '@/routes/voluntarios'
import { index as patrocinadoresIndex } from '@/routes/patrocinadores'
import { useEffect, useState } from 'react'
import { dashboard, home, login, logout } from '@/routes'

// TIPOS
import { type SharedData } from '@/types'
import {
    Building2,
    ChevronDown,
    Handshake,
    LayoutGrid,
    LogOut,
    User,
    UsersRound,
    Sparkles,
    type LucideIcon,
} from 'lucide-react'

interface Props {
    children: React.ReactNode
}

/** OKLCH amarelo (~98°) alinhado ao AppSidebarLayout. */
const temaPainelAmarelo = cn(
    '[--primary:oklch(0.82_0.17_98)] [--primary-foreground:oklch(0.22_0.04_98)]',
    '[--sidebar-primary:oklch(0.82_0.17_98)] [--sidebar-primary-foreground:oklch(0.22_0.04_98)]',
    '[--ring:oklch(0.72_0.16_98)] [--sidebar-ring:oklch(0.72_0.16_98)]',
    '[--sidebar-accent:oklch(0.96_0.07_98)] [--sidebar-accent-foreground:oklch(0.28_0.06_98)]',
)

interface ItemNavegacaoPainel {
    titulo: string
    href: ReturnType<typeof dashboard>
    icone: LucideIcon
    ativo: boolean
    visivel: boolean
}

const PainelLayout: React.FC<Props> = ({ children }) => {
    const [mobileOpen, setMobileOpen] = useState(false)
    const { props, url } = usePage<SharedData>()
    const pathname = (url.split('?')[0] ?? '').replace(/\/$/, '') || '/'

    const user = props.auth?.user
    const ehAdministrador = props.eh_administrador === true

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

    const isVoluntariosCreate = pathname === '/voluntarios/create'
    const isVoluntariosNav =
        pathname === '/voluntarios' ||
        /^\/voluntarios\/[^/]+\/edit$/.test(pathname)

    const isPatrocinadoresCreate = pathname === '/patrocinadores/create'
    const isPatrocinadoresNav =
        pathname === '/patrocinadores' ||
        /^\/patrocinadores\/[^/]+\/edit$/.test(pathname)

    const handleLogout = () => {
        router.post(logout.url())
    }

    const navLinkClass =
        'rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900'
    const navLinkActive = (active: boolean) =>
        active
            ? 'bg-amber-50 text-amber-800 ring-1 ring-amber-100'
            : ''

    const sidebarLinkClass =
        'flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-sidebar-accent hover:text-sidebar-accent-foreground'
    const sidebarLinkActive = (active: boolean) =>
        active
            ? 'bg-sidebar-accent font-medium text-sidebar-accent-foreground shadow-[inset_3px_0_0_0_var(--primary)]'
            : ''

    const itensNavegacao: ItemNavegacaoPainel[] = [
        {
            titulo: 'Início',
            href: dashboard(),
            icone: LayoutGrid,
            ativo: isDashboard,
            visivel: true,
        },
        {
            titulo: 'Hospitais',
            href: index(),
            icone: Building2,
            ativo: isHospitaisNav && !isHospitaisCreate,
            visivel: true,
        },
        {
            titulo: 'Voluntários',
            href: voluntariosIndex(),
            icone: UsersRound,
            ativo: isVoluntariosNav && !isVoluntariosCreate,
            visivel: ehAdministrador,
        },
        {
            titulo: 'Patrocinadores',
            href: patrocinadoresIndex(),
            icone: Handshake,
            ativo: isPatrocinadoresNav && !isPatrocinadoresCreate,
            visivel: ehAdministrador,
        },
    ]

    const itensVisiveis = itensNavegacao.filter((item) => item.visivel)

    const menuConta = user ? (
        <DropdownMenu>
            <DropdownMenuTrigger
                className="flex max-w-[14rem] items-center gap-2 rounded-full border border-gray-200 bg-white py-1.5 pl-2 pr-2 text-left text-sm shadow-sm transition hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                aria-label="Menu da conta"
            >
                <span className="flex size-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-800">
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
                    className="flex cursor-pointer items-center gap-2 text-gray-600 focus:text-gray-900"
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
            className="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-800 shadow-sm transition hover:border-amber-200 hover:bg-amber-50"
        >
            Entrar
        </Link>
    )

    return (
        <div
            className={cn(
                'flex min-h-screen flex-col bg-slate-50/80 text-gray-900 sm:flex-row',
                temaPainelAmarelo,
            )}
        >
            {/* Sidebar — apenas desktop (sm+); mobile inalterado */}
            <aside className="hidden shrink-0 flex-col border-r border-primary/15 bg-white shadow-[inset_3px_0_0_0_var(--primary)] sm:flex sm:w-56 lg:w-64">
                <div className="border-b border-primary/10 px-4 py-4">
                    <Link
                        href={dashboard()}
                        className="flex items-center gap-2"
                    >
                        <img
                            src="/assets/images/logo-colorida.png"
                            alt="Esquadrão da Alegria"
                            className="h-10 w-auto"
                        />
                        <span className="text-sm font-semibold tracking-tight text-gray-500">
                            Painel
                        </span>
                    </Link>
                </div>
                <nav className="flex flex-1 flex-col gap-0.5 p-3" aria-label="Menu do painel">
                    <p className="mb-1 px-3 text-xs font-medium text-gray-400">
                        Menu principal
                    </p>
                    {itensVisiveis.map((item) => {
                        const Icone = item.icone

                        return (
                            <Link
                                key={item.titulo}
                                href={item.href}
                                className={`${sidebarLinkClass} ${sidebarLinkActive(
                                    item.ativo,
                                )}`}
                            >
                                <Icone className="size-4 shrink-0 opacity-70" aria-hidden />
                                {item.titulo}
                            </Link>
                        )
                    })}
                </nav>
            </aside>

            <div className="flex min-w-0 flex-1 flex-col">
                <header className="sticky top-0 z-50 w-full bg-white shadow sm:border-b sm:border-gray-200/80">
                    <nav className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:max-w-none sm:px-6">
                        <div className="flex min-w-0 flex-1 items-center gap-6">
                            <Link
                                href={dashboard()}
                                className="flex shrink-0 items-center gap-2 sm:hidden"
                                onClick={closeMobile}
                            >
                                <img
                                    src="/assets/images/logo-colorida.png"
                                    alt="Esquadrão da Alegria"
                                    className="h-12 w-auto"
                                />
                            </Link>

                            <span className="hidden text-sm font-semibold tracking-tight text-gray-500 sm:inline">
                                Painel administrativo
                            </span>

                            {/* Menu mobile */}
                            <div
                                className={`${mobileOpen
                                    ? 'absolute left-0 right-0 top-full z-50 mx-4 mt-2 flex flex-col rounded-xl border border-gray-200 bg-white p-2 shadow-lg'
                                    : 'hidden'
                                    } sm:hidden`}
                            >
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

                                <Link
                                    href={eventosIndex()}
                                    className={`${navLinkClass} ${navLinkActive(
                                        pathname === '/eventos' || /^\/eventos\/[0-9]+\/edit$/.test(pathname),
                                    )} flex items-center gap-2`}
                                    onClick={closeMobile}
                                >
                                    <Sparkles className="size-4 shrink-0 opacity-70" aria-hidden />
                                    Eventos
                                </Link>

                                {ehAdministrador ? (
                                    <>
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
                                            href={voluntariosIndex()}
                                            className={`${navLinkClass} ${navLinkActive(
                                                isVoluntariosNav && !isVoluntariosCreate,
                                            )} flex items-center gap-2`}
                                            onClick={closeMobile}
                                        >
                                            <UsersRound className="size-4 shrink-0 opacity-70" aria-hidden />
                                            Voluntários
                                        </Link>

                                        <Link
                                            href={patrocinadoresIndex()}
                                            className={`${navLinkClass} ${navLinkActive(
                                                isPatrocinadoresNav && !isPatrocinadoresCreate,
                                            )} flex items-center gap-2`}
                                            onClick={closeMobile}
                                        >
                                            <Handshake className="size-4 shrink-0 opacity-70" aria-hidden />
                                            Patrocinadores
                                        </Link>
                                    </>
                                ) : null}
                            </div>
                        </div>

                        <div className="flex items-center gap-2 sm:ml-auto">
                            <Link
                                href={home()}
                                className="hidden text-sm font-medium text-gray-500 transition hover:text-gray-800 md:inline"
                            >
                                Ver site
                            </Link>

                            {menuConta}

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
        </div>
    )
}

export default PainelLayout
