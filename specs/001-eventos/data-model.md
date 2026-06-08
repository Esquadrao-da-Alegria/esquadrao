# Data Model for Sistema de Eventos

## Evento

- **id**: integer, PK
- **tipo**: string (enum), valores: `OFICINA`, `REUNIAO`
- **titulo**: string, max 255
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
- **criado_por_id**: integer, FK → `users.id`
- **created_at**: timestamp
- **updated_at**: timestamp
- **deleted_at**: timestamp, soft delete

### Relacionamentos

- `cidade()` → `belongsTo(Cidade::class)`
- `criadoPor()` → `belongsTo(User::class, 'criado_por_id')`
- `eventoOrigem()` → `belongsTo(Evento::class, 'evento_origem_id')`
- `eventosTransferidos()` → `hasMany(Evento::class, 'evento_origem_id')`
- `participantes()` → `hasMany(EventoParticipante::class)`

## EventoParticipante

- **id**: integer, PK
- **evento_id**: integer, FK → `eventos.id`
- **user_id**: integer, FK → `users.id`
- **status**: string (enum), valores: `INSCRITO`, `PRESENTE`, `AUSENTE`, `CANCELADO`
- **presenca_confirmada_por_id**: integer, nullable, FK → `users.id`
- **presenca_confirmada_em**: timestamp, nullable
- **created_at**: timestamp
- **updated_at**: timestamp

### Relacionamentos

- `evento()` → `belongsTo(Evento::class)`
- `usuario()` → `belongsTo(User::class, 'user_id')`
- `confirmadoPor()` → `belongsTo(User::class, 'presenca_confirmada_por_id')`

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
