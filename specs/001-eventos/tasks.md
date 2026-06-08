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
