# web-push-notifications Specification

## Purpose

Permite que cada voluntário controle notificações Web Push por dispositivo, com entrega segura e navegação protegida para as telas internas do Esquadrão.

## Requirements

### Requirement: Ativação explícita e compatibilidade de Web Push
O sistema SHALL oferecer ao usuário autenticado uma ação explícita para ativar notificações Push. Antes de criar uma subscription, o sistema MUST solicitar a permissão do navegador e informar de forma amigável quando o navegador ou dispositivo não suportar o recurso. A ausência, recusa ou revogação da permissão MUST manter o restante do web app utilizável.

#### Scenario: Usuário ativa notificações em navegador compatível
- **WHEN** um usuário autenticado confirma a ativação e concede a permissão do navegador
- **THEN** o sistema registra uma subscription para o dispositivo atual e confirma o resultado ao usuário

#### Scenario: Navegador sem suporte a Push
- **WHEN** um usuário acessa as preferências em um navegador sem suporte a Web Push
- **THEN** o sistema explica que notificações Push não estão disponíveis naquele navegador sem tentar solicitar permissão

### Requirement: Gerenciamento de subscriptions por dispositivo
O sistema SHALL vincular cada subscription ao usuário autenticado e SHALL suportar mais de uma subscription para o mesmo usuário. O sistema MUST atualizar uma subscription já conhecida sem criar duplicidade e MUST permitir que o usuário desative as notificações do dispositivo atual, removendo ou invalidando sua subscription. O sistema MUST aceitar operações somente para a identidade autenticada, sem confiar em um identificador de usuário fornecido pelo cliente.

#### Scenario: Mesmo usuário registra dois dispositivos
- **WHEN** um usuário ativa notificações em dois dispositivos compatíveis distintos
- **THEN** o sistema mantém uma subscription ativa para cada dispositivo

#### Scenario: Usuário desativa o dispositivo atual
- **WHEN** um usuário autenticado desativa notificações nas preferências
- **THEN** o sistema deixa de usar a subscription do dispositivo atual em envios futuros

### Requirement: Entrega resiliente e privativa
O sistema SHALL entregar Push de forma assíncrona e falhas de entrega MUST NOT bloquear cadastro, atualização, cancelamento ou participação em atividades. Quando o provedor informar que uma subscription é inválida ou expirada, o sistema MUST invalidá-la para impedir novas tentativas. O payload e os logs MUST NOT conter resumo, feedback ou outros dados sensíveis de visitas; o conteúdo visível na tela bloqueada MUST ser limitado a um lembrete genérico ou a dados não sensíveis da atividade.

#### Scenario: Subscription inválida durante o envio
- **WHEN** o provedor rejeita uma subscription como expirada ou inválida
- **THEN** o sistema a invalida e conclui o processamento das demais subscriptions sem falhar o fluxo da atividade

#### Scenario: Lembrete de relatório exibido na tela bloqueada
- **WHEN** um dispositivo recebe um lembrete de relatório de visita
- **THEN** a notificação não exibe o conteúdo de relatórios, feedbacks ou observações da visita

### Requirement: Abertura segura pelo clique da notificação
O sistema SHALL exibir a notificação recebida pelo service worker e SHALL associar sua ação principal a uma rota interna da visita, do relatório ou do evento correspondente. Ao clicar, o sistema MUST abrir ou focalizar o web app na rota indicada; o acesso final MUST continuar sujeito à autenticação e à autorização normais da aplicação.

#### Scenario: Clique em lembrete de evento autenticado
- **WHEN** um usuário autenticado toca em um lembrete de evento
- **THEN** o web app abre os detalhes daquele evento

#### Scenario: Clique sem sessão válida
- **WHEN** um usuário sem sessão válida toca em um lembrete
- **THEN** o sistema exige autenticação antes de liberar qualquer conteúdo protegido
