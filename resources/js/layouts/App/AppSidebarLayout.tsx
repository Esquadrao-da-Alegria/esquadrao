import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { type PropsWithChildren } from 'react';

/** OKLCH yellow (~98°) as app primary; scoped to this layout subtree. */
const appPrimaryYellow = cn(
    '[--primary-foreground:oklch(0.22_0.04_98)] [--primary:oklch(0.82_0.17_98)]',
    '[--sidebar-primary-foreground:oklch(0.22_0.04_98)] [--sidebar-primary:oklch(0.82_0.17_98)]',
    '[--ring:oklch(0.72_0.16_98)] [--sidebar-ring:oklch(0.72_0.16_98)]',
    '[--sidebar-accent-foreground:oklch(0.28_0.06_98)] [--sidebar-accent:oklch(0.96_0.07_98)]',
    'dark:[--primary-foreground:oklch(0.16_0.03_98)] dark:[--primary:oklch(0.86_0.14_98)]',
    'dark:[--sidebar-primary-foreground:oklch(0.16_0.03_98)] dark:[--sidebar-primary:oklch(0.86_0.14_98)]',
    'dark:[--ring:oklch(0.68_0.14_98)] dark:[--sidebar-ring:oklch(0.68_0.14_98)]',
    'dark:[--sidebar-accent-foreground:oklch(0.96_0.04_98)] dark:[--sidebar-accent:oklch(0.3_0.07_98)]',
);

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: PropsWithChildren<{ breadcrumbs?: BreadcrumbItem[] }>) {
    return (
        <div className={cn('min-h-svh w-full', appPrimaryYellow)}>
            <AppShell variant="sidebar">
                <AppSidebar />
                <AppContent variant="sidebar" className="overflow-x-hidden">
                    <AppSidebarHeader breadcrumbs={breadcrumbs} />
                    {children}
                </AppContent>
            </AppShell>
        </div>
    );
}
