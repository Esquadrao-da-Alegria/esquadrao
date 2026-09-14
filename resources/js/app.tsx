import '../css/app.css';

import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { initializeTheme } from './hooks/use-appearance';

// Import do Toastify
import 'react-toastify/dist/ReactToastify.css';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.tsx`,
            import.meta.glob('./Pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <>
                <App {...props} />
            </>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});

initializeTheme();

function habilitarTransicaoPagina(): void {
    if (typeof document.startViewTransition !== 'function') {
        return;
    }

    let concluir: (() => void) | null = null;

    const liberar = (): void => {
        concluir?.();
        concluir = null;
    };

    router.on('start', (evento) => {
        if (evento.detail.visit.prefetch || concluir) {
            return;
        }

        document.startViewTransition(() => {
            return new Promise<void>((resolver) => {
                concluir = resolver;
            });
        });
    });

    router.on('finish', liberar);
    router.on('invalid', liberar);
    router.on('exception', liberar);
}

habilitarTransicaoPagina();
