<<<<<<< HEAD
# Research for Sistema de Eventos
=======
# Research for Sistema de Eventos — Aprimoramentos

**Atualizado**: 2026-06-10

---

## Decision: geolocalização via Nominatim (OpenStreetMap) + deep links

- **Decision**: Usar Nominatim para geocodificação (endereço → lat/lon) no frontend. Armazenar `local_latitude`, `local_longitude` e `local_descricao` no banco. Exibir links de "Abrir no Google Maps" e "Abrir no Waze" usando deep links padrão — sem embed de mapa.
- **Rationale**: Nominatim é 100% gratuito e sem API key. O volume de criação de eventos da ONG (dezenas/mês) está bem dentro do limite de cortesia de 1 req/s. Deep links não exigem API key e funcionam em mobile e desktop. Sem embed = sem componente extra, sem custo.
- **Como usar no frontend**: `GET https://nominatim.openstreetmap.org/search?format=json&q={endereço}&limit=5` — retorna `lat`, `lon`, `display_name`. O formulário envia os três campos ao backend.
- **Deep links**:
  - Google Maps: `https://maps.google.com/?q={lat},{lon}`
  - Waze: `https://waze.com/ul?ll={lat},{lon}&navigate=yes`
- **Alternatives considered**: Google Maps Geocoding API (requer billing), Mapbox (requer API key) — ambos descartados por adicionar setup sem necessidade.

## Decision: date/time picker nativo HTML5

- **Decision**: Usar `<input type="datetime-local">` para seleção de data e hora. Sem biblioteca externa.
- **Rationale**: Projeto já usa inputs nativos estilizados com Tailwind. O atributo `min` do input permite validar `data_fim > data_inicio` no cliente sem JS extra. Consistente com o padrão existente.
- **Alternatives considered**: `react-datepicker` — descartado para não introduzir dependência sem necessidade.

## Decision: autorização por responsável via Policy Laravel

- **Decision**: Criar `EventoPolicy` com métodos `update`, `finalizar`, `cancelar`. A policy verifica `criado_por_id === Auth::id()` OR existência na tabela `evento_responsaveis`.
- **Rationale**: O projeto usa middleware de cargo para admin, mas permissões de evento são por dado (não por cargo). Laravel Policy é a forma idiomática e evita repetir lógica em Service e Controller.
- **Alternatives considered**: Verificação inline no Service — descartado por espalhar lógica.

## Decision: remoção de evento_origem_id e status TRANSFERIDO

- **Decision**: Nova migration remove `evento_origem_id` da tabela `eventos`. Enum `StatusEvento` perde valor `TRANSFERIDO`.
- **Rationale**: Decisão de produto — funcionalidade de transferência descartada. Manter seria dead code.
- **Atenção**: Verificar existência de registros com `status = TRANSFERIDO` antes de rodar em produção.

## Decision: rotas dedicadas "meus eventos" sem novo controller

- **Decision**: Adicionar `GET /eventos/meus-responsaveis` e `GET /eventos/meus-inscritos` como rotas nomeadas estáticas no `EventoController`, declaradas antes do `Route::resource`.
- **Rationale**: Reutiliza controller existente; queries são extensões naturais das queries já implementadas.
>>>>>>> 002-eventos-aprimoramentos

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
