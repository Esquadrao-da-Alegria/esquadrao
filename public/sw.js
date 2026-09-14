
self.addEventListener('push', (event) => {
    const notification = event.data.json();

    self.registration.showNotification(notification.title, {
        body: notification.body,
        data: {
            notifURL: notification.url
        }

    });
})

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data.notifURL)
    );
})