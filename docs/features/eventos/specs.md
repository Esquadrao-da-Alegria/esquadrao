# Eventos — especificação da feature

Documento de referência para o cadastro e a manutenção das atividades do módulo de eventos.

---

## Tipos de evento

O campo `eventos.tipo` aceita exclusivamente:

| Valor | Exibição | Participa das metas semestrais | Confirmação por QR Code |
|---|---|---|---|
| `oficina` | Oficina | Sim, na meta específica de oficinas | Sim |
| `reuniao` | Reunião | Sim, na meta específica de reuniões | Sim |
| `evento` | Evento | Não | Não |

O tipo `evento` representa outras atividades institucionais, como festivais e encontros especiais. Ele não deve ser confundido com o tipo `acao_especial` do módulo de visitas.

O frontend e o backend devem manter a mesma lista de tipos. Valores diferentes dos três tipos documentados precisam ser rejeitados no backend durante a criação e a edição.

---

## Cadastro e edição

- Somente administradores podem acessar as rotas de criação e edição.
- O cadastro exige título, tipo, cidade, data e horário de início e data final posterior ao início.
- O prazo de inscrição, quando informado, deve ser anterior ou igual ao início.
- O limite de participantes é opcional e, quando informado, deve ser de pelo menos uma pessoa.
- Apenas eventos com status `agendado` podem ser editados.
- O limite não pode ser reduzido para um valor inferior ao total de participantes ativos.

Os três tipos seguem o mesmo fluxo geral de agendamento, listagem, inscrição, cancelamento e finalização. Regras exclusivas de presença por QR Code permanecem descritas em `docs/features/eventos/presenca-qr-code/specs.md`.

---

## Arquivos centrais

- `app/Models/Evento.php`
- `app/Http/Controllers/Web/EventoController.php`
- `app/Http/Requests/Web/Evento/StoreRequest.php`
- `app/Http/Requests/Web/Evento/UpdateRequest.php`
- `resources/js/lib/evento.ts`
- `resources/js/components/Painel/Evento/Formulario/Form.tsx`
- `resources/js/Pages/Evento/Create.tsx`
- `resources/js/Pages/Evento/Edit.tsx`
- `tests/Feature/EventoTest.php`

---

## Validação de regressão

Ao alterar os tipos de evento, validar no mínimo:

1. criação e edição dos três tipos permitidos;
2. rejeição de um tipo desconhecido;
3. exclusão de `evento` das metas de reuniões e oficinas;
4. bloqueio do QR Code para `evento`;
5. tipagem e build do frontend.
