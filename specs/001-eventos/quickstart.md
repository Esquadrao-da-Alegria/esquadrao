# Quickstart for Sistema de Eventos

## 1. Confirmar branch

- Trabalhar na branch `dev-mauro` ou criar uma branch de feature a partir de `dev-mauro`.

## 2. Criar migration e modelo de eventos

- Criar migration `create_eventos_table` com campos do modelo de evento.
- Criar migration `create_evento_participantes_table` para inscrições.
- Criar `App\Models\Evento` e `App\Models\EventoParticipante`.
- Usar `SoftDeletes` em `Evento`.

## 3. Criar enums de domínio

- Criar `App\Enums\TipoEvento` com `OFICINA`, `REUNIAO`.
- Criar `App\Enums\StatusEvento` com `AGENDADO`, `FINALIZADO`, `CANCELADO`, `TRANSFERIDO`.
- Criar `App\Enums\StatusInscricao` com `INSCRITO`, `PRESENTE`, `AUSENTE`, `CANCELADO`.

## 4. Criar requests e controller

- Criar `App\Http\Requests\Web\Evento\StoreRequest`
- Criar `App\Http\Requests\Web\Evento\UpdateRequest`
- Criar `App\Http\Controllers\Web\EventoController`
- Manter `edit(Evento $evento)` recebendo apenas o modelo

## 5. Criar services e queries

- Criar `App\Services\Evento\Service`
- Criar `App\Services\Evento\Form\Service`
- Criar `App\Queries\Evento\Queries`
- Reutilizar o padrão existente de `retornar_lista` e filtros somente no backend

## 6. Registrar rotas

- Adicionar rotas em `routes/web.php` sob `auth` e middleware apropriado.
- Incluir pelo menos:
  - `GET /eventos`
  - `GET /eventos/create`
  - `POST /eventos`
  - `GET /eventos/{evento}`
  - `GET /eventos/{evento}/edit`
  - `PATCH /eventos/{evento}`
  - `DELETE /eventos/{evento}`
  - ações adicionais de inscrição, cancelamento e presença conforme evolução

## 7. Criar páginas Inertia

- Criar páginas em `resources/js/Pages/Evento/`:
  - `Index.tsx`
  - `Create.tsx`
  - `Edit.tsx`
  - `Show.tsx`
  - `Presencas.tsx`
  - `Dashboard.tsx`

## 8. Testes

- Escrever testes de feature para:
  - criação de oficina e reunião
  - bloqueio de criação por usuário não autorizado
  - listagem e filtros
  - inscrição e duplicidade
  - cancelamento de inscrição
  - lógica de presença e finalização

## 9. Executar migrações e testes

- `php artisan migrate`
- `composer test` ou `vendor/bin/phpunit`

## 10. Referência do plano

- Planejamento completo: `specs/001-eventos/plan.md`
