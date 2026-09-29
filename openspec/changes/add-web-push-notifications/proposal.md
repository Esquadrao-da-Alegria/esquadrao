## Why

Voluntários podem perder o prazo de registrar o relatório de uma visita ou esquecer atividades nas quais estão inscritos. O Esquadrão precisa de lembretes confiáveis, entregues mesmo com o web app fechado, sem expor dados sensíveis nem interferir nos fluxos de visitas e eventos.

## What Changes

- Disponibilizar Web Push opt-in para usuários autenticados, com suporte a múltiplos dispositivos, gerenciamento de subscriptions e mensagem amigável em navegadores incompatíveis.
- Registrar um service worker e manifest PWA para receber notificações e encaminhar o clique, após autenticação e autorização, à atividade correta.
- Criar uma entrega reutilizável de Web Push no backend, executada em fila e capaz de invalidar subscriptions rejeitadas sem interromper outros fluxos.
- Agendar e revalidar lembretes de relatório após o término de visitas para participantes elegíveis que ainda não tenham enviado o relatório obrigatório.
- Agendar lembretes de eventos para participantes inscritos 24 horas e 1 hora antes do início; cancelamentos, alteração de data/hora e remoção de inscrição devem impedir envios indevidos.
- Registrar cada tentativa elegível de forma suficiente para evitar duplicidade por atividade, usuário, dispositivo e tipo de lembrete, sem persistir payloads sensíveis.

## Capabilities

### New Capabilities

- `web-push-notifications`: Gerencia a permissão do navegador, subscriptions por dispositivo, recebimento de Push, privacidade do conteúdo e navegação segura pelo clique.
- `activity-push-reminders`: Agenda, revalida e envia lembretes deduplicados para relatórios de visita e para eventos inscritos.

### Modified Capabilities

- Nenhuma.

## Impact

- Backend Laravel: migrations e models de subscriptions e registros de envio, endpoints autenticados, serviço de entrega, jobs/filas e agendamento.
- Frontend React/Inertia: preferências de notificação, manifesto e service worker.
- Módulos de visitas, relatórios e eventos: criação/atualização/cancelamento e participação passam a acionar ou invalidar lembretes.
- Configuração de ambiente e operação: chaves VAPID, execução de worker de fila e scheduler pelo Supervisor.
- A dependência `minishlink/web-push` e uma base inicial de subscription/envio já estão presentes no diretório de trabalho; esta mudança definirá o comportamento completo e substituirá os caminhos parciais incompatíveis com ele.
