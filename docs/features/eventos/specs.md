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

## Participação semestral

O dashboard gerencial **Participação semestral** apresenta uma relação de integrantes com os totais separados de presenças em reuniões e oficinas no semestre selecionado. É uma consulta informativa: não altera cadastro, não aplica advertência e não bloqueia voluntários.

- Rota: `dashboards.participacao-semestral` (`/dashboards/participacao-semestral`).
- Acesso: administradores e coordenadores gerais têm escopo global; coordenadores locais têm acesso apenas à própria cidade-base; diretores e voluntários sem esses cargos recebem HTTP 403. Coordenador local sem cidade-base não acessa a consulta.
- Por padrão, a tela abre no semestre atual e mostra integrantes ativos. A situação é o status atual do cadastro, inclusive ao consultar períodos anteriores; não há histórico de status nesta modelagem.
- Filtros: ano, semestre, nome ou e-mail, situação atual e cidade para escopo global. Perfis globais com cidade-base iniciam nela e podem escolher outra cidade ou todas; contas globais sem cidade-base iniciam em todas.
- A relação mantém integrantes sem presença, com `0` em reuniões e oficinas.
- Uma presença é contabilizada somente quando `evento_participantes.presenca = presente`, o evento é `finalizado` e seu tipo é `reuniao` ou `oficina`. Inscrição, ausência, presença pendente, evento cancelado e o tipo `evento` não entram.
- Nesta versão, a presença entra somente quando o evento pertence à cidade-base do integrante. Participações em intercâmbio permanecem registradas no histórico, mas não elevam o consolidado, para manter coerência com o indicador individual atual. Caso a regra mude, alterar as duas consultas de `Dashboard\\Evento\\ParticipacaoSemestral\\Queries` e seus testes.
- Os detalhes expansíveis mostram os eventos que compõem os totais da página atual. A listagem e os detalhes usam consultas agregadas em lote, sem consulta por integrante.

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

## Agenda em lista

A página de eventos oferece as visualizações **Calendário** e **Lista**, com a preferência armazenada no navegador. A lista respeita os filtros já aplicados e apresenta data, local, cidade, responsável, vagas e participantes ativos; as cores comunicam visualmente a situação. O botão de detalhes reutiliza o modal existente. A consulta carrega participantes ativos junto dos eventos para evitar consultas adicionais por linha.

---

## Validação de regressão

Ao alterar os tipos de evento, validar no mínimo:

1. criação e edição dos três tipos permitidos;
2. rejeição de um tipo desconhecido;
3. exclusão de `evento` das metas de reuniões e oficinas;
4. bloqueio do QR Code para `evento`;
5. tipagem e build do frontend.
