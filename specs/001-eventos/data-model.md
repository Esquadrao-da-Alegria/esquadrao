# Data Model for Sistema de Eventos

<<<<<<< HEAD
=======
**Atualizado**: 2026-06-10 — Removido evento_origem_id e TRANSFERIDO; adicionados campos de geolocalização e tabela evento_responsaveis.

---

>>>>>>> 002-eventos-aprimoramentos
## Evento

- **id**: integer, PK
- **tipo**: string (enum), valores: `OFICINA`, `REUNIAO`
- **titulo**: string, max 255
<<<<<<< HEAD
- **descricao**: string, max 255
- **data_inicio**: datetime
- **data_fim**: datetime
- **local**: string, max 255
- **cidade_id**: integer, FK → `cidades.id`
- **semestre**: string, max 255
- **status**: string (enum), valores: `AGENDADO`, `FINALIZADO`, `CANCELADO`, `TRANSFERIDO`
- **limite_vagas**: integer, nullable
- **feedback_habilitado**: boolean
- **evento_origem_id**: integer, nullable, FK → `eventos.id`
=======
- **descricao**: text
- **data_inicio**: datetime
- **data_fim**: datetime
- **local_descricao**: string, max 500 — endereço legível (ex: "Rua das Flores, 123, Centro, São Paulo")
- **local_latitude**: decimal(10,7), nullable — latitude geocodificada
- **local_longitude**: decimal(10,7), nullable — longitude geocodificada
- **cidade_id**: integer, FK → `cidades.id`
- **status**: string (enum), valores: `AGENDADO`, `FINALIZADO`, `CANCELADO`
- **limite_vagas**: integer, nullable
- **feedback_habilitado**: boolean, default false
>>>>>>> 002-eventos-aprimoramentos
- **criado_por_id**: integer, FK → `users.id`
- **created_at**: timestamp
- **updated_at**: timestamp
- **deleted_at**: timestamp, soft delete

<<<<<<< HEAD
=======
> **Removido**: `local` (string simples), `evento_origem_id`, `semestre`

>>>>>>> 002-eventos-aprimoramentos
### Relacionamentos

- `cidade()` → `belongsTo(Cidade::class)`
- `criadoPor()` → `belongsTo(User::class, 'criado_por_id')`
<<<<<<< HEAD
- `eventoOrigem()` → `belongsTo(Evento::class, 'evento_origem_id')`
- `eventosTransferidos()` → `hasMany(Evento::class, 'evento_origem_id')`
- `participantes()` → `hasMany(EventoParticipante::class)`
=======
- `participantes()` → `hasMany(EventoParticipante::class)`
- `responsaveis()` → `hasMany(EventoResponsavel::class)`

### Regras de validação

- `titulo` único quando `status = AGENDADO` (unique condicional no banco + validação no StoreRequest)
- `data_fim` deve ser posterior a `data_inicio`
- `cidade_id` deve existir em `cidades`
- `limite_vagas`, se informado, deve ser inteiro positivo
- `feedback_habilitado` deve ser booleano

### Estados do domínio

```
AGENDADO → FINALIZADO  (ação: finalizar — só criador/responsável)
AGENDADO → CANCELADO   (ação: cancelar  — só criador/responsável)
```

---
>>>>>>> 002-eventos-aprimoramentos

## EventoParticipante

- **id**: integer, PK
<<<<<<< HEAD
- **evento_id**: integer, FK → `eventos.id`
- **user_id**: integer, FK → `users.id`
=======
- **evento_id**: integer, FK → `eventos.id` (cascade delete)
- **user_id**: integer, FK → `users.id` (cascade delete)
>>>>>>> 002-eventos-aprimoramentos
- **status**: string (enum), valores: `INSCRITO`, `PRESENTE`, `AUSENTE`, `CANCELADO`
- **presenca_confirmada_por_id**: integer, nullable, FK → `users.id`
- **presenca_confirmada_em**: timestamp, nullable
- **created_at**: timestamp
- **updated_at**: timestamp

<<<<<<< HEAD
=======
> Unique constraint: `(evento_id, user_id)`

>>>>>>> 002-eventos-aprimoramentos
### Relacionamentos

- `evento()` → `belongsTo(Evento::class)`
- `usuario()` → `belongsTo(User::class, 'user_id')`
- `confirmadoPor()` → `belongsTo(User::class, 'presenca_confirmada_por_id')`

<<<<<<< HEAD
## Regras de validação de dados

- `data_fim` deve ser igual ou posterior a `data_inicio`
- `cidade_id` deve existir em `cidades`
- `criado_por_id` deve existir em `users`
- `evento_origem_id`, se presente, deve existir em `eventos`
- `limite_vagas`, se informado, deve ser inteiro não negativo
- `feedback_habilitado` deve ser booleano

## Estados do domínio

- Evento começa como `AGENDADO`
- Evento pode transitar para `FINALIZADO`, `CANCELADO` ou `TRANSFERIDO`
- `EventoParticipante.status` inicia como `INSCRITO`
- Confirmar presença muda para `PRESENTE`
- Cancelar inscrição muda para `CANCELADO`

## Observações de implementação

- `evento_origem_id` permite histórico de transferências sem duplicar lógica de evento principal.
- `limite_vagas` nulo significa vagas ilimitadas.
- Soft delete em eventos preserva histórico e permite auditoria de cancelamentos/transferências.
=======
### Estados do domínio

```
INSCRITO → PRESENTE   (ação: confirmar presença — responsável ao finalizar evento)
INSCRITO → AUSENTE    (ação: marcar ausência   — responsável ao finalizar evento)
INSCRITO → CANCELADO  (ação: cancelar inscrição — pelo próprio voluntário antes do início)
```

---

## EventoResponsavel *(tabela nova)*

- **id**: integer, PK
- **evento_id**: integer, FK → `eventos.id` (cascade delete)
- **voluntario_id**: integer, FK → `users.id` (cascade delete)
- **tipo_responsavel**: string, nullable — *reservado para uso futuro; não utilizado enquanto cargos de voluntário não estiverem definidos*
- **created_at**: timestamp
- **updated_at**: timestamp

> Unique constraint: `(evento_id, voluntario_id)`

### Relacionamentos

- `evento()` → `belongsTo(Evento::class)`
- `voluntario()` → `belongsTo(User::class, 'voluntario_id')`

---

## Enums afetados

| Enum | Antes | Depois |
|---|---|---|
| `StatusEvento` | AGENDADO, FINALIZADO, CANCELADO, TRANSFERIDO | AGENDADO, FINALIZADO, CANCELADO |
| `TipoEvento` | OFICINA, REUNIAO | sem alteração |
| `StatusInscricao` | INSCRITO, PRESENTE, AUSENTE, CANCELADO | sem alteração |

---

## Migrations necessárias

| # | Arquivo | Operação |
|---|---|---|
| 1 | `drop_evento_origem_from_eventos_table` | Remove `evento_origem_id` |
| 2 | `update_eventos_geolocation_fields` | Remove `local` (string), adiciona `local_descricao`, `local_latitude`, `local_longitude` |
| 3 | `create_evento_responsaveis_table` | Cria tabela `evento_responsaveis` |

> **Nota**: `semestre` já foi removido em migration anterior (2026-06-08). Confirmar antes de rodar.
>>>>>>> 002-eventos-aprimoramentos
