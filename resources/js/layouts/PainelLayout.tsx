// REACT/INERTIA
import { useEffect, useState } from 'react';
import { Link, router, usePage, type InertiaLinkProps } from '@inertiajs/react';

// UI
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { cn } from '@/lib/utils';
import { itensDashboards } from '@/lib/dashboard';
import { toastAviso, toastErro, toastSucesso } from '@/lib/utils/toast';

// ROTAS
import { dashboard, home, login, logout } from '@/routes';
import { index } from '@/routes/hospitais';
import { edit } from '@/routes/profile';
import { index as eventosIndex } from '@/routes/eventos'
import { index as visitasIndex } from '@/routes/visitas'
import { index as voluntariosIndex } from '@/routes/voluntarios';
import { index as patrocinadoresIndex } from '@/routes/patrocinadores';

const ROTA_METAS = '/hospitais/metas';
const ROTA_AGENDA_LIBERACAO = '/visitas/agenda-liberacao';

// TIPOS
import { type SharedData } from '@/types';
import {
    Building2,
    CalendarDays,
    ChevronDown,
    ChevronRight,
    CircleHelp,
    Handshake,
    LayoutDashboard,
    LogOut,
    Settings2,
    Target,
    Unlock,
    User,
    UsersRound,
    type LucideIcon,
} from 'lucide-react';

interface Props {
    children: React.ReactNode;
}

/** OKLCH amarelo (~98°) alinhado ao AppSidebarLayout. */
const temaPainelAmarelo = cn(
    '[--primary-foreground:oklch(0.22_0.04_98)] [--primary:oklch(0.82_0.17_98)]',
    '[--sidebar-primary-foreground:oklch(0.22_0.04_98)] [--sidebar-primary:oklch(0.82_0.17_98)]',
    '[--ring:oklch(0.72_0.16_98)] [--sidebar-ring:oklch(0.72_0.16_98)]',
    '[--sidebar-accent-foreground:oklch(0.28_0.06_98)] [--sidebar-accent:oklch(0.96_0.07_98)]',
);

interface ItemNavegacaoPainel {
    titulo: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icone: LucideIcon;
    ativo: boolean;
    visivel: boolean;
}

const PainelLayout: React.FC<Props> = ({ children }) => {
    const [mobileOpen, setMobileOpen] = useState(false);
    const { props, url } = usePage<SharedData>();
    const pathname = (url.split('?')[0] ?? '').replace(/\/$/, '') || '/';
    const rotaDashboardAtiva =
        pathname === '/dashboard' || pathname.startsWith('/dashboards/');
    const rotaAvancadoAtiva =
        pathname === ROTA_METAS || pathname === ROTA_AGENDA_LIBERACAO;
    const [dashboardsOpen, setDashboardsOpen] = useState(rotaDashboardAtiva);
    const [avancadoOpen, setAvancadoOpen] = useState(rotaAvancadoAtiva);

    const user = props.auth?.user;
    const ehAdministrador = props.eh_administrador === true;
    const ehGestor = props.eh_gestor === true;
    const dashboardsVisiveis = itensDashboards.filter(
        (item) => item.permissao === null || props.permissoes_dashboards?.[item.permissao] === true,
    );

    useEffect(() => {
        const mensagemSucesso = props.mensagem_sucesso;
        const mensagemErro = props.mensagem_erro;
        const mensagemAlerta = props.mensagem_alerta;

        if (mensagemSucesso) toastSucesso(mensagemSucesso);
        if (mensagemErro) toastErro(mensagemErro);
        if (mensagemAlerta) toastAviso(mensagemAlerta);
    }, [
        props.mensagem_sucesso,
        props.mensagem_erro,
        props.mensagem_alerta,
    ]);

    useEffect(() => {
        if (rotaDashboardAtiva) setDashboardsOpen(true);
    }, [rotaDashboardAtiva]);

    useEffect(() => {
        if (rotaAvancadoAtiva) setAvancadoOpen(true);
    }, [rotaAvancadoAtiva]);

    const closeMobile = () => setMobileOpen(false);

    const isAjuda = pathname === '/ajuda';
    const isHospitaisCreate = pathname === '/hospitais/create';
    const isHospitaisNav =
        pathname === '/hospitais' ||
        /^\/hospitais\/[^/]+\/edit$/.test(pathname);

    const isVoluntariosCreate = pathname === '/voluntarios/create';
    const isVoluntariosNav =
        pathname === '/voluntarios' ||
        /^\/voluntarios\/[^/]+\/edit$/.test(pathname);

    const isPatrocinadoresCreate = pathname === '/patrocinadores/create';
    const isPatrocinadoresNav =
        pathname === '/patrocinadores' ||
        /^\/patrocinadores\/[^/]+\/edit$/.test(pathname);

    const handleLogout = () => {
        router.post(logout.url());
    };

    const navLinkClass =
        'rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900';
    const navLinkActive = (active: boolean) =>
        active ? 'bg-amber-50 text-amber-800 ring-1 ring-amber-100' : '';

    const sidebarLinkClass =
        'flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-sidebar-accent hover:text-sidebar-accent-foreground';
    const sidebarLinkActive = (active: boolean) =>
        active
            ? 'bg-sidebar-accent font-medium text-sidebar-accent-foreground shadow-[inset_3px_0_0_0_var(--primary)]'
            : '';

    const chevronSubmenuClass = (aberto: boolean) =>
        cn(
            'size-4 shrink-0 transition-transform duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]',
            aberto && 'rotate-90',
        );

    const itensNavegacao: ItemNavegacaoPainel[] = [
        {
            titulo: 'Hospitais',
            href: index(),
            icone: Building2,
            ativo: isHospitaisNav && !isHospitaisCreate,
            visivel: ehAdministrador,
        },
        {
            titulo: 'Eventos',
            href: eventosIndex(),
            icone: CalendarDays,
            ativo: pathname === '/eventos' || /^\/eventos(\/.*)?$/.test(pathname),
            visivel: true,
        },
        {
            titulo: 'Visitas',
            href: visitasIndex(),
            icone: CalendarDays,
            ativo: pathname === '/visitas',
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
        {
            titulo: 'Ajuda / Tutoriais',
            href: '/ajuda',
            icone: CircleHelp,
            ativo: isAjuda,
            visivel: true,
        },
    ];

    const itensVisiveis = itensNavegacao.filter((item) => item.visivel);
    const indicePatrocinadores = itensVisiveis.findIndex(
        (item) => item.titulo === 'Patrocinadores',
    );
    const itensAntesAvancado =
        indicePatrocinadores >= 0
            ? itensVisiveis.slice(0, indicePatrocinadores + 1)
            : itensVisiveis;
    const itensDepoisAvancado =
        indicePatrocinadores >= 0
            ? itensVisiveis.slice(indicePatrocinadores + 1)
            : [];

    const menuConta = user ? (
        <DropdownMenu>
            <DropdownMenuTrigger
                className="flex max-w-[14rem] items-center gap-2 rounded-full border border-gray-200 bg-white py-1.5 pr-2 pl-2 text-left text-sm shadow-sm transition hover:border-gray-300 hover:bg-gray-50 focus:ring-2 focus:ring-amber-500/20 focus:outline-none"
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
                        handleLogout();
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
    );

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
                <nav
                    className="flex flex-1 flex-col gap-0.5 p-3"
                    aria-label="Menu do painel"
                >
                    <p className="mb-1 px-3 text-xs font-medium text-gray-400">
                        Menu principal
                    </p>
                    {dashboardsVisiveis.length > 0 && (
                        <Collapsible
                            open={dashboardsOpen}
                            onOpenChange={setDashboardsOpen}
                        >
                            <CollapsibleTrigger
                                className={`${sidebarLinkClass} ${sidebarLinkActive(
                                    rotaDashboardAtiva,
                                )}`}
                            >
                                <LayoutDashboard
                                    className="size-4 shrink-0 opacity-70"
                                    aria-hidden
                                />
                                <span className="flex-1 text-left">Dashboards</span>
                                <ChevronRight
                                    className={chevronSubmenuClass(dashboardsOpen)}
                                    aria-hidden
                                />
                            </CollapsibleTrigger>
                            <CollapsibleContent className="mt-0.5 pl-5">
                                <div className="space-y-0.5 py-0.5">
                                {dashboardsVisiveis.map((item) => {
                                    const Icone = item.icone;
                                    const ativo =
                                        pathname === item.caminho || pathname.startsWith(`${item.caminho}/`) ||
                                        pathname === item.caminho;

                                    return (
                                        <Link
                                            key={item.permissao}
                                            href={item.href}
                                            className={`${sidebarLinkClass} ${sidebarLinkActive(
                                                ativo,
                                            )}`}
                                        >
                                            <Icone
                                                className="size-4 shrink-0 opacity-70"
                                                aria-hidden
                                            />
                                            {item.titulo}
                                        </Link>
                                    );
                                })}
                                </div>
                            </CollapsibleContent>
                        </Collapsible>
                    )}
                    {itensAntesAvancado.map((item) => {
                        const Icone = item.icone;

                        return (
                            <Link
                                key={item.titulo}
                                href={item.href}
                                className={`${sidebarLinkClass} ${sidebarLinkActive(
                                    item.ativo,
                                )}`}
                            >
                                <Icone
                                    className="size-4 shrink-0 opacity-70"
                                    aria-hidden
                                />
                                {item.titulo}
                            </Link>
                        );
                    })}
                    {ehGestor && (
                        <Collapsible
                            open={avancadoOpen}
                            onOpenChange={setAvancadoOpen}
                        >
                            <CollapsibleTrigger
                                className={`${sidebarLinkClass} ${sidebarLinkActive(
                                    rotaAvancadoAtiva,
                                )}`}
                            >
                                <Settings2
                                    className="size-4 shrink-0 opacity-70"
                                    aria-hidden
                                />
                                <span className="flex-1 text-left">Avançado</span>
                                <ChevronRight
                                    className={chevronSubmenuClass(avancadoOpen)}
                                    aria-hidden
                                />
                            </CollapsibleTrigger>
                            <CollapsibleContent className="mt-0.5 pl-5">
                                <div className="space-y-0.5 py-0.5">
                                <Link
                                    href={ROTA_METAS}
                                    className={`${sidebarLinkClass} ${sidebarLinkActive(
                                        pathname === ROTA_METAS,
                                    )}`}
                                >
                                    <Target
                                        className="size-4 shrink-0 opacity-70"
                                        aria-hidden
                                    />
                                    Metas
                                </Link>
                                <Link
                                    href={ROTA_AGENDA_LIBERACAO}
                                    className={`${sidebarLinkClass} ${sidebarLinkActive(
                                        pathname === ROTA_AGENDA_LIBERACAO,
                                    )}`}
                                >
                                    <Unlock
                                        className="size-4 shrink-0 opacity-70"
                                        aria-hidden
                                    />
                                    Liberar agendas
                                </Link>
                                </div>
                            </CollapsibleContent>
                        </Collapsible>
                    )}
                    {itensDepoisAvancado.map((item) => {
                        const Icone = item.icone;

                        return (
                            <Link
                                key={item.titulo}
                                href={item.href}
                                className={`${sidebarLinkClass} ${sidebarLinkActive(
                                    item.ativo,
                                )}`}
                            >
                                <Icone
                                    className="size-4 shrink-0 opacity-70"
                                    aria-hidden
                                />
                                {item.titulo}
                            </Link>
                        );
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

                            {/* Menu mobile — mapeamento dinâmico dos itens visíveis */}
                            <div
                                className={`${
                                    mobileOpen
                                        ? 'absolute top-full right-0 left-0 z-50 mx-4 mt-2 flex flex-col rounded-xl border border-gray-200 bg-white p-2 shadow-lg'
                                        : 'hidden'
                                } sm:hidden`}
                            >
                                {dashboardsVisiveis.length > 0 && (
                                    <Collapsible
                                        open={dashboardsOpen}
                                        onOpenChange={setDashboardsOpen}
                                    >
                                        <CollapsibleTrigger
                                            className={`${navLinkClass} ${navLinkActive(
                                                rotaDashboardAtiva,
                                            )} flex w-full items-center gap-2`}
                                        >
                                            <LayoutDashboard
                                                className="size-4 shrink-0 opacity-70"
                                                aria-hidden
                                            />
                                            <span className="flex-1 text-left">
                                                Dashboards
                                            </span>
                                            <ChevronRight
                                                className={chevronSubmenuClass(dashboardsOpen)}
                                                aria-hidden
                                            />
                                        </CollapsibleTrigger>
                                        <CollapsibleContent className="pl-5">
                                            <div className="space-y-0.5 py-0.5">
                                            {dashboardsVisiveis.map((item) => {
                                                const Icone = item.icone;
                                                const ativo =
                                                    pathname === item.caminho || pathname.startsWith(`${item.caminho}/`) ||
                                                    pathname === item.caminho;

                                                return (
                                                    <Link
                                                        key={item.permissao}
                                                        href={item.href}
                                                        className={`${navLinkClass} ${navLinkActive(
                                                            ativo,
                                                        )} flex items-center gap-2`}
                                                        onClick={closeMobile}
                                                    >
                                                        <Icone
                                                            className="size-4 shrink-0 opacity-70"
                                                            aria-hidden
                                                        />
                                                        {item.titulo}
                                                    </Link>
                                                );
                                            })}
                                            </div>
                                        </CollapsibleContent>
                                    </Collapsible>
                                )}
                                {itensAntesAvancado.map((item) => {
                                    const Icone = item.icone;

                                    return (
                                        <Link
                                            key={item.titulo}
                                            href={item.href}
                                            className={`${navLinkClass} ${navLinkActive(
                                                item.ativo,
                                            )} flex items-center gap-2`}
                                            onClick={closeMobile}
                                        >
                                            <Icone
                                                className="size-4 shrink-0 opacity-70"
                                                aria-hidden
                                            />
                                            {item.titulo}
                                        </Link>
                                    );
                                })}
                                {ehGestor && (
                                    <Collapsible
                                        open={avancadoOpen}
                                        onOpenChange={setAvancadoOpen}
                                    >
                                        <CollapsibleTrigger
                                            className={`${navLinkClass} ${navLinkActive(
                                                rotaAvancadoAtiva,
                                            )} flex w-full items-center gap-2`}
                                        >
                                            <Settings2
                                                className="size-4 shrink-0 opacity-70"
                                                aria-hidden
                                            />
                                            <span className="flex-1 text-left">
                                                Avançado
                                            </span>
                                            <ChevronRight
                                                className={chevronSubmenuClass(avancadoOpen)}
                                                aria-hidden
                                            />
                                        </CollapsibleTrigger>
                                        <CollapsibleContent className="pl-5">
                                            <div className="space-y-0.5 py-0.5">
                                            <Link
                                                href={ROTA_METAS}
                                                className={`${navLinkClass} ${navLinkActive(
                                                    pathname === ROTA_METAS,
                                                )} flex items-center gap-2`}
                                                onClick={closeMobile}
                                            >
                                                <Target
                                                    className="size-4 shrink-0 opacity-70"
                                                    aria-hidden
                                                />
                                                Metas
                                            </Link>
                                            <Link
                                                href={ROTA_AGENDA_LIBERACAO}
                                                className={`${navLinkClass} ${navLinkActive(
                                                    pathname === ROTA_AGENDA_LIBERACAO,
                                                )} flex items-center gap-2`}
                                                onClick={closeMobile}
                                            >
                                                <Unlock
                                                    className="size-4 shrink-0 opacity-70"
                                                    aria-hidden
                                                />
                                                Liberar agendas
                                            </Link>
                                            </div>
                                        </CollapsibleContent>
                                    </Collapsible>
                                )}
                                {itensDepoisAvancado.map((item) => {
                                    const Icone = item.icone;

                                    return (
                                        <Link
                                            key={item.titulo}
                                            href={item.href}
                                            className={`${navLinkClass} ${navLinkActive(
                                                item.ativo,
                                            )} flex items-center gap-2`}
                                            onClick={closeMobile}
                                        >
                                            <Icone
                                                className="size-4 shrink-0 opacity-70"
                                                aria-hidden
                                            />
                                            {item.titulo}
                                        </Link>
                                    );
                                })}
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
                                aria-label={
                                    mobileOpen ? 'Fechar menu' : 'Abrir menu'
                                }
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
                    <p>
                        © {new Date().getFullYear()} Esquadrão da Alegria —
                        Painel administrativo
                    </p>
                </footer>
            </div>
        </div>
    );
};

export default PainelLayout;
