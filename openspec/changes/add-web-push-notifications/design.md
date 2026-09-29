## Context

Veja a motivação em [proposal.md](proposal.md). O projeto é Laravel 12 com React/Inertia, já possui `minishlink/web-push`, tabela de jobs e modelos para visitas, relatórios e eventos. Há uma implementação local parcial de subscription e aviso de evento de 1 hora; ela solicita permissão automaticamente, não possui service worker no diretório público e usa um marcador por evento, insuficiente para os dois intervalos e para múltiplos dispositivos.

Visitas mantêm participantes em `visita_participante`, com o papel `relator`; eventos mantêm inscrições na pivot `evento_participantes`. O serviço de prazo de relatório já centraliza as 48 horas de atraso, mas o lembrete deste change ocorre após `fim_em`.

## Goals / Non-Goals

**Goals:**

- Fazer o Push depender de opt-in explícito, com subscriptions por dispositivo e conteúdo privativo.
- Tornar lembretes duráveis, revalidáveis, deduplicados e desacoplados dos fluxos transacionais de visitas e eventos.
- Tratar alterações de agenda, cancelamentos, exclusões de inscrição e subscriptions inválidas de forma segura.

**Non-Goals:**

- Criar um segundo aviso perto do prazo de 48 horas do relatório.
- Permitir preferências separadas por categoria de notificação ou que o usuário configure intervalos.
- Notificar alterações e cancelamentos de atividades.
- Garantir Push em navegadores que não oferecem essa capacidade; as limitações do iOS/iPadOS serão documentadas, incluindo a instalação na tela inicial quando aplicável.

## Decisions

### Subscription controlada nas preferências e service worker público

Uma tela de preferências oferecerá ativação e desativação explícitas, verificará compatibilidade e usará o service worker e o manifest do app para receber Push e tratar `notificationclick`. A API autenticada associará o endpoint ao usuário da sessão; não receberá um `user_id` do cliente. O payload conterá somente título, mensagem segura, rótulo da ação e URL interna.

Alternativa considerada: solicitar permissão ao carregar `app.tsx`. Foi descartada porque surpreende o usuário, reduz a taxa de concessão e viola o opt-in explícito.

### Registros duráveis de lembretes e entregas

Serão usados registros persistidos de lembrete da atividade, com horário, tipo e estado, e registros de entrega por subscription. Uma restrição única no escopo da entrega protege contra concorrência e reexecução. Alterações relevantes invalidam lembretes pendentes e geram novos horários; o job sempre relê atividade, participação, relatório e subscription antes de entregar.

Alternativa considerada: colunas booleanas em `eventos` e `visitas`. Foi descartada porque não representa 24h + 1h, múltiplos dispositivos, falhas/retry nem reagendamento de forma auditável.

### Dispatcher periódico e jobs assíncronos por entrega

O scheduler identifica lembretes pendentes vencidos e os envia à fila após o commit. Jobs específicos para visita-relatório e evento chamam o serviço genérico de Web Push e tratam cada subscription isoladamente. O scheduler e o worker precisam ser processos persistentes supervisionados em produção.

Alternativa considerada: jobs atrasados diretamente na criação/edição. Foi descartada como mecanismo principal porque cancelar ou substituir jobs já enfileirados exige coordenação adicional; registros pendentes e revalidação tornam alterações de agenda previsíveis e recuperáveis.

### Elegibilidade consultada no disparo

Visitas canceladas, participantes inativos/cancelados e autores que já enviaram o relatório são excluídos no job. Eventos exigem `status = agendado` e pivot `status = inscrito`. O MVP usará todos os participantes ativos de visita enquanto a regra de responsável pelo relatório não for mais específica que o papel existente.

Alternativa considerada: calcular destinatários no agendamento. Foi descartada porque participação, relatório e status podem mudar antes da entrega.

## Risks / Trade-offs

- [O scheduler ou worker pode ficar indisponível] → registros pendentes permitem que a próxima execução recupere lembretes ainda elegíveis; monitorar filas e processos no Supervisor.
- [Uma notificação pode chegar atrasada após indisponibilidade] → o job revalida horário e define uma janela de validade para evitar lembretes obsoletos.
- [Provedores variam por navegador, especialmente iOS/iPadOS] → detectar capacidade, orientar instalação quando necessária e documentar a limitação.
- [Resposta ambígua do provedor após timeout] → a restrição de entrega impede duplicação concorrente; registrar resultado técnico mínimo e aplicar retry apenas conforme a política definida.
- [O diretório de trabalho contém Push parcial] → substituir a solicitação automática, o comando ad-hoc e os marcadores por evento pela estrutura deste design, preservando apenas elementos compatíveis.

## Migration Plan

1. Publicar migrations, configuração VAPID e assets PWA sem habilitar disparos até que as chaves e processos estejam configurados.
2. Executar migrations e configurar variáveis VAPID em cada ambiente.
3. Configurar e verificar scheduler e worker de fila no Supervisor.
4. Habilitar a preferência de Push e os lembretes; validar em desktop e Android antes da liberação geral.
5. Em rollback, interromper o dispatcher e workers de lembrete antes de reverter o código; manter tabelas de histórico até confirmar que não há jobs em trânsito.
