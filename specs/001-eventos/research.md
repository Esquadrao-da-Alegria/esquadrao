# Research for Sistema de Eventos

## Decision: backend filtering first

- Decision: eventos serão filtrados no backend antes de retornar para o frontend.
- Rationale: o projeto já usa serviços e queries para filtrar dados, e a listagem de eventos pode crescer. Enviar todos os eventos para o cliente e filtrar no frontend criaria sobrecarga de dados, pioraria performance e fugiria do padrão existente.
- Alternatives considered:
  - client-side filtering de todos os registros carregados de uma vez: simples, mas não escalável e não aproveita a API já existente.
  - backend filtering por rota de listagem com filtros opcionais: escolhido por ser consistente com o padrão atual e permitir paginação/filtros progressivos.

## Decision: usar enums PHP para tipo e status

- Decision: `TipoEvento` e `StatusEvento` serão representados como enums PHP e persistidos como strings no banco.
- Rationale: o backend terá valores centralizados e tipados, reduzindo risco de strings inválidas. O Laravel permite cast de enum para atributo, o que facilita conversão entre DB e modelo.
- Alternatives considered:
  - strings livres no banco: mais simples, mas menos seguro.
  - enums de banco SQL nativo: viável, mas aumenta acoplamento à plataforma e complica migrações em diferentes bancos.

## Decision: usar entidade `EventoParticipante`

- Decision: criar tabela/model `evento_participantes` em vez de uma pivot simples `evento_user`.
- Rationale: é necessário rastrear status da inscrição (`INSCRITO`, `PRESENTE`, `AUSENTE`, `CANCELADO`), data e usuário que confirmou presença, e regras de cancelamento. Uma tabela dedicada torna esses dados explícitos e testáveis.
- Alternatives considered:
  - pivot simples com `timestamps`: insuficiente para status e confirmação.

## Decision: reaproveitar `User` e `Cidade`

- Decision: usar as entidades existentes `User` e `Cidade` para responsáveis, criador, cidade do evento e validações de FK.
- Rationale: já há suporte à autenticação, relacionamento de cidade e políticas de acesso. Reusar estas entidades mantém consistência e evita duplicação de modelo.

## Decision: criar fluxo backend-first com Inertia

- Decision: manter Inertia TSX como camada de apresentação para os formulários e listagens de eventos.
- Rationale: o projeto atual usa Inertia + React/TSX. Isso evita introduzir uma nova stack e permite reutilizar layouts, componentes e navegação existentes.
