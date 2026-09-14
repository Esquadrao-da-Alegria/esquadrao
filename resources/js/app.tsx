import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
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

console.log("APP.TSX FOI EXECUTADO");

if ("serviceWorker" in navigator) {
    window.addEventListener("load", async () => {
        try {
            const registration =
                await navigator.serviceWorker.register("/sw.js");

            console.log("Service Worker registrado:", registration);
        } catch (error) {
            console.error("Erro ao registrar Service Worker:", error);
        }
    });
}

async function registrarPushSubscription() {
    try {
        const registration = await navigator.serviceWorker.ready;

        const subscription =
            await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey:
                    import.meta.env.VITE_VAPID_PUBLIC_KEY,
            });

        console.log("Push Subscription criada:", subscription);

        const response = await fetch("/push-subscriptions", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") ?? "",
            },
            body: JSON.stringify(subscription.toJSON()),
        });

        console.log("Resposta do Laravel:", response.status);
    } catch (error) {
        console.error("Erro ao registrar Push Subscription:", error);
    }
}


initializeTheme();
