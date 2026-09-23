## 1. Base de dados e configuração de Push

- [x] 1.1 Consolidar a migration e o model de subscriptions para suportar endpoint único por dispositivo, vínculo seguro ao usuário e invalidação, verificando migrations em banco limpo.
- [x] 1.2 Criar migrations, models e constraints para lembretes pendentes e entregas por atividade, usuário, subscription e tipo, verificando unicidade com teste de concorrência/reexecução.
- [x] 1.3 Centralizar chaves VAPID, intervalos de 24h e 1h e janela de validade em configuração e variáveis de ambiente, verificando valores padrão em teste de configuração.

## 2. Preferências e PWA

- [x] 2.1 Criar endpoints autenticados para criar/atualizar e remover a subscription do dispositivo da sessão, verificando autorização e ausência de `user_id` controlado pelo cliente em testes feature.
- [x] 2.2 Adicionar preferências de notificação com ativação explícita, desativação, tratamento de permissão recusada e incompatibilidade, verificando o comportamento pelo navegador e pela tipagem do frontend.
- [x] 2.3 Configurar manifest e service worker para receber Push, mostrar conteúdo seguro e abrir/focalizar a URL interna no clique, verificando o fluxo em navegador desktop compatível.

## 3. Entrega assíncrona de Web Push

- [x] 3.1 Implementar o serviço genérico de Web Push e jobs de entrega isolados por subscription, verificando que uma falha não interrompe as demais entregas nem fluxos de atividade.
- [x] 3.2 Tratar respostas permanentes de endpoint inválido ou expirado, invalidando a subscription e verificando o comportamento com teste do provedor simulado.
- [x] 3.3 Remover ou adaptar a solicitação automática, o comando ad-hoc e o marcador por evento existentes para a nova estrutura, verificando que não restam caminhos de envio concorrentes.

## 4. Agendamento e revalidação dos lembretes

- [x] 4.1 Criar o serviço de agendamento que persiste lembretes de relatório após `fim_em` e de evento em 24h e 1h, verificando os horários calculados em testes unitários.
- [x] 4.2 Integrar o agendamento e a invalidação aos fluxos de criação, edição, cancelamento e participação de visitas e eventos, verificando que mudanças de data/hora e cancelamentos não deixam lembretes anteriores ativos.
- [x] 4.3 Implementar o dispatcher periódico e os jobs de lembrete para buscar pendências e revalidar atividade, participação, relatório e subscription antes de cada envio, verificando a execução agendada e em fila.
- [x] 4.4 Implementar a elegibilidade de relatório de visita usando participantes ativos e ausência de relatório obrigatório, documentando a limitação temporária de destinatários do MVP e verificando visita cancelada, participação cancelada e relatório já enviado.
- [x] 4.5 Implementar a elegibilidade de eventos `reuniao`, `oficina` e `evento` com inscrição ativa, verificando os dois intervalos, evento cancelado, inscrição removida e evento reagendado.

## 5. Segurança, operação e validação final

- [x] 5.1 Garantir que payloads e logs usem somente dados não sensíveis e que as rotas abertas pelo clique mantenham autenticação e autorização, verificando cenários de acesso sem sessão e sem permissão.
- [x] 5.2 Configurar e documentar os processos de scheduler e worker no Supervisor, as variáveis VAPID e as limitações de desktop, Android e iOS/iPadOS, verificando os comandos de operação no ambiente de deploy.
- [ ] 5.3 Executar os testes de Push, visitas, relatórios e eventos e validar manualmente ativação, recebimento e clique em desktop e Android compatíveis.
- [ ] 5.4 Atualizar as specs permanentes em `docs/features/visitas`, `docs/features/visitas/relatorios` e no contexto de eventos após a implementação validada, verificando que refletem o comportamento entregue.
