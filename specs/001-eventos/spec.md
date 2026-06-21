# Feature Specification: Sistema de Eventos

<<<<<<< HEAD
**Feature Branch**: `001-eventos`

**Created**: 2026-06-02

**Status**: Draft

**Input**: User description: "Sistema completo de gerenciamento de eventos (oficinas e reuniões) com cadastro, listagem, inscrição, presença e relatórios"
=======
**Feature Branch**: `002-eventos-aprimoramentos`

**Created**: 2026-06-02
**Updated**: 2026-06-10

**Status**: Draft

**Input**: Melhorias no módulo de eventos — remoção de evento_origem, tabela de responsáveis, controle de permissão de edição, ícone de detalhes, inscrição com regras de negócio, geolocalização, mensagens de feedback, filtros/ordenação, finalização/cancelamento por responsáveis, dashboard de participação.

---
>>>>>>> 002-eventos-aprimoramentos

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Criar cadastro de eventos (Priority: P1)

<<<<<<< HEAD
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
=======
Como voluntário autenticado, quero cadastrar oficinas e reuniões para organizar o cronograma de atividades da ONG.

**Why this priority**: Funcionalidade central e bloqueadora. Sem eventos criados, nenhuma outra funcionalidade de eventos faz sentido.

**Independent Test**: Um voluntário autenticado pode criar uma oficina com todos os dados obrigatórios e visualizá-la na listagem com status AGENDADO. O sistema exibe mensagem de sucesso após a criação.

**Acceptance Scenarios**:

1. **Given** usuário autenticado acessa rota criar evento,
   **When** formulário é carregado,
   **Then** exibe campos: título, tipo (OFICINA/REUNIAO), descrição, data/hora início, data/hora fim, localização geográfica, cidade, limite de vagas (opcional), feedback habilitado. O status nasce como AGENDADO e o criador é registrado automaticamente pela conta autenticada.
2. **Given** usuário preenche formulário com dados válidos,
   **When** clica em "Salvar",
   **Then** evento é criado com status AGENDADO, uma mensagem de sucesso é exibida e usuário é redirecionado para a listagem.
3. **Given** já existe um evento AGENDADO com o mesmo título,
   **When** usuário tenta salvar novo evento com título idêntico,
   **Then** sistema exibe mensagem de erro "Já existe um evento agendado com este nome".
4. **Given** usuário informa data/hora fim menor ou igual à data/hora início,
   **When** tenta salvar o evento,
   **Then** sistema exibe mensagem de erro "A data/hora de término deve ser posterior ao início".
5. **Given** usuário deixa o limite de vagas em branco,
   **When** salva o evento,
   **Then** evento é criado sem limite, aceitando inscrições ilimitadas.

---

### User Story 2 - Listar e filtrar eventos (Priority: P1)

Como voluntário, quero visualizar oficinas e reuniões disponíveis com opções de ordenação e filtragem para encontrar eventos relevantes rapidamente.

**Why this priority**: Sem listagem acessível, eventos não chegam aos voluntários. Filtros aumentam a utilidade diretamente.

**Independent Test**: Voluntário acessa página de eventos e consegue filtrar por tipo, data e ordenar por hora de início ou fim.

**Acceptance Scenarios**:

1. **Given** eventos existem no sistema,
   **When** voluntário acessa página Eventos,
   **Then** lista exibe: título, tipo, data, horário de início, horário de término, local, quantidade de inscritos, limite de vagas (se houver), ícone de visualizar detalhes, ícone de inscrição (quando aplicável).
2. **Given** voluntário já está inscrito em um evento,
   **When** visualiza a listagem geral,
   **Then** ícone de inscrição NÃO é exibido para aquele evento.
3. **Given** voluntário está na listagem,
   **When** aplica filtro por tipo (OFICINA ou REUNIAO),
   **Then** apenas eventos do tipo selecionado são exibidos.
4. **Given** voluntário está na listagem,
   **When** aplica filtro por data ou ordena por hora de início / hora de término,
   **Then** eventos são reordenados conforme critério selecionado.
5. **Given** voluntário é criador ou responsável por um evento,
   **When** visualiza a listagem,
   **Then** ícone de editar aparece habilitado para esses eventos e desabilitado ou oculto para os demais.

---

### User Story 3 - Visualizar detalhes de evento (Priority: P1)

Como voluntário, quero abrir os detalhes completos de um evento para entender conteúdo, local, horário e responsáveis.

**Why this priority**: Necessário para decisão de inscrição. O ícone de olho na listagem direciona para esta tela.

**Independent Test**: Voluntário clica no ícone de olho de qualquer evento e visualiza todos os detalhes, incluindo localização com opção de abrir no mapa.

**Acceptance Scenarios**:

1. **Given** evento está na listagem,
   **When** voluntário clica no ícone de olho,
   **Then** página de detalhes exibe: título, tipo, descrição, data/horário início e fim, localização com link para abrir no Google Maps ou Waze, cidade, lista de responsáveis, quantidade de inscritos, status.
2. **Given** evento tem localização geográfica registrada,
   **When** voluntário visualiza detalhes,
   **Then** botão "Abrir no mapa" está disponível, ao clicar abre o local no aplicativo de mapas (Google Maps ou Waze).
3. **Given** evento tem status AGENDADO e voluntário não está inscrito,
   **When** visualiza detalhes,
   **Then** botão "Inscrever-se" é exibido.
4. **Given** voluntário já está inscrito,
   **When** visualiza detalhes,
   **Then** botão "Inscrever-se" NÃO é exibido; botão "Cancelar inscrição" é exibido em seu lugar.
5. **Given** evento está cancelado ou finalizado,
   **When** voluntário visualiza detalhes,
   **Then** nenhum botão de ação de inscrição é exibido.
>>>>>>> 002-eventos-aprimoramentos

---

### User Story 4 - Inscrever-se em evento (Priority: P2)

<<<<<<< HEAD
Como voluntário, quero me inscrever em uma oficina ou reunião para indicar que pretendo participar.

**Why this priority**: Funcionalidade de engajamento. Necessária para rastrear participação, mas pode vir após MVP básico de criar/listar.

**Independent Test**: Voluntário se inscreve com sucesso em evento agendado, não consegue se inscrever duas vezes, e inscrição é criada com status INSCRITO.

**Acceptance Scenarios**:

1. **Given** evento agendado existe e voluntário não está inscrito, **When** clica em "Inscrever-se", **Then** inscrição é criada com status INSCRITO
2. **Given** voluntário já está inscrito, **When** tenta se inscrever novamente, **Then** recebe mensagem de erro "Você já está inscrito neste evento"
3. **Given** evento tem limite de vagas e limite foi atingido, **When** voluntário tenta se inscrever, **Then** recebe mensagem "Limite de vagas atingido"
4. **Given** evento está cancelado ou finalizado, **When** voluntário tenta se inscrever, **Then** recebe mensagem "Não é possível se inscrever neste evento"
=======
Como voluntário, quero me inscrever em uma oficina ou reunião para indicar participação.

**Why this priority**: Funcionalidade de engajamento e base para o dashboard de participação.

**Independent Test**: Voluntário se inscreve com sucesso em evento agendado com vagas, recebe mensagem de confirmação, e não consegue se inscrever duas vezes nem em evento cheio ou expirado.

**Acceptance Scenarios**:

1. **Given** evento agendado existe, está dentro do prazo e tem vagas disponíveis, e voluntário não está inscrito,
   **When** clica em "Inscrever-se",
   **Then** inscrição é criada com status INSCRITO e mensagem de sucesso é exibida.
2. **Given** voluntário já está inscrito,
   **When** tenta se inscrever novamente,
   **Then** recebe mensagem "Você já está inscrito neste evento".
3. **Given** evento atingiu o limite de vagas,
   **When** voluntário tenta se inscrever,
   **Then** recebe mensagem "Limite de vagas atingido para este evento".
4. **Given** data/hora de início do evento já passou,
   **When** voluntário tenta se inscrever,
   **Then** recebe mensagem "Não é possível se inscrever após o início do evento".
5. **Given** evento está cancelado ou finalizado,
   **When** voluntário tenta se inscrever,
   **Then** recebe mensagem "Não é possível se inscrever neste evento".
>>>>>>> 002-eventos-aprimoramentos

---

### User Story 5 - Cancelar inscrição em evento (Priority: P3)

<<<<<<< HEAD
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
=======
Como voluntário, quero cancelar minha inscrição em um evento para liberar a vaga e avisar que não participarei.

**Why this priority**: Suporta fluxo de inscrição; menos crítico que o próprio ato de se inscrever.

**Independent Test**: Voluntário inscrito cancela inscrição antes do início do evento, vaga é liberada e inscrição muda para CANCELADO.

**Acceptance Scenarios**:

1. **Given** voluntário está inscrito e evento não foi iniciado,
   **When** clica em "Cancelar inscrição",
   **Then** inscrição muda para status CANCELADO e vaga é liberada (se houver limite).
2. **Given** evento já foi finalizado,
   **When** voluntário tenta cancelar inscrição,
   **Then** recebe mensagem "Não é possível cancelar após evento finalizado".

---

### User Story 6 - Editar evento (Priority: P2)

Como criador ou responsável pelo evento, quero editar os dados de uma oficina ou reunião para corrigir informações.

**Why this priority**: Necessário para manter dados corretos; deve ser restrito ao criador e responsáveis.

**Independent Test**: Somente o criador ou quem está na lista de responsáveis consegue acessar e salvar alterações no evento.

**Acceptance Scenarios**:

1. **Given** usuário é o criador do evento,
   **When** acessa a opção de editar,
   **Then** formulário de edição é exibido com dados atuais preenchidos.
2. **Given** usuário está na lista de responsáveis do evento,
   **When** acessa a opção de editar,
   **Then** formulário de edição é exibido com dados atuais preenchidos.
3. **Given** usuário não é criador nem responsável,
   **When** tenta acessar edição,
   **Then** ação é bloqueada e usuário recebe mensagem de erro de autorização.
4. **Given** dados editados são válidos,
   **When** responsável salva,
   **Then** evento é atualizado com sucesso.

---

### User Story 7 - Finalizar evento e registrar presença (Priority: P3)

Como criador ou responsável pelo evento, quero finalizar o evento selecionando quem participou e quem não participou.

**Why this priority**: Transição de estado necessária para alimentar o dashboard de participação.

**Independent Test**: Responsável acessa opção de finalizar, seleciona presentes e ausentes, confirma e status muda para FINALIZADO.

**Acceptance Scenarios**:

1. **Given** evento está AGENDADO e usuário é criador ou responsável,
   **When** clica no ícone de finalizar,
   **Then** tela exibe lista de inscritos com opção de marcar cada um como PRESENTE ou AUSENTE.
2. **Given** responsável marcou presenças,
   **When** confirma finalização,
   **Then** status do evento muda para FINALIZADO e presenças são registradas.
3. **Given** evento está finalizado,
   **When** voluntário tenta se inscrever,
   **Then** recebe mensagem "Evento já foi finalizado".
4. **Given** usuário não é criador nem responsável,
   **When** tenta finalizar evento,
   **Then** ação é bloqueada.

---

### User Story 8 - Cancelar evento (Priority: P3)

Como criador ou responsável pelo evento, quero cancelar um evento para avisar que não ocorrerá.

**Why this priority**: Gerenciamento de mudanças; importante para manter voluntários informados.

**Independent Test**: Responsável cancela evento, status muda para CANCELADO, e inscrições ficam bloqueadas.

**Acceptance Scenarios**:

1. **Given** evento está AGENDADO e usuário é criador ou responsável,
   **When** clica no ícone de cancelar,
   **Then** sistema solicita confirmação e ao confirmar, status muda para CANCELADO.
2. **Given** evento foi cancelado,
   **When** voluntário tenta se inscrever,
   **Then** recebe mensagem "Este evento foi cancelado".
3. **Given** usuário não é criador nem responsável,
   **When** tenta cancelar,
   **Then** ação é bloqueada.

---

### User Story 9 - Meus eventos como responsável (Priority: P2)

Como voluntário, quero ver uma listagem separada com os eventos em que sou criador ou responsável para gerenciá-los facilmente sem precisar filtrar na lista geral.

**Why this priority**: Responsáveis precisam de acesso rápido às ações de editar, finalizar e cancelar. Uma visão dedicada elimina ruído da listagem geral.

**Independent Test**: Voluntário acessa a aba "Meus eventos como responsável" e vê apenas os eventos em que é criador ou está na tabela evento_responsaveis.

**Acceptance Scenarios**:

1. **Given** voluntário acessa a listagem de responsabilidades,
   **When** página carrega,
   **Then** exibe somente eventos em que o usuário é criador (criado_por_id) ou está em evento_responsaveis.
2. **Given** listagem de responsabilidades está visível,
   **When** voluntário visualiza cada evento,
   **Then** ícones de editar, finalizar e cancelar estão disponíveis para todos os eventos listados.
3. **Given** usuário não é criador nem responsável de nenhum evento,
   **When** acessa a listagem,
   **Then** tela exibe mensagem "Você não é responsável por nenhum evento".

---

### User Story 10 - Meus eventos inscritos (Priority: P2)

Como voluntário, quero ver uma listagem separada com os eventos em que estou inscrito e que ainda estão agendados para acompanhar minha agenda de participação.

**Why this priority**: Voluntário precisa saber rapidamente em quais eventos futuros confirmou presença sem precisar pesquisar na lista geral.

**Independent Test**: Voluntário acessa a aba "Meus eventos inscritos" e vê apenas eventos com status AGENDADO nos quais sua inscrição está ativa (status INSCRITO).

**Acceptance Scenarios**:

1. **Given** voluntário possui inscrições ativas em eventos AGENDADOS,
   **When** acessa a listagem de eventos inscritos,
   **Then** vê apenas eventos com status AGENDADO em que sua inscrição tem status INSCRITO.
2. **Given** evento em que o voluntário estava inscrito foi CANCELADO ou FINALIZADO,
   **When** voluntário acessa a listagem,
   **Then** esse evento NÃO aparece na listagem.
3. **Given** voluntário cancelou sua inscrição em um evento,
   **When** acessa a listagem,
   **Then** esse evento NÃO aparece na listagem.
4. **Given** voluntário não possui inscrições ativas em eventos agendados,
   **When** acessa a listagem,
   **Then** tela exibe mensagem "Você não está inscrito em nenhum evento agendado".
5. **Given** evento exibido na listagem,
   **When** voluntário visualiza,
   **Then** ícone de "cancelar inscrição" está disponível; ícone de "inscrever-se" NÃO é exibido.

---

### User Story 11 - Dashboard de participação por voluntário (Priority: P3)

Como voluntário ou diretoria, quero visualizar na aba de eventos um dashboard com a pontuação de participação de cada voluntário, filtrando por nome e semestre.

**Why this priority**: Ferramenta de acompanhamento de engajamento; não bloqueia o MVP funcional.

**Independent Test**: Usuário acessa dashboard, filtra por semestre "2026-1", visualiza lista de voluntários com contagem de eventos que participaram.

**Acceptance Scenarios**:

1. **Given** usuário acessa aba de eventos,
   **When** navega para o dashboard de participação,
   **Then** lista de voluntários é exibida com: nome, pontuação total de eventos participados.
2. **Given** dashboard está sendo exibido,
   **When** usuário aplica filtro por semestre,
   **Then** lista é atualizada mostrando participação somente no período selecionado.
3. **Given** dashboard está sendo exibido,
   **When** usuário filtra por nome,
   **Then** apenas voluntários cujo nome contém o texto digitado são exibidos.
4. **Given** voluntário tem presenças registradas,
   **When** pontuação é calculada,
   **Then** apenas status PRESENTE são contabilizados.
>>>>>>> 002-eventos-aprimoramentos

---

### Edge Cases

<<<<<<< HEAD
- O que acontece quando um evento é criado mas tem data de fim igual à data de início (mesmo horário)?
- Como o sistema lida com múltiplos responsáveis para o mesmo evento?
- Se evento_origem_id aponta para um evento que é transferência de outro, como tratar a cadeia?
- Como funciona limite de vagas quando inscrição é cancelada? Vaga é liberada imediatamente?
- Se um voluntário é deletado, o que acontece com suas inscrições?
- Como tratar eventos com datas no passado (já finalizados) quando são consultados?
=======
- Voluntário cancela inscrição e outro tenta se inscrever ao mesmo tempo: a vaga deve ser liberada atomicamente.
- Dois responsáveis tentam finalizar o mesmo evento simultaneamente.
- Voluntário deletado/desativado: suas presenças históricas permanecem no dashboard.
- Evento sem inscritos ao ser finalizado: responsável pode finalizar sem selecionar presenças.
- Filtro de nome no dashboard: deve ser case-insensitive e ignorar acentos.

---
>>>>>>> 002-eventos-aprimoramentos

## Requirements *(mandatory)*

### Functional Requirements

<<<<<<< HEAD
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
=======
**Criação e Edição:**
- **FR-001**: Sistema DEVE permitir criar evento com tipo OFICINA ou REUNIAO
- **FR-002**: Campos obrigatórios na criação: título, tipo, descrição, data/hora início, data/hora fim, localização geográfica, cidade
- **FR-003**: Evento criado DEVE nascer com status AGENDADO e criado_por_id registrado automaticamente
- **FR-004**: Sistema DEVE impedir criação de dois eventos com o mesmo título quando ambos estão com status AGENDADO
- **FR-005**: Apenas o criador ou quem está na tabela evento_responsaveis DEVE poder editar o evento
- **FR-006**: Sistema DEVE exibir mensagem de sucesso após criação bem-sucedida de evento

**Listagem e Filtros:**
- **FR-007**: Sistema DEVE listar eventos com filtros por: tipo, data
- **FR-008**: Sistema DEVE permitir ordenação por: data, hora de início, hora de término
- **FR-009**: Sistema DEVE exibir em cada item da listagem: título, tipo, data, horário, local, quantidade de inscritos, limite de vagas
- **FR-010**: Ícone de "visualizar detalhes" (olho) DEVE estar visível para todos os usuários em cada evento
- **FR-011**: Ícone de editar DEVE aparecer habilitado apenas para criador ou responsáveis do evento
- **FR-012a**: Ícone de inscrição DEVE ser exibido apenas quando o usuário NÃO está inscrito no evento; ao se inscrever, o ícone DEVE desaparecer da listagem
- **FR-012b**: Sistema DEVE oferecer visão "Meus eventos como responsável" exibindo somente eventos em que o usuário é criador ou está em evento_responsaveis
- **FR-012c**: Sistema DEVE oferecer visão "Meus eventos inscritos" exibindo somente eventos com status AGENDADO em que o usuário possui inscrição ativa (status INSCRITO); nessa visão, ícone de cancelar inscrição está disponível e ícone de inscrever-se não é exibido

**Detalhes e Geolocalização:**
- **FR-012**: Tela de detalhes DEVE exibir todas as informações do evento incluindo localização com link para mapa externo
- **FR-013**: Sistema DEVE armazenar localização geográfica do evento (coordenadas ou endereço geocodificado)
- **FR-014**: Na tela de detalhes, DEVE haver opção para abrir localização no Google Maps ou Waze

**Inscrição:**
- **FR-015**: Sistema DEVE permitir inscrição de voluntário em evento AGENDADO com vagas disponíveis e dentro do prazo
- **FR-016**: Sistema DEVE impedir inscrição duplicada no mesmo evento
- **FR-017**: Sistema DEVE impedir inscrição quando limite de vagas foi atingido
- **FR-018**: Sistema DEVE impedir inscrição após a data/hora de início do evento
- **FR-019**: Sistema DEVE impedir inscrição em eventos com status CANCELADO ou FINALIZADO
- **FR-020**: Inscrição DEVE nascer com status INSCRITO
- **FR-021**: Sistema DEVE exibir mensagem de sucesso após inscrição bem-sucedida
- **FR-022**: Sistema DEVE permitir cancelamento de inscrição antes de evento ser iniciado
- **FR-023**: Ao cancelar inscrição, vaga DEVE ser liberada imediatamente se houver limite

**Responsáveis:**
- **FR-024**: Sistema DEVE manter tabela evento_responsaveis com campos: id, evento_id, voluntario_id (tipo_responsavel reservado para uso futuro)
- **FR-025**: Permissões de editar, finalizar e cancelar evento DEVEM verificar criado_por_id OU presença na tabela evento_responsaveis

**Finalização e Cancelamento:**
- **FR-026**: Sistema DEVE permitir criador ou responsável finalizar evento, exibindo lista de inscritos para marcar presença/ausência
- **FR-027**: Após finalização, status muda para FINALIZADO e novas inscrições são bloqueadas
- **FR-028**: Sistema DEVE permitir criador ou responsável cancelar evento, mudando status para CANCELADO
- **FR-029**: Apenas presenças com status PRESENTE DEVEM ser contabilizadas nos indicadores

**Dashboard:**
- **FR-030**: Sistema DEVE exibir dashboard de participação na aba de eventos com lista de voluntários e pontuação
- **FR-031**: Dashboard DEVE ser filtrável por semestre e por nome do voluntário
- **FR-032**: Pontuação de cada voluntário DEVE refletir somente participações confirmadas como PRESENTE

### Key Entities

- **Evento**: tipo, titulo, descricao, data_inicio, data_fim, local_latitude, local_longitude, local_descricao, cidade_id (FK), status, limite_vagas (nullable), feedback_habilitado, criado_por_id (FK users), created_at, updated_at, deleted_at. *(evento_origem_id removido)*
- **EventoParticipante**: evento_id (FK), user_id (FK), status (INSCRITO/PRESENTE/AUSENTE/CANCELADO), presenca_confirmada_por_id (FK users, nullable), presenca_confirmada_em (timestamp, nullable), created_at, updated_at
- **EventoResponsavel**: id, evento_id (FK), voluntario_id (FK users), tipo_responsavel *(campo reservado — não utilizado até definição dos cargos de voluntários)*
- **Cidade**: já existe no sistema
- **User**: já existe no sistema

---
>>>>>>> 002-eventos-aprimoramentos

## Success Criteria *(mandatory)*

### Measurable Outcomes

<<<<<<< HEAD
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
=======
- **SC-001**: Voluntários conseguem criar novo evento em menos de 2 minutos
- **SC-002**: Voluntários conseguem localizar evento específico por filtros em menos de 30 segundos
- **SC-003**: Taxa de sucesso na inscrição em evento (sem erros de sistema) acima de 95%
- **SC-004**: Limite de vagas é respeitado — nunca mais inscritos que o limite definido
- **SC-005**: Dashboard de participação carrega em menos de 3 segundos
- **SC-006**: 100% dos eventos criados iniciam com status AGENDADO
- **SC-007**: Nenhum usuário sem vínculo com o evento consegue editar, finalizar ou cancelar
- **SC-008**: 100% das tentativas de inscrição em evento cheio ou após início são bloqueadas com mensagem clara
- **SC-009**: Localização de todos os eventos criados pode ser aberta em aplicativo de mapa externo

---
>>>>>>> 002-eventos-aprimoramentos

## Assumptions

- Autenticação e sistema de usuários já estão implementados (Laravel Fortify)
<<<<<<< HEAD
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
=======
- Sistema de cargos/autorização já existe; permissão de acesso às ações de evento é baseada em criado_por_id ou presença em evento_responsaveis
- Cidades já existem no banco de dados
- Semestres são strings no formato "AAAA-N" (ex.: "2026-1", "2026-2")
- Limite de vagas é opcional; se não informado, evento aceita inscrições ilimitadas
- feedback_habilitado é campo boolean; a funcionalidade de coletar feedback não faz parte desta feature
- Soft delete será usado via SoftDeletes trait no modelo Evento
- Geolocalização: será usado serviço de geocodificação para converter endereço em coordenadas; integração com Google Maps ou Waze será via deep link (não embed de mapa)
- tipo_responsavel em EventoResponsavel está reservado e será comentado no código até definição dos cargos de voluntários
- Email de notificação NÃO faz parte desta feature
- Exportação de calendário (iCal) NÃO faz parte desta feature
- A funcionalidade de transferência de evento (evento_origem_id) foi removida do escopo; status TRANSFERIDO também é removido
>>>>>>> 002-eventos-aprimoramentos
