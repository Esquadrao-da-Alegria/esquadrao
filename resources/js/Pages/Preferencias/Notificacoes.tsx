import { useState } from 'react';
import { Head } from '@inertiajs/react';
import HeadingSmall from '@/components/heading-small';
import PainelLayout from '@/layouts/PainelLayout';
import SettingsLayout from '@/layouts/Settings/Layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { usePushNotifications } from '@/hooks/use-push-notifications';

interface Props {
    vapidPublicKey?: string;
}

export default function Notificacoes({ vapidPublicKey }: Props) {
    const {
        suportado,
        permissao,
        inscrito,
        carregando,
        processando,
        ativarNotificacoes: ativar,
        desativarNotificacoes: desativar,
    } = usePushNotifications(vapidPublicKey);
    const [mensagem, setMensagem] = useState<{ tipo: 'sucesso' | 'erro' | 'info'; texto: string } | null>(null);

    async function ativarNotificacoes() {
        setMensagem(null);

        try {
            await ativar();

            if (Notification.permission !== 'granted') {
                setMensagem({
                    tipo: 'info',
                    texto: 'Permissão de notificação não foi concedida. Você pode alterá-la nas configurações do seu navegador.',
                });
                return;
            }

            setMensagem({
                tipo: 'sucesso',
                texto: 'Notificações ativadas com sucesso neste dispositivo!',
            });
        } catch (error) {
            console.error('Erro ao ativar notificações:', error);
            setMensagem({
                tipo: 'erro',
                texto: 'Ocorreu um erro ao ativar as notificações neste dispositivo. Tente novamente.',
            });
        }
    }

    async function desativarNotificacoes() {
        setMensagem(null);

        try {
            await desativar();
            setMensagem({
                tipo: 'sucesso',
                texto: 'Notificações desativadas para este dispositivo.',
            });
        } catch (error) {
            console.error('Erro ao desativar notificações:', error);
            setMensagem({
                tipo: 'erro',
                texto: 'Erro ao desativar notificações. Tente novamente.',
            });
        }
    }

    return (
        <PainelLayout>
            <Head title="Notificações" />

            <div className="mx-auto max-w-7xl px-6 pb-16">
                <SettingsLayout>
                    <div className="space-y-6">
                        <HeadingSmall
                            title="Notificações Web Push"
                            description="Receba lembretes de visitas e atividades diretamente neste dispositivo, mesmo com o navegador fechado."
                        />

                        {mensagem && (
                            <Alert
                                variant={mensagem.tipo === 'erro' ? 'destructive' : 'default'}
                                className={mensagem.tipo === 'sucesso' ? 'border-green-600/30 text-green-700 dark:text-green-400' : ''}
                            >
                                <AlertTitle>
                                    {mensagem.tipo === 'sucesso' && 'Sucesso'}
                                    {mensagem.tipo === 'erro' && 'Atenção'}
                                    {mensagem.tipo === 'info' && 'Informação'}
                                </AlertTitle>
                                <AlertDescription>{mensagem.texto}</AlertDescription>
                            </Alert>
                        )}

                        {carregando ? (
                            <p className="text-sm text-muted-foreground">Verificando suporte a notificações no navegador...</p>
                        ) : suportado === false ? (
                            <div className="rounded-lg border border-amber-500/20 bg-amber-500/10 p-4 text-sm space-y-2">
                                <div className="font-semibold text-amber-800 dark:text-amber-300">
                                    Navegador ou dispositivo não suportado
                                </div>
                                <p className="text-muted-foreground">
                                    Este navegador não possui suporte para notificações Web Push.
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    Dica para iOS / iPadOS: instale o aplicativo na sua Tela de Início (Compartilhar &gt; Adicionar à Tela de Início) para habilitar as notificações push no Safari.
                                </p>
                            </div>
                        ) : (
                            <div className="space-y-6">
                                <div className="flex items-center justify-between rounded-lg border p-4">
                                    <div className="space-y-1">
                                        <div className="text-sm font-medium">Status no dispositivo atual</div>
                                        <div className="text-xs text-muted-foreground">
                                            {inscrito
                                                ? 'Notificações ativas e vinculadas à sua conta neste navegador.'
                                                : permissao === 'denied'
                                                    ? 'Permissão bloqueada nas configurações do navegador.'
                                                    : 'Notificações estão desativadas neste dispositivo.'}
                                        </div>
                                    </div>
                                    <Badge variant={inscrito ? 'default' : permissao === 'denied' ? 'destructive' : 'outline'}>
                                        {inscrito ? 'Ativadas' : permissao === 'denied' ? 'Bloqueadas' : 'Desativadas'}
                                    </Badge>
                                </div>

                                {permissao === 'denied' && (
                                    <div className="rounded-lg border border-destructive/20 bg-destructive/10 p-4 text-sm text-destructive">
                                        A permissão de notificação foi bloqueada nas permissões do site. Para receber lembretes, acesse as configurações do navegador ou o ícone ao lado da barra de endereços para permitir notificações para este site.
                                    </div>
                                )}

                                <div className="flex items-center gap-3">
                                    {inscrito ? (
                                        <Button
                                            variant="destructive"
                                            onClick={desativarNotificacoes}
                                            disabled={processando}
                                        >
                                            {processando ? 'Desativando...' : 'Desativar neste dispositivo'}
                                        </Button>
                                    ) : (
                                        <Button
                                            onClick={ativarNotificacoes}
                                            disabled={processando || permissao === 'denied'}
                                        >
                                            {processando ? 'Ativando...' : 'Ativar notificações neste dispositivo'}
                                        </Button>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>
                </SettingsLayout>
            </div>
        </PainelLayout>
    );
}
