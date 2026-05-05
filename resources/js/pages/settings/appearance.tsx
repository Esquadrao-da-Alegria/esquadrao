import { Head } from '@inertiajs/react';

import HeadingSmall from '@/components/heading-small';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { edit as editAppearance } from '@/routes/appearance';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Aparência',
        href: editAppearance().url,
    },
];

export default function Appearance() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Aparência" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Aparência"
                        description="O aplicativo está disponível apenas no tema claro."
                    />
                    <p className="text-sm text-muted-foreground">
                        Não há opções de tema escuro neste momento.
                    </p>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
