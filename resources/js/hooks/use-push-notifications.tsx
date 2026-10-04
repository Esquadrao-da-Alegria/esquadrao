import { useCallback, useEffect, useState } from 'react';

function urlBase64ToUint8Array(base64String: string): Uint8Array<ArrayBuffer> {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const buffer = new ArrayBuffer(rawData.length);
    const outputArray = new Uint8Array(buffer);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

export function usePushNotifications(vapidPublicKeyProp?: string) {
    const [suportado, setSuportado] = useState<boolean | null>(null);
    const [permissao, setPermissao] = useState<NotificationPermission | null>(null);
    const [inscrito, setInscrito] = useState<boolean>(false);
    const [carregando, setCarregando] = useState<boolean>(true);
    const [processando, setProcessando] = useState<boolean>(false);

    const publicKey = vapidPublicKeyProp || (import.meta.env.VITE_VAPID_PUBLIC_KEY as string | undefined);

    const verificarEstadoAtual = useCallback(async () => {
        setCarregando(true);
        try {
            const ehSuportado =
                typeof window !== 'undefined' &&
                'serviceWorker' in navigator &&
                'PushManager' in window &&
                'Notification' in window;

            setSuportado(ehSuportado);

            if (!ehSuportado) {
                setCarregando(false);
                return;
            }

            setPermissao(Notification.permission);

            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();
            setInscrito(subscription !== null);
        } catch (error) {
            console.error('Erro ao verificar status de notificações:', error);
            setInscrito(false);
        } finally {
            setCarregando(false);
        }
    }, []);

    useEffect(() => {
        verificarEstadoAtual();
    }, [verificarEstadoAtual]);

    async function ativarNotificacoes(): Promise<void> {
        setProcessando(true);

        try {
            if (!suportado) {
                throw new Error('Notificações Web Push não são suportadas neste navegador.');
            }

            if (!publicKey) {
                throw new Error('A chave pública de notificações (VAPID) não está configurada no servidor.');
            }

            const permissaoConcedida = await Notification.requestPermission();
            setPermissao(permissaoConcedida);

            if (permissaoConcedida !== 'granted') {
                return;
            }

            const registration = await navigator.serviceWorker.register('/sw.js');
            await navigator.serviceWorker.ready;

            let subscription = await registration.pushManager.getSubscription();

            if (!subscription) {
                subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(publicKey),
                });
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

            const response = await fetch('/push-subscriptions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                body: JSON.stringify(subscription.toJSON()),
            });

            if (!response.ok) {
                throw new Error('Falha ao salvar a subscription no servidor.');
            }

            setInscrito(true);
        } finally {
            setProcessando(false);
        }
    }

    async function desativarNotificacoes(): Promise<void> {
        setProcessando(true);

        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();

            if (subscription) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

                await fetch('/push-subscriptions', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({
                        endpoint: subscription.endpoint,
                    }),
                });

                await subscription.unsubscribe();
            }

            setInscrito(false);
        } finally {
            setProcessando(false);
        }
    }

    return {
        suportado,
        permissao,
        inscrito,
        carregando,
        processando,
        ativarNotificacoes,
        desativarNotificacoes,
    } as const;
}
