<<<<<<< HEAD
# Implementation Plan: Sistema de Eventos

**Branch**: `dev-mauro` | **Date**: 2026-06-03 | **Spec**: specs/001-eventos/spec.md

**Input**: Feature specification from `specs/001-eventos/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Adicionar o módulo de eventos ao sistema existente, com cadastro de oficinas e reuniões, listagem filtrável, detalhes de evento e fluxo de inscrições/presenças. A implementação deve aproveitar a arquitetura Laravel + Inertia atual, reutilizar `User` e `Cidade`, e seguir o padrão Service/Query/Controller observado nas demais áreas do app.

## Technical Context

**Language/Version**: PHP 8.x com Laravel 10

**Primary Dependencies**: Laravel, Inertia, React/TSX, Fortify, Eloquent ORM

**Storage**: Banco relacional MySQL/MariaDB via Eloquent

**Testing**: PHPUnit com testes de Feature e Unit do Laravel

**Target Platform**: Aplicação web servida em Linux/PHP

**Project Type**: Aplicação web monolítica Laravel + Inertia

**Performance Goals**: consultas de eventos filtradas no backend; evitar carregar listas completas sem necessidade; manter consultas a cidades/participantes sem N+1

**Constraints**: reutilizar autenticação, autorização, `User` e `Cidade`; seguir o padrão de controllers, services, queries e requests já presente no repositório; suportar filtros por tipo/cidade/semestre/status sem expor todo o catálogo ao frontend

**Scale/Scope**: sistema de ONG com centenas de eventos e inscrições; foco em administrador/responsável e voluntário, sem multitenancy ou alta escala além do uso interno do app

## Constitution Check

A constituição em `.specify/memory/constitution.md` está em branco/placeholder e não define gates concretos. Este plano segue o padrão atual do repositório e presume que os critérios de revisão serão validados nas políticas de PR existentes.

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)
<!--
  ACTION REQUIRED: Replace the placeholder tree below with the concrete layout
  for this feature. Delete unused options and expand the chosen structure with
  real paths (e.g., apps/admin, packages/something). The delivered plan must
  not include Option labels.
-->

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── Web/EventoController.php
│   ├── Requests/
│   │   └── Web/Evento/
│   │       ├── StoreRequest.php
│   │       └── UpdateRequest.php
│   └── ...
├── Models/Evento.php
├── Models/EventoParticipante.php
├── Queries/Evento/Queries.php
├── Services/Evento/Service.php
├── Services/Evento/Form/Service.php
└── ...

resources/js/Pages/Evento/
├── Index.tsx
├── Create.tsx
├── Edit.tsx
├── Show.tsx
├── Presencas.tsx
└── Dashboard.tsx

routes/web.php
database/migrations/
tests/Feature/
tests/Unit/
```

**Structure Decision**: utilizar a arquitetura Laravel existente. O recurso de eventos seguirá o mesmo layout do repositório atual com modelos, serviços, queries e controllers no backend, requests específicos para validação, e páginas Inertia TSX no frontend.

## Complexity Tracking

Nenhuma violação de constituição identificada; o recurso usa o padrão já presente no projeto, sem necessidade de uma nova camada arquitetural.
=======
# Implementation Plan: Sistema de Eventos — Aprimoramentos

**Branch**: `002-eventos-aprimoramentos` | **Date**: 2026-06-10 | **Spec**: specs/001-eventos/spec.md

## Summary

Aprimorar o módulo de eventos existente com: remoção de `evento_origem_id`, nova tabela `evento_responsaveis`, autorização por responsável (EventoPolicy), geolocalização via Nominatim + deep links, filtros/ordenação na listagem, ícone de detalhes e inscrição condicional, visões "Meus Eventos como Responsável" e "Meus Eventos Inscritos", fluxo de finalização com seleção de presença, cancelamento de evento, e dashboard de participação. Toda implementação segue a arquitetura Laravel + Inertia + React/TSX existente.

## Technical Context

- **Language/Version**: PHP 8.2+, Laravel 12
- **Primary Dependencies**: Laravel, Inertia.js, React 19/TSX, Fortify, Eloquent ORM, Tailwind CSS 4, Radix UI, lucide-react
- **Storage**: MySQL 8 via Eloquent (soft deletes, enums como strings)
- **Testing**: PHPUnit — Feature tests para fluxos de inscrição/finalização
- **Geolocalização**: Nominatim (OpenStreetMap) — sem API key; deep links Google Maps/Waze para visualização
- **Date picker**: `<input type="datetime-local">` nativo
- **Autorização**: `EventoPolicy` Laravel para ações por dado (criador/responsável)
- **Padrão**: Controller → Service → Queries (mesmo padrão dos módulos Hospital, Patrocinador, Voluntario)

## Constitution Check

A constituição em `.specify/memory/constitution.md` está em placeholder. Este plano segue os padrões observados no repositório e será validado nas políticas de PR da equipe.

## Project Structure

### Arquivos novos ou modificados

```text
Backend
├── app/
│   ├── Enums/
│   │   └── StatusEvento.php                    ← MODIFICAR: remover TRANSFERIDO
│   ├── Models/
│   │   ├── Evento.php                          ← MODIFICAR: geoloc, remove evento_origem
│   │   ├── EventoParticipante.php              ← manter
│   │   └── EventoResponsavel.php               ← NOVO
│   ├── Policies/
│   │   └── EventoPolicy.php                    ← NOVO
│   ├── Http/
│   │   ├── Controllers/Web/
│   │   │   └── EventoController.php            ← MODIFICAR: +show, +inscrever, +cancelarInscricao, +finalizar, +cancelar, +meusResponsaveis, +meusInscritos, +dashboard
│   │   └── Requests/Web/Evento/
│   │       ├── StoreRequest.php                ← MODIFICAR: geoloc, remover evento_origem, +unique titulo/AGENDADO
│   │       ├── UpdateRequest.php               ← MODIFICAR: idem
│   │       ├── FinalizarRequest.php            ← NOVO: array de presenças
│   │       └── InscricaoRequest.php            ← NOVO (opcional, validação simples)
│   ├── Services/Evento/
│   │   ├── Service.php                         ← MODIFICAR: +inscrever, +cancelarInscricao, +finalizar, +cancelarEvento, +meusResponsaveis, +meusInscritos, +dashboard
│   │   └── Form/Service.php                    ← MODIFICAR: remover evento_origem, +cidades
│   └── Queries/Evento/
│       └── Queries.php                         ← MODIFICAR: +filtros geo, +meusResponsaveis, +meusInscritos, +dashboard, remover evento_origem
│
├── database/migrations/
│   ├── xxxx_drop_evento_origem_from_eventos_table.php   ← NOVO
│   ├── xxxx_update_eventos_geolocation_fields.php       ← NOVO
│   └── xxxx_create_evento_responsaveis_table.php        ← NOVO
│
└── routes/web.php                              ← MODIFICAR: +rotas customizadas

Frontend
└── resources/js/Pages/Evento/
    ├── Index.tsx                               ← MODIFICAR: +olho, inscrição condicional, edit condicional, filtros, ordenação
    ├── Show.tsx                                ← MODIFICAR: geoloc links, inscrição condicional
    ├── Create.tsx                              ← MODIFICAR: geoloc field, remove evento_origem
    ├── Edit.tsx                                ← MODIFICAR: geoloc field, remove evento_origem
    ├── Finalizar.tsx                           ← NOVO: lista inscritos c/ toggle presente/ausente
    ├── MeusResponsaveis.tsx                    ← NOVO
    ├── MeusInscritos.tsx                       ← NOVO
    └── Dashboard.tsx                           ← NOVO
```

## Rotas

```php
// Rotas estáticas ANTES do resource (evitar conflito de parâmetros)
Route::get('/eventos/meus-responsaveis', [EventoController::class, 'meusResponsaveis'])->name('eventos.meus-responsaveis');
Route::get('/eventos/meus-inscritos',    [EventoController::class, 'meusInscritos'])->name('eventos.meus-inscritos');
Route::get('/eventos/dashboard',         [EventoController::class, 'dashboard'])->name('eventos.dashboard');

// Resource padrão
Route::resource('/eventos', EventoController::class)->parameters(['eventos' => 'evento']);

// Ações de evento (fora do CRUD padrão)
Route::post('/eventos/{evento}/inscrever',         [EventoController::class, 'inscrever'])->name('eventos.inscrever');
Route::delete('/eventos/{evento}/inscricao',       [EventoController::class, 'cancelarInscricao'])->name('eventos.cancelar-inscricao');
Route::post('/eventos/{evento}/finalizar',         [EventoController::class, 'finalizar'])->name('eventos.finalizar');
Route::post('/eventos/{evento}/cancelar',          [EventoController::class, 'cancelar'])->name('eventos.cancelar');
```

## Detalhamento por camada

### 1. Migrations

**Drop evento_origem_id**
```sql
ALTER TABLE eventos DROP FOREIGN KEY fk_evento_origem;
ALTER TABLE eventos DROP COLUMN evento_origem_id;
```

**Geolocalização**
```sql
ALTER TABLE eventos DROP COLUMN local;
ALTER TABLE eventos ADD COLUMN local_descricao VARCHAR(500) AFTER data_fim;
ALTER TABLE eventos ADD COLUMN local_latitude DECIMAL(10,7) NULL AFTER local_descricao;
ALTER TABLE eventos ADD COLUMN local_longitude DECIMAL(10,7) NULL AFTER local_latitude;
```

**Tabela evento_responsaveis**
```sql
CREATE TABLE evento_responsaveis (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  evento_id BIGINT UNSIGNED NOT NULL,
  voluntario_id BIGINT UNSIGNED NOT NULL,
  tipo_responsavel VARCHAR(100) NULL COMMENT 'Reservado — aguarda definição de cargos',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY (evento_id, voluntario_id),
  FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
  FOREIGN KEY (voluntario_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 2. Models

**Evento.php** — fillable: `[tipo, titulo, descricao, data_inicio, data_fim, local_descricao, local_latitude, local_longitude, cidade_id, status, limite_vagas, feedback_habilitado, criado_por_id]`

Novo relacionamento: `responsaveis()` → `hasMany(EventoResponsavel::class)`

Método helper: `podeEditar(User $user): bool` → verifica `criado_por_id` ou `responsaveis()`

**EventoResponsavel.php** — novo model, fillable: `[evento_id, voluntario_id, tipo_responsavel]`

### 3. EventoPolicy

```php
public function update(User $user, Evento $evento): bool
{
    return $this->eCriadorOuResponsavel($user, $evento);
}
public function finalizar(User $user, Evento $evento): bool
{
    return $this->eCriadorOuResponsavel($user, $evento);
}
public function cancelar(User $user, Evento $evento): bool
{
    return $this->eCriadorOuResponsavel($user, $evento);
}
private function eCriadorOuResponsavel(User $user, Evento $evento): bool
{
    return $user->id === $evento->criado_por_id
        || $evento->responsaveis()->where('voluntario_id', $user->id)->exists();
}
```

Registrar em `AuthServiceProvider` (ou via Model discovery no Laravel 12).

### 4. Service — métodos adicionais

| Método | Descrição |
|---|---|
| `inscrever(int $eventoId, int $userId)` | Valida regras (AGENDADO, vaga, prazo, duplicata) → cria EventoParticipante |
| `cancelarInscricao(int $eventoId, int $userId)` | Muda status para CANCELADO, libera vaga |
| `finalizar(int $eventoId, array $presencas, User $user)` | Autoriza via Policy, atualiza status dos participantes, muda evento para FINALIZADO |
| `cancelarEvento(int $eventoId, User $user)` | Autoriza via Policy, muda status para CANCELADO |
| `meusResponsaveis(int $userId)` | Retorna eventos onde user é criador ou responsável |
| `meusInscritos(int $userId)` | Retorna eventos AGENDADOS onde user tem inscrição INSCRITO |
| `dashboard(array $filtros)` | Retorna voluntários com contagem de presenças; filtra por semestre e nome |

### 5. Queries — filtros adicionais

`aplicarFiltros()` ganha casos:
- `tipo` → `whereEnum('tipo', TipoEvento::from($valor))`
- `data` → `whereDate('data_inicio', $valor)`
- `ordenar_por` → `orderBy('data_inicio'|'data_fim', $direcao)`
- `meus_responsaveis` (user_id) → subquery em `evento_responsaveis` OU `criado_por_id`
- `meus_inscritos` (user_id) → join em `evento_participantes` com status INSCRITO

### 6. StoreRequest / UpdateRequest — alterações

- Remover: `evento_origem_id`, `semestre`, `local`
- Adicionar: `local_descricao` (required, string, max:500), `local_latitude` (nullable, numeric), `local_longitude` (nullable, numeric)
- Adicionar regra única: título não pode duplicar com evento AGENDADO existente (`Rule::unique('eventos')->where('status', 'AGENDADO')->ignore($id)`)

### 7. FinalizarRequest

```php
rules() → [
    'presencas'             => 'required|array',
    'presencas.*.user_id'   => 'required|integer|exists:users,id',
    'presencas.*.status'    => 'required|in:PRESENTE,AUSENTE',
]
```

### 8. Frontend — Index.tsx

Alterações na listagem geral:
- Ícone `Eye` (lucide-react) → link para `Show`; visível para todos
- Ícone de inscrição → visível apenas se `!evento.inscrito` (prop booleana passada pelo backend)
- Ícone `Pencil` → visível apenas se `evento.pode_editar` (prop booleana passada pelo backend)
- Ícones `CheckSquare` (finalizar) e `XCircle` (cancelar) → visíveis apenas se `evento.pode_editar`
- Filtros: select de tipo, date picker de data
- Ordenação: select por `data_inicio`, `data_fim`

O backend passa no array de cada evento: `inscrito: bool`, `pode_editar: bool`, calculados na query/service.

### 9. Frontend — Show.tsx

- Exibe todos os campos do evento
- Seção de localização: `local_descricao` + botões "Abrir no Google Maps" e "Abrir no Waze" (deep links)
- Botão "Inscrever-se": exibido apenas se `!inscrito && status === AGENDADO && !passou_inicio`
- Botão "Cancelar inscrição": exibido apenas se `inscrito && status === AGENDADO`
- Nenhum botão de ação se evento CANCELADO ou FINALIZADO

### 10. Frontend — Finalizar.tsx

- Lista de inscritos com toggle PRESENTE/AUSENTE por participante
- Botão "Confirmar finalização"
- Submit via `router.post(route('eventos.finalizar', evento.id), { presencas })`

### 11. Frontend — MeusResponsaveis.tsx e MeusInscritos.tsx

Ambas seguem o mesmo layout de tabela do `Index.tsx` existente com `PainelLayout`:
- **MeusResponsaveis**: mostra todos os eventos criados/com responsabilidade; ícones de editar, finalizar, cancelar sempre visíveis
- **MeusInscritos**: mostra eventos AGENDADOS com inscrição ativa; ícone de cancelar inscrição; sem ícone de inscrever-se

### 12. Frontend — Dashboard.tsx

- Tabela: Nome do voluntário | Eventos participados (total) | (expansível: por tipo)
- Filtros: select de semestre, input de nome (busca em tempo real no frontend sobre os dados carregados)
- Dados carregados via `GET /eventos/dashboard` com filtros de semestre

## Complexity Tracking

Sem violações de constituição. Nenhuma nova camada arquitetural introduzida — toda implementação reutiliza o padrão Controller → Service → Queries existente. A maior novidade é a `EventoPolicy`, que é idiomática do Laravel e não adiciona complexidade estrutural.
>>>>>>> 002-eventos-aprimoramentos
