# Feature Specification: Sistema de Eventos

**Feature Branch**: `001-eventos`

**Created**: 2026-06-02

**Status**: Draft

**Input**: User description: "Sistema completo de gerenciamento de eventos (oficinas e reuniões) com cadastro, listagem, inscrição, presença e relatórios"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Criar cadastro de eventos (Priority: P1)

Como diretor ou responsável autorizado, quero cadastrar oficinas e reuniões para organizar o cronograma de atividades da ONG.

**Why this priority**: Funcionalidade central e bloqueadora. Sem eventos criados, nenhuma outra funcionalidade de eventos faz sentido.

**Independent Test**: Um responsável autorizado pode criar uma oficina com todos os dados obrigatórios e visualizá-la na listagem com status AGENDADO.

**Acceptance Scenarios**:

1. **Given** usuário autenticado como responsável, 
**When** acessa rota criar evento, 
**Then** formulário é exibido com campos: título, descrição, data/hora início, data/hora fim, local, cidade, limite de vagas (opcional), se o feedback vai ser habilitado, evento origem em caso de transferencia de outro evento,. O atríbutos tipo sempre vai ser agendado, e o atríbuto resposável vai ser decidido de acordo com a conta que está realizando a criação do evento. 
2. **Given** usuário preenche formulário com dados válidos, 
**When** clica em "Salvar", 
**Then** evento é criado com status AGENDADO e redireciona para listagem
3. **Given** usuário não autorizado tenta acessar formulário de criação, **When** submete requisição, **Then** recebe erro de autorização
4. **GIven** usuário informar data/hora fim menor ou igual data/hora inicio, 
**When** tenta salvar o evento, 
**Then** Sistema exibe uma mensagem de erro informando que a data/hora fim deve ser posterior á data, 
6. **Given** usuário seleciona uma data/hora início,
  **When** tenta selecionar uma data/hora fim anterior à data/hora início,
   **Then** o campo data/hora fim deve impedir a seleção de valores inválidos
7. **Given** Usuário deixa em branco o limíte de vagas,
---

### User Story 2 - Listar eventos em calendário/listagem (Priority: P1)

Como voluntário, quero visualizar oficinas e reuniões disponíveis para saber quais atividades estão previstas e poder me inscrever.

**Why this priority**: Complementa a criação - sem listagem, eventos não são acessíveis aos voluntários. Essencial para o MVP.

**Independent Test**: Voluntário acessa página de eventos e visualiza lista com eventos agendados, filtrados por cidade, com informações de tipo, data, horário, local, inscritos e limite de vagas.

**Acceptance Scenarios**:

1. **Given** eventos já existem no sistema, **When** voluntário acessa página Eventos, **Then** lista é exibida mostrando: título, tipo, data, horário, local, quantidade de inscritos, limite de vagas (se houver)
2. **Given** voluntário está na listagem, **When** aplica filtro por tipo OFICINA, **Then** apenas oficinas são exibidas
3. **Given** voluntário está na listagem, **When** aplica filtro por cidade, **Then** apenas eventos daquela cidade são exibidos
4. **Given** eventos cancelados existem, **When** voluntário visualiza listagem, **Then** eventos cancelados aparecem de forma diferenciada ou podem ser filtrados

---

### User Story 3 - Visualizar detalhes de evento (Priority: P2)

Como voluntário, quero abrir os detalhes de uma oficina ou reunião para entender o conteúdo, local, horário e responsáveis.

**Why this priority**: Complementa listagem. Usuário precisa de contexto completo antes de se inscrever. P2 porque pode ser substituído por informações inline na listagem no MVP.

**Independent Test**: Voluntário clica em um evento na listagem, vê detalhes completos incluindo responsáveis e inscritos, com opção de inscrição se evento estiver agendado.

**Acceptance Scenarios**:

1. **Given** evento está na listagem, **When** voluntário clica no evento, **Then** página de detalhes é exibida com: título, tipo, descrição, data/horário, local, cidade, responsáveis, inscritos, status
2. **Given** evento tem status AGENDADO, **When** voluntário visualiza detalhes, **Then** botão "Inscrever-se" é exibido
3. **Given** voluntário já está inscrito, **When** visualiza detalhes, **Then** botão "Cancelar inscrição" é exibido
4. **Given** evento está cancelado ou finalizado, **When** voluntário visualiza detalhes, **Then** botão de inscrição não é exibido

---

### User Story 4 - Inscrever-se em evento (Priority: P2)

Como voluntário, quero me inscrever em uma oficina ou reunião para indicar que pretendo participar.

**Why this priority**: Funcionalidade de engajamento. Necessária para rastrear participação, mas pode vir após MVP básico de criar/listar.

**Independent Test**: Voluntário se inscreve com sucesso em evento agendado, não consegue se inscrever duas vezes, e inscrição é criada com status INSCRITO.

**Acceptance Scenarios**:

1. **Given** evento agendado existe e voluntário não está inscrito, **When** clica em "Inscrever-se", **Then** inscrição é criada com status INSCRITO
2. **Given** voluntário já está inscrito, **When** tenta se inscrever novamente, **Then** recebe mensagem de erro "Você já está inscrito neste evento"
3. **Given** evento tem limite de vagas e limite foi atingido, **When** voluntário tenta se inscrever, **Then** recebe mensagem "Limite de vagas atingido"
4. **Given** evento está cancelado ou finalizado, **When** voluntário tenta se inscrever, **Then** recebe mensagem "Não é possível se inscrever neste evento"

---

### User Story 5 - Cancelar inscrição em evento (Priority: P3)

Como voluntário, quero cancelar minha inscrição em um evento para avisar que não vou conseguir participar.

**Why this priority**: Suporta fluxo de inscrição, mas menos crítico que o próprio ato de se inscrever. Pode ser implementado depois.

**Independent Test**: Voluntário inscrito consegue cancelar sua inscrição antes do evento finalizar, vaga é liberada se houver limite, e inscrição muda para status CANCELADO.

**Acceptance Scenarios**:

1. **Given** voluntário está inscrito e evento não foi finalizado, **When** clica em "Cancelar inscrição", **Then** inscrição muda para status CANCELADO
2. **Given** voluntário estava inscrito, **When** cancela inscrição, **Then** vaga é liberada para outro voluntário (se houver limite)
3. **Given** evento foi finalizado, **When** voluntário tenta cancelar inscrição, **Then** recebe mensagem "Não é possível cancelar após evento finalizado"

---

### User Story 6 - Confirmar presença de participantes (Priority: P3)

Como responsável pelo evento, quero confirmar quem participou da oficina ou reunião para registrar presenças reais no sistema.

**Why this priority**: Suporta funcionalidade de acompanhamento, mas é atividade pós-evento. Importante para indicadores, mas não é MVP.

**Independent Test**: Responsável acessa tela de presença, lista inscritos, marca como presente/ausente com data/hora, e apenas presenças confirmadas contam nos indicadores.

**Acceptance Scenarios**:

1. **Given** responsável do evento acessa tela de presença, **When** página carrega, **Then** lista de inscritos é exibida com opção de marcar como presente/ausente
2. **Given** responsável marca participante como presente, **When** salva, **Then** sistema registra quem confirmou e data/hora da confirmação
3. **Given** evento foi finalizado, **When** responsável tenta alterar presença sem permissão especial, **Then** recebe erro de autorização

---

### User Story 7 - Finalizar evento (Priority: P3)

Como responsável pelo evento, quero finalizar uma oficina ou reunião para encerrar a lista de presença e alimentar os indicadores.

**Why this priority**: Transição de estado importante, mas é ação manual e pós-evento. Não bloqueia MVP de criar/listar/se inscrever.

**Independent Test**: Responsável autorizado consegue finalizar evento, status muda para FINALIZADO, e novas inscrições são bloqueadas.

**Acceptance Scenarios**:

1. **Given** evento está com status AGENDADO e responsável autorizado acessa, **When** clica em "Finalizar evento", **Then** status muda para FINALIZADO
2. **Given** evento está finalizado, **When** voluntário tenta se inscrever, **Then** recebe mensagem "Evento já foi finalizado"
3. **Given** evento está finalizado, **When** inscritos não confirmados existem, **Then** responsável pode marcar como AUSENTE (com permissão especial)

---

### User Story 8 - Cancelar ou transferir evento (Priority: P3)

Como diretor ou responsável autorizado, quero cancelar ou transferir uma oficina/reunião para manter o cronograma atualizado quando houver mudança de planos.

**Why this priority**: Gerenciamento de mudanças. Importante em longo prazo, mas não essencial para MVP - pode ser adicionado depois.

**Independent Test**: Responsável consegue marcar evento como CANCELADO ou TRANSFERIDO, registra motivo, e no caso de transferência, um novo evento vinculado pode ser criado.

**Acceptance Scenarios**:

1. **Given** evento agendado existe, **When** responsável marca como CANCELADO, **Then** status muda e motivo é registrado
2. **Given** evento agendado existe, **When** responsável marca como TRANSFERIDO, **Then** status muda e novo evento vinculado pode ser criado
3. **Given** evento foi cancelado, **When** voluntário visualiza, **Then** vê que evento foi cancelado e consegue visualizar motivo (se houver)

---

### User Story 9 - Dashboard de presença semestral (Priority: P3)

Como diretoria, quero visualizar a participação dos integrantes em oficinas e reuniões no semestre para acompanhar engajamento e identificar quem precisa de atenção.

**Why this priority**: Funcionalidade de acompanhamento/indicadores. Importante para gestão, mas não necessária para MVP funcional. P3.

**Independent Test**: Diretoria acessa dashboard, filtra por semestre, visualiza voluntários com contagem de presenças separadas por tipo de evento, com indicadores visuais de status.

**Acceptance Scenarios**:

1. **Given** diretoria acessa dashboard de eventos, **When** seleciona semestre, **Then** lista voluntários com contagem de oficinas e reuniões que compareceram
2. **Given** dashboard está sendo exibido, **When** visualiza tabela, **Then** mostra status: "dentro do esperado", "atenção", "abaixo do esperado"
3. **Given** voluntário tem presenças registradas, **When** comparativo é feito, **Then** apenas status PRESENTE são contabilizados

---

### Edge Cases

- O que acontece quando um evento é criado mas tem data de fim igual à data de início (mesmo horário)?
- Como o sistema lida com múltiplos responsáveis para o mesmo evento?
- Se evento_origem_id aponta para um evento que é transferência de outro, como tratar a cadeia?
- Como funciona limite de vagas quando inscrição é cancelada? Vaga é liberada imediatamente?
- Se um voluntário é deletado, o que acontece com suas inscrições?
- Como tratar eventos com datas no passado (já finalizados) quando são consultados?

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Sistema DEVE permitir criar evento com tipo OFICINA ou REUNIAO
- **FR-002**: Sistema DEVE permitir definir titulo, descrição, data/hora início, data/hora fim, local, cidade, limite de vagas (opcional), e responsáveis na criação de maneira obrigatório com excessão do límite de vagas.
- **FR-003**: Evento criado DEVE nascer com status AGENDADO
- **FR-004**: Todos os usuários podem criar uma oficina ou reunião
- **FR-005**: Sistema DEVE listar eventos com filtros por tipo, cidade e semestre
- **FR-006**: Sistema DEVE exibir em cada evento da listagem: título, tipo, data, horário, local, quantidade de inscritos, limite de vagas
- **FR-007**: Sistema DEVE permitir voluntário ver detalhes completos do evento (título, tipo, descrição, data, horário, local, cidade, responsáveis, inscritos, status)
- **FR-008**: Sistema DEVE permitir inscrição de voluntário em evento agendado
- **FR-009**: Sistema DEVE impedir duplicidade de inscrição - voluntário não pode se inscrever duas vezes no mesmo evento
- **FR-010**: Sistema DEVE validar limite de vagas - se atingido, bloqueia nova inscrição
- **FR-011**: Sistema DEVE impedir inscrição em eventos cancelados ou finalizados
- **FR-012**: Inscrição DEVE nascer com status INSCRITO
- **FR-013**: Sistema DEVE permitir cancelamento de inscrição antes de evento ser finalizado
- **FR-014**: Ao cancelar inscrição, a vaga DEVE ser liberada se houver limite
- **FR-015**: Sistema DEVE permitir responsável confirmar presenças de participantes
- **FR-016**: Presença confirmada DEVE registrar quem confirmou e data/hora da confirmação
- **FR-017**: Apenas presenças confirmadas DEVEM contar nos indicadores
- **FR-018**: Sistema DEVE permitir responsável finalizar evento, mudando status para FINALIZADO
- **FR-019**: Após evento finalizado, DEVE bloquear novas inscrições
- **FR-020**: Sistema DEVE permitir marcar evento como CANCELADO ou TRANSFERIDO
- **FR-021**: Evento transferido DEVE poder estar vinculado a novo evento via evento_origem_id
- **FR-022**: Sistema DEVE exibir dashboard com participação de voluntários por semestre
- **FR-023**: Dashboard DEVE separar contagem por tipo de evento (OFICINA/REUNIAO)
- **FR-024**: Dashboard DEVE mostrar indicador de status: "dentro do esperado", "atenção", "abaixo do esperado"
- **FR-025**: Sistema DEVE usar soft delete (Deleted_at) para eventos
- **FR-026**: Sistema DEVE registrar criado_por_id - responsável que criou o evento

### Key Entities

- **Evento**: Representa oficina ou reunião. Atributos: tipo, titulo, descricao, data_inicio, data_fim, local, cidade_id (FK), semestre, status, limite_vagas (nullable), feedback_habilitado, evento_origem_id (FK para self), criado_por_id (FK users), created_at, updated_at, deleted_at
- **EventoParticipante**: Relacionamento entre usuário e evento. Atributos: evento_id (FK), user_id (FK), status (INSCRITO/PRESENTE/AUSENTE/CANCELADO), presenca_confirmada_por_id (FK users, nullable), presenca_confirmada_em (timestamp, nullable), created_at, updated_at
- **Cidade**: Já existe no sistema. FK de Evento.
- **User**: Já existe no sistema. Responsável e participantes.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Responsáveis conseguem criar novo evento em menos de 2 minutos
- **SC-002**: Voluntários conseguem localizar evento específico por filtros em menos de 30 segundos
- **SC-003**: Taxa de sucesso na inscrição em evento (sem erros) acima de 95%
- **SC-004**: Limite de vagas é respeitado - nunca mais inscritos que limite
- **SC-005**: Relatório de presença por semestre fica disponível em menos de 3 segundos
- **SC-006**: 100% dos eventos criados têm status AGENDADO inicialmente
- **SC-007**: Apenas usuários com cargo autorizado conseguem criar/finalizar eventos
- **SC-008**: Eventos cancelados não permitem novas inscrições após marcação
- **SC-009**: Feedback_habilitado pode ser toggle verdadeiro/falso sem erros
- **SC-010**: Evento_origem_id permite rastrear transferências sem ciclos

## Assumptions

- Autenticação e sistema de usuários já estão implementados (Laravel Fortify)
- Sistema de cargos/autorização já existe e será usado para controlar permissões
- Cidades já existem no banco de dados
- Semestres são strings (ex.: "2026-1", "2026-2")
- Limite de vagas é opcional; se não informado, evento tem vagas ilimitadas
- Feedback_habilitado é campo simples boolean; não há implementação de feedback nesta feature
- Soft delete será usado via SoftDeletes trait
- Responsável por evento (criado_por_id) é sempre um User
- Presença confirmada é opcional; inscritos podem permanecer INSCRITO sem confirmação até evento finalizar
- Email de notificação NÃO faz parte desta feature
- Icalendar/exportação de calendário NÃO faz parte desta feature (pode ser evolução)
