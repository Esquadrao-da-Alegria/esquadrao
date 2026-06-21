# Feature Specification: Testes Unitários — Módulo de Eventos

**Feature Branch**: `002-testes-unitarios-eventos`

**Created**: 19/06/2026

**Status**: Draft

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Validar Criação de Evento (Priority: P1)

Como desenvolvedor, quero que o `store()` garanta que o evento é criado com o criador correto, os responsáveis são convertidos e salvos, e que erros causem rollback.

**Why this priority**: É o fluxo principal do módulo. Sem garantias aqui, todo o resto é instável.

**Independent Test**: Pode ser testado isoladamente mockando `Auth`, `DB` e `Queries` — sem dependências externas.

**Acceptance Scenarios**:

1. **Given** um payload válido com responsáveis, **When** `store()` é chamado, **Then** `criado_por_id` é igual a `Auth::id()` e `createMany` recebe `[['voluntario_id' => id], ...]`
2. **Given** `Queries::store()` retorna `sucesso = false`, **When** `store()` é chamado, **Then** `DB::rollBack()` é chamado e flash de erro é disparado
3. **Given** o banco lança uma exceção em qualquer ponto, **When** `store()` é chamado, **Then** o `catch` captura, `rollBack()` é chamado e `sucesso = false` é retornado

---

### User Story 2 — Validar Atualização de Evento (Priority: P2)

Como desenvolvedor, quero que o `update()` garanta que responsáveis antigos são removidos, novos são criados na ordem correta, e erros causem rollback.

**Why this priority**: Atualização é tão crítica quanto criação — sincronização errada de responsáveis compromete permissões.

**Independent Test**: Pode ser testado mockando `Queries` e verificando a sequência `delete() → createMany()`.

**Acceptance Scenarios**:

1. **Given** `Queries::update()` retorna `sucesso = false`, **When** `update()` é chamado, **Then** `DB::rollBack()` é chamado e flash de erro é disparado
2. **Given** o banco lança exceção, **When** `update()` é chamado, **Then** o `catch` captura e `rollBack()` é chamado
3. **Given** atualização bem-sucedida, **When** `update()` é chamado, **Then** `delete()` é chamado antes de `createMany()`

---

### User Story 3 — Validar Inscrição de Usuário (Priority: P2)

Como desenvolvedor, quero que `inscrever()` bloqueie corretamente todos os cenários inválidos e permita inscrição apenas quando todas as regras são satisfeitas.

**Why this priority**: Regras de negócio críticas — inscrição indevida compromete integridade dos eventos.

**Independent Test**: Cada cenário pode ser testado isoladamente mockando `Evento` e `EventoParticipante`.

**Acceptance Scenarios**:

1. **Given** evento AGENDADO, não iniciado, sem inscrição anterior, vagas disponíveis, **When** `inscrever()` é chamado, **Then** participante é criado com status INSCRITO e flash de sucesso é disparado
2. **Given** evento com status FINALIZADO ou CANCELADO, **When** `inscrever()` é chamado, **Then** erro é retornado sem criar registro
3. **Given** `data_inicio <= now()`, **When** `inscrever()` é chamado, **Then** erro é retornado sem criar registro
4. **Given** usuário já possui inscrição com status INSCRITO, **When** `inscrever()` é chamado, **Then** erro é retornado sem criar novo registro
5. **Given** `total inscritos = limite_vagas`, **When** `inscrever()` é chamado, **Then** erro é retornado sem criar registro
6. **Given** `limite_vagas = null`, **When** `inscrever()` é chamado, **Then** inscrição é realizada sem validar vagas
7. **Given** usuário possui inscrição com status CANCELADO, **When** `inscrever()` é chamado, **Then** status é atualizado para INSCRITO sem criar novo registro

---

### User Story 4 — Validar Cancelamento de Inscrição (Priority: P2)

Como desenvolvedor, quero que `cancelarInscricao()` só permita cancelamento quando há inscrição ativa e o evento não está finalizado.

**Why this priority**: Cancelamento indevido pode gerar inconsistência nos dados de presença.

**Independent Test**: Pode ser testado mockando `Evento` e `EventoParticipante`.

**Acceptance Scenarios**:

1. **Given** inscrição ativa com status INSCRITO, **When** `cancelarInscricao()` é chamado, **Then** status é atualizado para CANCELADO e flash de sucesso é disparado
2. **Given** evento com status FINALIZADO, **When** `cancelarInscricao()` é chamado, **Then** erro é retornado e status não é alterado
3. **Given** nenhum `EventoParticipante` com status INSCRITO para o usuário, **When** `cancelarInscricao()` é chamado, **Then** erro é retornado

---

### User Story 5 — Validar Finalização de Evento (Priority: P3)

Como desenvolvedor, quero que `finalizar()` só funcione em eventos AGENDADOS, registre o confirmador correto e faça rollback em caso de erro.

**Why this priority**: Finalização é irreversível — erros aqui têm impacto direto nos registros de presença.

**Independent Test**: Pode ser testado mockando `Evento`, `EventoParticipante` e `Auth`.

**Acceptance Scenarios**:

1. **Given** evento AGENDADO com participantes, **When** `finalizar()` é chamado, **Then** status do evento = FINALIZADO, participantes atualizados e `presenca_confirmada_por_id = Auth::id()`
2. **Given** evento com status CANCELADO ou FINALIZADO, **When** `finalizar()` é chamado, **Then** `rollBack()` é chamado e erro é retornado

---

### User Story 6 — Validar Cancelamento de Evento (Priority: P3)

Como desenvolvedor, quero que `cancelarEvento()` só cancele eventos AGENDADOS e retorne erro para qualquer outro status.

**Why this priority**: Cancelamento de evento afeta todos os inscritos — a regra de status deve ser rígida.

**Independent Test**: Pode ser testado mockando `Evento`.

**Acceptance Scenarios**:

1. **Given** evento com status AGENDADO, **When** `cancelarEvento()` é chamado, **Then** status = CANCELADO e flash de sucesso é disparado
2. **Given** evento com status FINALIZADO, **When** `cancelarEvento()` é chamado, **Then** erro é retornado e status não é alterado
3. **Given** evento com status CANCELADO, **When** `cancelarEvento()` é chamado, **Then** erro é retornado e status não é alterado

---

### Edge Cases

- O que acontece se `$responsaveisIds` chegar vazio mesmo com validação ativa?
- O que acontece se `Auth::id()` retornar `null` durante `store()`?
- O que acontece se `createMany()` falhar após `delete()` no `update()`?

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Todos os testes DEVEM usar mocks para `Auth::id()`, `DB` e repositório `Queries` — nenhum teste toca banco real
- **FR-002**: `store()` DEVE sempre definir `criado_por_id` como `Auth::id()`, ignorando qualquer valor do payload
- **FR-003**: `store()` DEVE converter o array de IDs de responsáveis para `[['voluntario_id' => id], ...]` antes de persistir
- **FR-004**: `update()` DEVE chamar `delete()` antes de `createMany()` — a ordem é obrigatória
- **FR-005**: Qualquer método com transação DEVE chamar `rollBack()` quando uma exceção for lançada
- **FR-006**: `session()->flash()` DEVE ser chamado com a mensagem correta em todos os fluxos de sucesso e erro
- **FR-007**: `inscrever()` DEVE reativar inscrição cancelada em vez de criar novo registro
- **FR-008**: `inscrever()` DEVE bloquear inscrição se `data_inicio <= now()`
- **FR-009**: `inscrever()` DEVE ignorar validação de vagas quando `limite_vagas = null`
- **FR-010**: `finalizar()` DEVE registrar `presenca_confirmada_por_id = Auth::id()` em todos os participantes atualizados

### Key Entities

- **EventoService**: Classe testada — contém toda a lógica de negócio do módulo
- **Queries (mock)**: Repositório mockado — simula acesso ao banco
- **Auth (mock)**: Facade mockada — retorna ID fixo do usuário autenticado
- **DB (mock)**: Facade mockada — verifica chamadas a `beginTransaction`, `commit` e `rollBack`
- **EventoParticipante (mock)**: Model mockado — simula registros de inscrição

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% dos casos de teste listados são implementados e passam
- **SC-002**: Nenhum teste acessa banco de dados real — verificado pela ausência de conexão ativa nos testes
- **SC-003**: Cobertura de 100% das branches dos métodos testados (`store`, `update`, `inscrever`, `cancelarInscricao`, `finalizar`, `cancelarEvento`)
- **SC-004**: Todos os fluxos [sad] verificam que `session()->flash()` foi chamado com mensagem de erro adequada
- **SC-005**: A ordem `delete() → createMany()` no `update()` é verificada por pelo menos um teste

## Assumptions

- Os testes seguem o padrão PHPUnit com Mockery ou mocks nativos do Laravel
- O `EventoService` recebe suas dependências via injeção — facilitando substituição por mocks
- O ambiente de testes usa banco SQLite em memória apenas para testes de integração (fora do escopo deste documento)
- Novos casos identificados durante implementação devem ser adicionados à seção "Casos Adicionais" do documento de referência
