## Purpose

Define lembretes Push revalidáveis para relatórios de visitas e eventos, assegurando destinatários elegíveis, horários atuais e uma entrega sem duplicidade por dispositivo.

## ADDED Requirements

### Requirement: Lembrete de relatório após visita
O sistema SHALL programar um lembrete após `fim_em` de cada visita e SHALL revalidar a visita e seus participantes no momento do envio. O lembrete MUST ser enviado somente a participantes ativos elegíveis para o relatório obrigatório que ainda não o tenham enviado. Visitas canceladas e usuários sem participação ativa MUST NOT receber o lembrete. Enquanto a regra de responsável não puder ser determinada além do papel existente, o sistema MAY enviar o lembrete a todos os participantes ativos e deve manter essa limitação explícita na documentação do MVP.

#### Scenario: Participante elegível sem relatório
- **WHEN** o horário `fim_em` de uma visita não cancelada é atingido e um participante elegível ainda não enviou o relatório obrigatório
- **THEN** o sistema agenda a entrega do lembrete de relatório para suas subscriptions ativas

#### Scenario: Relatório já enviado antes do disparo
- **WHEN** o lembrete de relatório é processado e o participante já enviou o relatório obrigatório
- **THEN** o sistema não envia a notificação para esse participante

#### Scenario: Visita cancelada antes do disparo
- **WHEN** uma visita é cancelada antes de seu lembrete ser processado
- **THEN** o sistema não envia lembretes de relatório daquela visita

### Requirement: Lembretes de eventos em 24 horas e 1 hora
O sistema SHALL programar lembretes para eventos agendados 24 horas e 1 hora antes de `data_inicio`. Os intervalos MUST ser centralizados em configuração. No momento de cada envio, o sistema MUST confirmar que o evento continua agendado, que o horário corresponde ao lembrete e que o destinatário mantém inscrição ativa. A regra SHALL abranger inicialmente `reuniao`, `oficina` e `evento` e permanecer extensível a outros tipos.

#### Scenario: Participante inscrito recebe os dois lembretes
- **WHEN** um participante permanece inscrito em um evento agendado
- **THEN** o sistema envia um lembrete 24 horas antes e outro 1 hora antes do início

#### Scenario: Participação cancelada antes do lembrete
- **WHEN** um participante cancela sua inscrição antes do horário de um lembrete
- **THEN** o sistema não envia aquele lembrete ao participante

#### Scenario: Evento cancelado antes do lembrete
- **WHEN** um evento é cancelado antes do horário de um lembrete
- **THEN** o sistema não envia lembretes daquele evento

### Requirement: Reagendamento conforme o estado atual da atividade
O sistema SHALL criar ou atualizar os lembretes quando uma visita ou evento relevante for criado ou alterado. Quando `fim_em`, `data_inicio` ou o status da atividade mudar, o sistema MUST invalidar os lembretes pendentes calculados com o estado anterior e considerar somente o estado atual no disparo.

#### Scenario: Data de evento alterada após agendamento
- **WHEN** a data ou horário de início de um evento agendado é alterado
- **THEN** lembretes pendentes no horário anterior não são enviados e os novos horários de 24 horas e 1 hora passam a ser considerados

### Requirement: Prevenção de entregas duplicadas
O sistema SHALL registrar a entrega de cada lembrete por atividade, usuário, dispositivo e tipo de lembrete. Para um mesmo conjunto desses identificadores, o sistema MUST NOT produzir mais de uma notificação bem-sucedida, inclusive quando jobs forem reexecutados ou concorrerem. O registro MUST conter somente identificadores técnicos e resultado de entrega necessários para essa garantia.

#### Scenario: Job reexecutado após envio bem-sucedido
- **WHEN** um job de lembrete já entregue com sucesso é executado novamente
- **THEN** o sistema não envia uma segunda notificação para a mesma subscription

#### Scenario: Falha transitória de entrega
- **WHEN** a entrega para uma subscription falha sem indicar invalidez permanente
- **THEN** o sistema registra o resultado sem bloquear outras entregas e aplica a política de nova tentativa sem duplicar envios já bem-sucedidos

