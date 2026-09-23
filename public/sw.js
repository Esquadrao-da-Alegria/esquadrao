self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener('push', (event) => {
    let data = {};

    try {
        if (event.data) {
            data = event.data.json();
        }
    } catch (e) {
        data = {
            title: 'Esquadrão da Alegria',
            body: event.data ? event.data.text() : 'Você possui uma nova notificação.',
        };
    }

    const title = data.title || 'Esquadrão da Alegria';
    const options = {
        body: data.body || 'Você possui uma nova notificação.',
        icon: data.icon || '/assets/images/logo-colorida.png',
        badge: data.badge || '/assets/images/icons8-clown-30.png',
        data: {
            url: data.url || data.notifURL || '/',
        },
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = new URL(
        event.notification.data?.url || '/',
        self.location.origin
    ).href;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (const client of windowClients) {
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }

            const focusableClient = windowClients.find((client) => 'focus' in client);
            if (focusableClient && 'navigate' in focusableClient) {
                return focusableClient
                    .navigate(targetUrl)
                    .then((navigatedClient) => (navigatedClient || focusableClient).focus())
                    .catch(() => clients.openWindow ? clients.openWindow(targetUrl) : focusableClient.focus());
            }
            if (focusableClient) {
                return focusableClient.focus();
            }

            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});