import { Head } from '@inertiajs/react';

import HeadingSmall from '@/components/heading-small';
import PainelLayout from '@/layouts/PainelLayout';
import SettingsLayout from '@/layouts/Settings/Layout';

export default function Appearance() {
    return (
        <PainelLayout>
            <Head title="Aparência" />

            <div className="mx-auto max-w-7xl px-6 pb-16">
                <SettingsLayout>
                    <div className="space-y-6">
                        <HeadingSmall
                            title="Aparência"
                            description="O aplicativo está disponível apenas no tema claro."
                        />
                        <p className="text-sm text-muted-foreground">
                            Não há opção de tema escuro no momento.
                        </p>
                    </div>
                </SettingsLayout>
            </div>
        </PainelLayout>
    );
}
