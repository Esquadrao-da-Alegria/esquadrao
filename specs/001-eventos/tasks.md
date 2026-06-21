<<<<<<< HEAD
# Tasks for Sistema de Eventos (specs/001-eventos)

## Setup

- [ ] 1. Criar migration `create_eventos_table` com campos especificados
- [ ] 2. Criar migration `create_evento_participantes_table`
- [ ] 3. Criar enums PHP: `TipoEvento`, `StatusEvento`, `StatusInscricao`
- [ ] 4. Criar models: `App\Models\Evento`, `App\Models\EventoParticipante` (Evento usa SoftDeletes)

## Core

- [ ] 5. Criar `App\Queries\Evento\Queries` com suporte a filtros: tipo, cidade_id, semestre, status, retornar_lista
- [ ] 6. Criar `App\Services\Evento\Service` (métodos index, store, update, destroy, buscarListaAgrupada...)
- [ ] 7. Criar `App\Services\Evento\Form\Service` que retorna dados auxiliares (cidades, responsáveis)
- [ ] 8. Criar `App\Http\Controllers\Web\EventoController` (index, create, store, edit, update, destroy, show)
- [ ] 9. Registrar rotas em `routes/web.php` (resource `/eventos` + actions de inscrição/presença)
- [ ] 10. Criar `App\Http\Requests\Web\Evento\StoreRequest` e `UpdateRequest` (validações implementadas)

## Frontend

- [ ] 11. Criar páginas Inertia TSX: `resources/js/Pages/Evento/Index.tsx`, `Create.tsx`, `Edit.tsx`, `Show.tsx` (iniciar com layout e props mínimos)
- [ ] 12. Criar componente `CardEvento` e listar eventos na `Index` com filtros básicos

## Inscrições & Presenças

- [ ] 13. Criar model/controller/action para inscrição `POST /eventos/{evento}/inscrever`
- [ ] 14. Criar endpoint para cancelar inscrição `DELETE /eventos/{evento}/inscricao`
- [ ] 15. Criar controller/actions e páginas para gerenciar presenças (`GET /eventos/{evento}/presencas`, `PATCH /eventos/{evento}/presencas`)

## Policies & Permissions

- [ ] 16. Implementar checagens de autorização (apenas responsáveis/diretor podem criar/finalizar/cancelar/transferir)

## Tests

- [ ] 17. Teste de criação de evento (OFICINA)
- [ ] 18. Teste de criação de evento (REUNIAO)
- [ ] 19. Teste bloqueando criação por usuário sem permissão
- [ ] 20. Teste de listagem com filtros
- [ ] 21. Teste de inscrição com sucesso
- [ ] 22. Teste de duplicidade de inscrição
- [ ] 23. Teste de limite de vagas
- [ ] 24. Teste de cancelamento de inscrição
- [ ] 25. Teste de finalização de evento e bloqueio de novas inscrições
- [ ] 26. Teste de confirmação de presença por responsável

## Polish / Docs

- [ ] 27. Documentar endpoints e fluxo em `specs/001-eventos/contracts` (opcional)
- [ ] 28. Criar seeders/samples para eventos em ambiente de desenvolvimento


---

Notes:
- Tarefas estão organizadas por dependência: Setup → Core → Frontend → Inscrições/Presenças → Tests → Polish
- Marcar tarefas como concluídas no arquivo quando implementadas
=======
# Tasks: Sistema de Eventos — Aprimoramentos

**Branch**: `002-eventos-aprimoramentos`
**Input**: specs/001-eventos/plan.md · spec.md · data-model.md · research.md
**Spec**: specs/001-eventos/spec.md

---

## Phase 1: Setup (Pré-condições)

**Purpose**: Verificar ambiente e confirmar que o branch e as dependências estão prontos.

- [ ] T001 Confirmar branch ativo é `002-eventos-aprimoramentos` com `git branch --show-current`
- [ ] T002 Rodar `php artisan migrate:status` e verificar que migrations de eventos anteriores estão aplicadas

---

## Phase 2: Foundational (Pré-requisitos bloqueantes)

**Purpose**: Alterações de banco, modelos e roteamento que TODAS as user stories dependem. Nenhuma fase de user story pode começar antes desta estar completa.

**⚠️ CRÍTICO**: Completar esta fase inteira antes de iniciar qualquer user story.

### Migrations

- [ ] T003 Criar migration `drop_evento_origem_from_eventos_table` — remove foreign key e coluna `evento_origem_id` da tabela `eventos` em `database/migrations/`
- [ ] T004 Criar migration `update_eventos_geolocation_fields` — remove coluna `local` (varchar), adiciona `local_descricao` (varchar 500), `local_latitude` (decimal 10,7 nullable), `local_longitude` (decimal 10,7 nullable) em `database/migrations/`
- [ ] T005 Criar migration `create_evento_responsaveis_table` — tabela com `id`, `evento_id` (FK cascade), `voluntario_id` (FK cascade), `tipo_responsavel` (nullable, com comment de uso futuro), `timestamps`, unique(`evento_id`,`voluntario_id`) em `database/migrations/`
- [ ] T006 Rodar `php artisan migrate` e confirmar que as 3 migrations aplicam sem erro

### Enums e Models

- [ ] T007 [P] Atualizar `app/Enums/StatusEvento.php` — remover case `TRANSFERIDO`; valores finais: `AGENDADO`, `FINALIZADO`, `CANCELADO`
- [ ] T008 [P] Atualizar `app/Models/Evento.php` — atualizar `$fillable` (remover `local`, `evento_origem_id`; adicionar `local_descricao`, `local_latitude`, `local_longitude`), remover relacionamentos `eventoOrigem()` e `eventosTransferidos()`, adicionar `responsaveis()` → `hasMany(EventoResponsavel::class)`, adicionar cast `local_latitude` e `local_longitude` como `float`
- [ ] T009 [P] Criar `app/Models/EventoResponsavel.php` — `HasFactory`, fillable `[evento_id, voluntario_id, tipo_responsavel]`, relacionamentos `evento()` e `voluntario()`

### Autorização

- [ ] T010 Criar `app/Policies/EventoPolicy.php` — métodos `update`, `finalizar`, `cancelar`; todos verificam `$user->id === $evento->criado_por_id OR $evento->responsaveis()->where('voluntario_id', $user->id)->exists()`
- [ ] T011 Registrar `EventoPolicy` em `app/Providers/AppServiceProvider.php` (ou `AuthServiceProvider` se existir) mapeando `Evento::class => EventoPolicy::class`

### Rotas

- [ ] T012 Atualizar `routes/web.php` — adicionar ANTES do `Route::resource('/eventos', ...)` as rotas estáticas: `GET /eventos/meus-responsaveis` (nome: `eventos.meus-responsaveis`), `GET /eventos/meus-inscritos` (nome: `eventos.meus-inscritos`), `GET /eventos/dashboard` (nome: `eventos.dashboard`); adicionar DEPOIS do resource: `POST /eventos/{evento}/inscrever`, `DELETE /eventos/{evento}/inscricao`, `POST /eventos/{evento}/finalizar`, `POST /eventos/{evento}/cancelar`

### Form Requests

- [ ] T013 [P] Atualizar `app/Http/Requests/Web/Evento/StoreRequest.php` — remover campo `evento_origem_id` e `semestre`, substituir `local` por `local_descricao` (required, string, max:500), adicionar `local_latitude` (nullable, numeric, between:-90,90), `local_longitude` (nullable, numeric, between:-180,180); adicionar regra unique condicional: título não pode duplicar com evento AGENDADO (`Rule::unique('eventos','titulo')->where('status','AGENDADO')`)
- [ ] T014 [P] Atualizar `app/Http/Requests/Web/Evento/UpdateRequest.php` — mesmas mudanças do T013 com `->ignore($this->route('evento')->id ?? null)`
- [ ] T015 Criar `app/Http/Requests/Web/Evento/FinalizarRequest.php` — regras: `presencas` (required, array), `presencas.*.user_id` (required, integer, exists:users,id), `presencas.*.status` (required, in:PRESENTE,AUSENTE), mensagens em português

**Checkpoint**: Migrations aplicadas, models atualizados, policy registrada, rotas configuradas, requests criados. Pronto para user stories.

---

## Phase 3: US1 — Criar evento (Priority: P1) 🎯 MVP

**Goal**: Responsável consegue criar evento com geolocalização; sistema bloqueia título duplicado; exibe mensagem de sucesso.

**Independent Test**: Criar evento OFICINA com endereço → evento aparece na listagem com status AGENDADO; tentar criar segundo evento com mesmo título → receber erro; campos `local_descricao`, `local_latitude`, `local_longitude` persistidos no banco.

- [ ] T016 [P] [US1] Atualizar `app/Services/Evento/Service.php` — método `store()`: remover `Arr::except` de `evento_origem_id` e `semestre`, garantir que `local_descricao/lat/lon` são passados ao Queries; manter `session()->flash('mensagem_sucesso', 'Evento criado com sucesso!')`
- [ ] T017 [P] [US1] Atualizar `app/Services/Evento/Form/Service.php` — remover referências a `evento_origem_id`; garantir que `buscarDados()` não retorna mais campo de origem
- [ ] T018 [US1] Atualizar `resources/js/Pages/Evento/Create.tsx` — remover campo `evento_origem` do formulário; adicionar campo de texto `local_descricao` com botão "Buscar localização" que chama `https://nominatim.openstreetmap.org/search?format=json&q={valor}&limit=5`; exibir sugestões de endereço; ao selecionar, preencher campos ocultos `local_latitude` e `local_longitude`; usar `<input type="datetime-local">` para data/hora início e fim

**Checkpoint**: Criação de evento funciona com geolocalização e bloqueia duplicata.

---

## Phase 4: US2 — Listar e filtrar eventos (Priority: P1) 🎯 MVP

**Goal**: Voluntário visualiza lista de eventos com filtros por tipo/data, ordenação por horário, ícone de olho para todos, ícone de inscrição apenas se não inscrito, ícone de editar apenas para criador/responsável.

**Independent Test**: Acessar `/eventos` → ver lista; aplicar filtro tipo OFICINA → apenas oficinas; ordenar por hora_inicio → lista reordenada; evento onde usuário é inscrito → ícone de inscrição ausente; evento onde usuário não é responsável → ícone de editar ausente.

- [ ] T019 [P] [US2] Atualizar `app/Queries/Evento/Queries.php` — adicionar ao `aplicarFiltros()` os casos: `'tipo'` (`whereEnum`), `'data'` (`whereDate('data_inicio')`), `'ordenar_por'` com suporte a `'data_inicio'` e `'data_fim'` com direção; carregar eager `with(['cidade', 'participantes', 'responsaveis'])` na query de index
- [ ] T020 [P] [US2] Atualizar `app/Services/Evento/Service.php` — método `index()`: após buscar lista, mapear cada evento adicionando `inscrito` (bool: participante com user_id=Auth::id() e status INSCRITO existe) e `pode_editar` (bool: criado_por_id === Auth::id() OR responsaveis contém Auth::id())
- [ ] T021 [US2] Atualizar `app/Http/Controllers/Web/EventoController.php` — `index()`: passar filtros de tipo, data e ordenar_por do request; passar lista enriquecida com `inscrito` e `pode_editar` ao Inertia
- [ ] T022 [US2] Atualizar `resources/js/Pages/Evento/Index.tsx` — adicionar ícone `Eye` (lucide-react) como link para `show` visível para todos; exibir ícone de inscrição apenas se `!evento.inscrito && evento.status === 'AGENDADO'`; exibir ícones de editar (`Pencil`), finalizar (`CheckSquare`) e cancelar (`XCircle`) apenas se `evento.pode_editar`; adicionar select de tipo (OFICINA/REUNIAO/todos) e date picker de data no topo da listagem; adicionar select de ordenação (Data início, Data fim)

**Checkpoint**: Listagem com filtros, ordenação e ícones condicionais funcionando.

---

## Phase 5: US3 — Visualizar detalhes de evento (Priority: P1) 🎯 MVP

**Goal**: Voluntário acessa tela de detalhes com todas as informações e links para abrir o local no Google Maps / Waze; botão de inscrição condicional.

**Independent Test**: Clicar no ícone de olho → ver detalhes completos; clicar "Abrir no Google Maps" → abre maps com coordenadas; usuário inscrito → não vê botão "Inscrever-se", vê "Cancelar inscrição".

- [ ] T023 [P] [US3] Atualizar `app/Http/Controllers/Web/EventoController.php` — método `show()`: carregar evento com `cidade`, `criadoPor`, `responsaveis.voluntario`, `participantes.usuario`; calcular `inscrito` e `pode_editar` para o usuário autenticado; passar ao Inertia
- [ ] T024 [US3] Atualizar `resources/js/Pages/Evento/Show.tsx` — exibir todos os campos; na seção de localização exibir `local_descricao` e dois botões: "Abrir no Google Maps" (`href="https://maps.google.com/?q={lat},{lon}"`) e "Abrir no Waze" (`href="https://waze.com/ul?ll={lat},{lon}&navigate=yes"`), com `target="_blank" rel="noopener noreferrer"`; botão "Inscrever-se" visível apenas se `!inscrito && status==='AGENDADO'`; botão "Cancelar inscrição" visível apenas se `inscrito && status==='AGENDADO'`; nenhum botão se `CANCELADO` ou `FINALIZADO`

**Checkpoint**: P1 completo — criação, listagem e detalhes funcionando.

---

## Phase 6: US6 — Editar evento (Priority: P2)

**Goal**: Apenas criador ou responsável acessa e salva edição; outros recebem erro de autorização.

**Independent Test**: Usuário não-responsável tenta acessar edição → redirecionado com erro; usuário responsável edita e salva → dados atualizados; campo de geolocalização funciona igual ao Create.

- [ ] T025 [P] [US6] Atualizar `app/Http/Controllers/Web/EventoController.php` — métodos `edit()` e `update()`: adicionar `$this->authorize('update', $evento)` antes de qualquer lógica
- [ ] T026 [US6] Atualizar `resources/js/Pages/Evento/Edit.tsx` — remover campo `evento_origem`; adicionar campo de geolocalização igual ao Create.tsx (T018); usar `<input type="datetime-local">` para datas

**Checkpoint**: Edição restrita a criador/responsável.

---

## Phase 7: US4 — Inscrever-se em evento (Priority: P2)

**Goal**: Voluntário se inscreve com sucesso; sistema bloqueia inscrição duplicada, evento cheio, após início, ou em evento não-AGENDADO; mensagem de sucesso exibida.

**Independent Test**: Inscrever em evento AGENDADO com vagas → inscrição criada, mensagem de sucesso; tentar segunda vez → erro; evento cheio → erro; data passada → erro.

- [ ] T027 [P] [US4] Atualizar `app/Services/Evento/Service.php` — adicionar método `inscrever(int $eventoId, int $userId)`: validar status AGENDADO, data_inicio > now(), vagas disponíveis, ausência de inscrição ativa; criar EventoParticipante; `flash('mensagem_sucesso', 'Inscrição realizada com sucesso!')` ou retornar erro específico
- [ ] T028 [P] [US4] Atualizar `app/Http/Controllers/Web/EventoController.php` — adicionar método `inscrever(Evento $evento)`: chamar `$this->service->inscrever($evento->id, Auth::id())`; redirecionar com resultado
- [ ] T029 [US4] Atualizar `resources/js/Pages/Evento/Index.tsx` e `Show.tsx` — ícone/botão de inscrição submete `router.post(route('eventos.inscrever', evento.id))` via Inertia; exibir toast de sucesso/erro com `react-hot-toast`

**Checkpoint**: Inscrição com todas as regras de negócio funcionando.

---

## Phase 8: US9 — Meus eventos como responsável (Priority: P2)

**Goal**: Voluntário vê listagem apenas dos eventos em que é criador ou responsável, com ações de gerenciamento disponíveis.

**Independent Test**: Acessar `/eventos/meus-responsaveis` → ver apenas eventos criados pelo usuário ou onde está em `evento_responsaveis`; todos os ícones de gerenciamento visíveis.

- [ ] T030 [P] [US9] Atualizar `app/Queries/Evento/Queries.php` — adicionar método `meusResponsaveis(int $userId)`: query com `where('criado_por_id', $userId)->orWhereHas('responsaveis', fn($q) => $q->where('voluntario_id', $userId))`; eager load `cidade`
- [ ] T031 [P] [US9] Atualizar `app/Services/Evento/Service.php` — adicionar método `meusResponsaveis(int $userId)`: delega a Queries, retorna lista com resposta padronizada
- [ ] T032 [P] [US9] Atualizar `app/Http/Controllers/Web/EventoController.php` — adicionar método `meusResponsaveis()`: chamar service, passar ao Inertia `Evento/MeusResponsaveis`
- [ ] T033 [US9] Criar `resources/js/Pages/Evento/MeusResponsaveis.tsx` — layout igual ao `Index.tsx` com `PainelLayout`; tabela com mesmas colunas; todos os ícones de editar, finalizar e cancelar visíveis em todos os itens (sem verificação condicional)

**Checkpoint**: Visão de responsabilidades funcionando.

---

## Phase 9: US10 — Meus eventos inscritos (Priority: P2)

**Goal**: Voluntário vê apenas eventos AGENDADOS em que está inscrito (status INSCRITO); ícone de cancelar inscrição disponível; sem ícone de inscrever-se.

**Independent Test**: Acessar `/eventos/meus-inscritos` → ver apenas eventos AGENDADOS com inscrição ativa; evento cancelado ou finalizado não aparece; ícone de cancelar inscrição visível.

- [ ] T034 [P] [US10] Atualizar `app/Queries/Evento/Queries.php` — adicionar método `meusInscritos(int $userId)`: query com join em `evento_participantes` filtrando `user_id=$userId`, `evento_participantes.status=INSCRITO`, `eventos.status=AGENDADO`; eager load `cidade`
- [ ] T035 [P] [US10] Atualizar `app/Services/Evento/Service.php` — adicionar método `meusInscritos(int $userId)`: delega a Queries
- [ ] T036 [P] [US10] Atualizar `app/Http/Controllers/Web/EventoController.php` — adicionar método `meusInscritos()`: chamar service, passar ao Inertia `Evento/MeusInscritos`
- [ ] T037 [US10] Criar `resources/js/Pages/Evento/MeusInscritos.tsx` — layout igual ao `Index.tsx`; tabela de eventos; ícone de cancelar inscrição (`UserMinus` lucide) em cada linha; sem ícone de inscrever-se; ícone de olho presente

**Checkpoint**: Todas as user stories P2 concluídas.

---

## Phase 10: US5 — Cancelar inscrição (Priority: P3)

**Goal**: Voluntário inscrito cancela inscrição antes do início; vaga liberada; erro se evento já finalizado.

**Independent Test**: Voluntário com inscrição INSCRITO cancela → status vira CANCELADO; evento com limite tem vaga liberada; evento finalizado → erro.

- [ ] T038 [P] [US5] Atualizar `app/Services/Evento/Service.php` — adicionar método `cancelarInscricao(int $eventoId, int $userId)`: verificar que evento está AGENDADO e data_inicio > now(); mudar status da inscrição para CANCELADO; `flash('mensagem_sucesso', 'Inscrição cancelada.')`
- [ ] T039 [P] [US5] Atualizar `app/Http/Controllers/Web/EventoController.php` — adicionar método `cancelarInscricao(Evento $evento)`: chamar service, redirecionar
- [ ] T040 [US5] Atualizar `resources/js/Pages/Evento/Show.tsx` e `MeusInscritos.tsx` — botão/ícone "Cancelar inscrição" submete `router.delete(route('eventos.cancelar-inscricao', evento.id))`

---

## Phase 11: US7 — Finalizar evento e registrar presença (Priority: P3)

**Goal**: Criador/responsável finaliza evento selecionando presença de cada inscrito; status muda para FINALIZADO.

**Independent Test**: Responsável acessa finalização → lista de inscritos com toggle presente/ausente; confirma → evento FINALIZADO, status dos participantes atualizados; não-responsável → erro de autorização.

- [ ] T041 [P] [US7] Atualizar `app/Services/Evento/Service.php` — adicionar método `finalizar(int $eventoId, array $presencas, User $user)`: autorizar via Policy; atualizar status de cada EventoParticipante para PRESENTE/AUSENTE com `presenca_confirmada_por_id` e `presenca_confirmada_em`; mudar status do evento para FINALIZADO; `flash('mensagem_sucesso', 'Evento finalizado com sucesso!')`
- [ ] T042 [P] [US7] Atualizar `app/Http/Controllers/Web/EventoController.php` — adicionar método `finalizar(FinalizarRequest $request, Evento $evento)`: `$this->authorize('finalizar', $evento)`; chamar service com `$request->validated()`
- [ ] T043 [US7] Criar `resources/js/Pages/Evento/Finalizar.tsx` — `PainelLayout`; lista de inscritos com toggle PRESENTE/AUSENTE por participante (radio button ou checkbox); botão "Confirmar finalização" submete `router.post(route('eventos.finalizar', evento.id), { presencas })`
- [ ] T044 [US7] Atualizar `resources/js/Pages/Evento/Index.tsx` e `MeusResponsaveis.tsx` — ícone `CheckSquare` de finalizar leva para `Evento/Finalizar` passando id do evento

---

## Phase 12: US8 — Cancelar evento (Priority: P3)

**Goal**: Criador/responsável cancela evento com confirmação; status muda para CANCELADO; inscrições bloqueadas.

**Independent Test**: Responsável clica cancelar → confirmação exibida; confirma → status CANCELADO; voluntário tenta se inscrever → erro; não-responsável → erro de autorização.

- [ ] T045 [P] [US8] Atualizar `app/Services/Evento/Service.php` — adicionar método `cancelarEvento(int $eventoId, User $user)`: autorizar via Policy; mudar status para CANCELADO; `flash('mensagem_sucesso', 'Evento cancelado.')`
- [ ] T046 [P] [US8] Atualizar `app/Http/Controllers/Web/EventoController.php` — adicionar método `cancelar(Evento $evento)`: `$this->authorize('cancelar', $evento)`; chamar service
- [ ] T047 [US8] Atualizar `resources/js/Pages/Evento/Index.tsx` e `MeusResponsaveis.tsx` — ícone `XCircle` de cancelar abre confirmação (`sweetalert2`) e submete `router.post(route('eventos.cancelar', evento.id))` ao confirmar

---

## Phase 13: US11 — Dashboard de participação (Priority: P3)

**Goal**: Usuário visualiza lista de voluntários com contagem de eventos participados; filtrável por semestre e nome.

**Independent Test**: Acessar `/eventos/dashboard` → lista de voluntários com contagem; filtrar por semestre → apenas presenças daquele período; filtrar por nome → resultados reduzidos; apenas status PRESENTE contabilizado.

- [ ] T048 [P] [US11] Atualizar `app/Queries/Evento/Queries.php` — adicionar método `dashboard(array $filtros)`: query em `evento_participantes` com join em `users` e `eventos`; filtrar por `status=PRESENTE`; agrupar por `user_id`; `selectRaw('users.id, users.name, COUNT(*) as total_presencas')`; suportar filtro `semestre` (join com eventos e where semestre) e `nome` (where users.name like); retornar coleção ordenada por `name`
- [ ] T049 [P] [US11] Atualizar `app/Services/Evento/Service.php` — adicionar método `dashboard(array $filtros)`: delega a Queries; retorna resultado padronizado
- [ ] T050 [P] [US11] Atualizar `app/Http/Controllers/Web/EventoController.php` — adicionar método `dashboard()`: passar filtros do request ao service; passar dados ao Inertia `Evento/Dashboard`; incluir lista de semestres disponíveis para o select
- [ ] T051 [US11] Criar `resources/js/Pages/Evento/Dashboard.tsx` — `PainelLayout`; tabela com colunas: Nome, Total de presenças; select de semestre e input de nome como filtros; filtro de nome feito no frontend sobre os dados carregados; filtro de semestre recarrega via Inertia

---

## Phase 14: Polish & Cross-Cutting

**Purpose**: Ajustes finais que afetam múltiplas user stories.

- [ ] T052 [P] Atualizar `database/seeders/EventoSeeder.php` — substituir campo `local` por `local_descricao`, `local_latitude`, `local_longitude`; remover `evento_origem_id` e `semestre` dos dados de seed
- [ ] T053 [P] Atualizar `database/seeders/DatabaseSeeder.php` — adicionar `EventoResponsavelSeeder` se necessário, ou associar responsáveis nos seeders existentes
- [ ] T054 Adicionar links de navegação para "Meus Eventos como Responsável", "Meus Eventos Inscritos" e "Dashboard" no menu lateral ou header de `resources/js/layouts/` (seguir padrão do AppSidebarLayout existente)
- [ ] T055 Revisar todas as mensagens flash de sucesso/erro em `app/Services/Evento/Service.php` para consistência de tom em português
- [ ] T056 Rodar `php artisan route:list | grep eventos` e confirmar que todas as 11 rotas estão registradas sem conflitos

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 (Setup)**: Sem dependências — iniciar imediatamente
- **Phase 2 (Foundational)**: Depende de Phase 1 — **BLOQUEIA todas as user stories**
- **Phase 3–5 (P1)**: Dependem de Phase 2; podem rodar em sequência (US1→US2→US3 pois US2 usa dados criados por US1)
- **Phase 6–9 (P2)**: Dependem de Phase 2 e de MVP P1 concluído; US6, US9, US10 podem rodar em paralelo; US4 depende de US2 (listagem precisa exibir o botão)
- **Phase 10–13 (P3)**: Dependem de US4 (US5 cancela inscrição criada por US4); US7, US8 independentes entre si; US11 depende de US7 (precisa de presenças)
- **Phase 14 (Polish)**: Depende de todas as fases anteriores

### User Story Dependencies

| US | Depende de | Pode paralelizar com |
|---|---|---|
| US1 (criar) | Foundational | — |
| US2 (listar) | US1 | — |
| US3 (detalhes) | US2 | US6 |
| US6 (editar) | Foundational | US3, US9, US10 |
| US4 (inscrever) | US2 | US9, US10 |
| US9 (meus resp.) | Foundational | US6, US10 |
| US10 (meus insc.) | US4 | US6, US9 |
| US5 (cancelar insc.) | US4 | US7, US8 |
| US7 (finalizar) | US4 | US8 |
| US8 (cancelar ev.) | Foundational | US7 |
| US11 (dashboard) | US7 | — |

### Parallel Opportunities por fase

```
Phase 2 em paralelo:
  T003, T004, T005 (migrations independentes — arquivos distintos)
  T007, T008, T009 (enum + models — arquivos distintos)
  T013, T014 (StoreRequest + UpdateRequest — arquivos distintos)

Phase 8 em paralelo:
  T027 (Service.inscrever) + T028 (Controller.inscrever)

Phase 8-9 em paralelo após Foundational:
  US6 (T025-T026) || US9 (T030-T033) || US10 (T034-T037)
```

---

## Implementation Strategy

### MVP (P1 — Phases 1–5)

1. Completar Phase 1 + Phase 2 (Foundational)
2. Phase 3 (US1 — criar)
3. Phase 4 (US2 — listar)
4. Phase 5 (US3 — detalhes)
5. **VALIDAR**: criar evento, ver na lista, abrir detalhes, links de mapa funcionando
6. Deploy/demo com esta base

### Entrega Incremental

- **MVP** (P1): criar + listar + detalhes + geolocalização
- **Incremento 1** (P2): inscrição + edição com permissão + meus eventos
- **Incremento 2** (P3): cancelar inscrição + finalizar + cancelar evento + dashboard

### Estratégia de time único (sequencial)

Seguir a ordem das fases: Foundation → US1 → US2 → US3 → US6 → US4 → US9 → US10 → US5 → US7 → US8 → US11 → Polish

---

## Notes

- `[P]` = pode rodar em paralelo (arquivos distintos, sem dependência de tarefa incompleta)
- `[USN]` = rastreabilidade com a user story correspondente em `spec.md`
- Cada checkpoint valida a US de forma independente antes de avançar
- Mensagens de sucesso/erro: usar `session()->flash()` no Service e ler via `usePage().props.flash` no TSX
- Nominatim: chamar do frontend com `fetch`, não do backend (evita latência adicional no PHP)
- Policy: ao usar `$this->authorize()` no Controller, o Laravel lança `AuthorizationException` automaticamente → tela de 403
>>>>>>> 002-eventos-aprimoramentos
