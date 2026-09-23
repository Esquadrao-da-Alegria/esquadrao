import { BellRing } from 'lucide-react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { usePushNotifications } from '@/hooks/use-push-notifications';

export default function PushNotificationBanner() {
    const { suportado, permissao, inscrito, carregando, processando, ativarNotificacoes } = usePushNotifications();

    async function ativar() {
        try {
            await ativarNotificacoes();
        } catch (error) {
            console.error('Erro ao ativar notificações pelo banner:', error);
        }
    }

    if (carregando || !suportado || inscrito || permissao === 'denied') {
        return null;
    }

    return (
        <Alert className="mb-4">
            <BellRing />
            <AlertTitle>Ative as notificações</AlertTitle>
            <AlertDescription>
                <p>Receba lembretes de visitas e eventos direto neste dispositivo, mesmo com o navegador fechado.</p>
                <div className="mt-2 flex gap-2">
                    <Button size="sm" onClick={ativar} disabled={processando}>
                        {processando ? 'Ativando...' : 'Ativar notificações'}
                    </Button>
                </div>
            </AlertDescription>
        </Alert>
    );
}
