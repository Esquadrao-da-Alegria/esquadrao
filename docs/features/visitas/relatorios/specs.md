# Relatórios de visita — especificação para desenvolvedores

Documento de referência do submódulo **relatórios** vinculados a uma visita. Complementa [`docs/features/visitas/specs.md`](../specs.md).

Um relatório atrasado conserva `fora_do_prazo = true`. Mediante justificativa, um administrador pode aceitá-lo apenas para contabilização pelo fluxo descrito em [`ajustes-contabilizacao/specs.md`](../ajustes-contabilizacao/specs.md).

---

## Vocabulário

| Termo | Significado |
|-------|-------------|
| **VisitaRelatorio** | Registro na tabela `visitas_relatorios` — relato enviado por um usuário sobre uma visita. |
| **Autor** | Usuário que criou o relatório (`autor_id` → `users.id`). |
| **Prazo 48h** | Recomendação de envio até 48h após `visita.fim_em`. Aviso visual; **não bloqueia** o envio. |
| **Fora do prazo** | `fora_do_prazo = true` quando `enviado_em > visita.fim_em + 48 horas`. Calculado na criação; imutável na edição. |

---

## Regras de negócio

1. **Quem cria** — Voluntários que participaram da visita (ou o líder da visita) e usuários com permissão `podeEditarVisita` (administradores, diretores e coordenadores autorizados).
2. **Quem vê** — Qualquer usuário autenticado (listagem, detalhe e PDF).
3. **Quem edita** — Autor do relatório **ou** quem passa em `podeEditarVisita` (mesma regra da visita).
4. **Visita cancelada** — Pode listar, ver detalhe e baixar PDF. **Não** pode criar nem editar relatório.
5. **Múltiplos relatórios** — Sem limite por visita (mesmo autor, mesmo tipo).
6. **Status da visita** — A criação de relatório altera automaticamente o status de visitas agendadas para `realizada`.
7. **Campos imutáveis na edição** — `visita_id`, `autor_id`, `enviado_em`, `fora_do_prazo` removidos do payload no service.
8. **Campos obrigatórios** — `tipo_relatorio` e `resumo` (validação `max:5000` nos textos).
9. **Contexto da visita** — Hospital, datas, líder, ala cadastrada na visita e participantes são **read-only** na UI e no PDF (não persistidos no relatório).
10. **Ala do relatório** — Campo opcional `ala_unidade_id` (FK → `alas_hospitais`), independente da ala da visita; exibido na seção do relatório (UI e PDF).
11. **Sem destroy** — Não há rota nem query `destroy` nesta v1.
12. **Histórico preservado** — Relatórios impedem exclusão física da visita (`visita_id` com `restrictOnDelete`). Visita com relatório(s) não pode ser removida do banco; o histórico não desaparece em cascata.
13. **Notificação por e-mail** — Ao cadastrar um novo relatório, uma notificação por e-mail (`RelatorioVisitaNotification`) é enviada automaticamente para todos os integrantes da visita (com status diferente de cancelado).

---

## Modelo de dados

### Tabela `visitas_relatorios`

Migration: `2026_07_19_135423_create_visitas_relatorios_table.php`

| Coluna | Descrição |
|--------|-----------|
| `id` | PK |
| `visita_id` | FK → `visitas` (restrictOnDelete) |
| `autor_id` | FK → `users` (restrictOnDelete) |
| `tipo_relatorio` | `palhaco` \| `paisana` \| `geral` |
| `ala_unidade_id` | FK → `alas_hospitais.id` (nullable, nullOnDelete) |
| `resumo` | text, obrigatório |
| `feedback` | text nullable |
| `quartos_visitados` | unsigned int nullable |
| `pessoas_impactadas` | unsigned int nullable |
| `observacao_visitantes_externos` | text nullable |
| `observacoes_gerais` | text nullable |
| `enviado_em` | timestamp; `now()` na criação |
| `fora_do_prazo` | boolean; calculado na criação |
| `created_at` / `updated_at` | timestamps |

### Relacionamentos

- `VisitaRelatorio::visita()` → BelongsTo `Visita`
- `VisitaRelatorio::alaUnidade()` → BelongsTo `Ala` (`ala_unidade_id`)
- `VisitaRelatorio::autor()` → BelongsTo `User` (`autor_id`)
- `Visita::relatorios()` → HasMany `VisitaRelatorio`

### Enum

`App\Enums\TipoRelatorio`: `Palhaco`, `Paisana`, `Geral`.

---

## Backend

### Camadas

| Camada | Caminho |
|--------|---------|
| Controller | `App\Http\Controllers\Web\Visita\Relatorio\VisitaRelatorioController` |
| Service | `App\Services\Visita\Relatorio\Service` |
| Queries | `App\Queries\Visita\Relatorio\Queries` |
| Requests | `App\Http\Requests\Web\Visita\Relatorio\StoreRequest`, `UpdateRequest` |

Envelope padrão: `['sucesso' => bool, 'dados' => ..., 'erros' => []]`.

### Regras no Service

- `calcularForaDoPrazo(visita, enviadoEm)` → `enviadoEm > visita.fim_em + 48h`
- A regra de 48 horas é centralizada em `App\Services\Visita\Relatorio\Prazo\Service` e reutilizada pelo dashboard de participação.
- `podeEditarRelatorio(user, visita, relatorio)` → falso se visita cancelada; verdadeiro se autor; senão `podeEditarVisita`
- `store` → bloqueia visita cancelada; define `autor_id`, `enviado_em`, `fora_do_prazo`
- `pdf` → valida pertencimento; gera download síncrono via Spatie Laravel PDF

### Logs de erro

- Queries retornam o envelope; o Service grava em `$retornoDatabase`, valida `sucesso` e, se falso, chama `logarErro` antes de devolver o envelope.
- Exceptions no Service também passam por `logarErro`.
- Payload do log via `payloadLogErro`: somente `visita_id`, `relatorio_id` e `autor_id` (quando existirem) — **nunca** resumo, feedback nem observações.
- Detalhe técnico do erro: mensagem da query (`erros[0]`) ou `formatarMensagemErro($th)`.

### Route model binding

Grupo `visitas.{visita}.relatorios.*` usa `scopeBindings()` — `{relatorio}` deve pertencer à `{visita}`.

---

## Rotas Web

Grupo aninhado em `visitas.` (middleware `auth` + `verified`):

| Método | URI | Action | Nome |
|--------|-----|--------|------|
| GET | `/visitas/{visita}/relatorios` | `VisitaRelatorioController@index` | `visitas.relatorios.index` |
| GET | `/visitas/{visita}/relatorios/create` | `VisitaRelatorioController@create` | `visitas.relatorios.create` |
| POST | `/visitas/{visita}/relatorios` | `VisitaRelatorioController@store` | `visitas.relatorios.store` |
| GET | `/visitas/{visita}/relatorios/{relatorio}` | `VisitaRelatorioController@show` | `visitas.relatorios.show` |
| GET | `/visitas/{visita}/relatorios/{relatorio}/edit` | `VisitaRelatorioController@edit` | `visitas.relatorios.edit` |
| PUT | `/visitas/{visita}/relatorios/{relatorio}` | `VisitaRelatorioController@update` | `visitas.relatorios.update` |
| GET | `/visitas/{visita}/relatorios/{relatorio}/pdf` | `VisitaRelatorioController@pdf` | `visitas.relatorios.pdf` |

Pós-criar e pós-editar: redirect para `visitas.relatorios.show`.

---

## PDF (v1)

- **Pacotes:** `spatie/laravel-pdf`, `spatie/browsershot`
- **Geração:** síncrona na request; view `resources/views/pdf/visita/relatorio.blade.php`
- **Persistência:** nenhuma — stream/download direto (sem Storage, sem fila)
- **Chromium (Sail):** Google Chrome via `google-chrome-stable` no Dockerfile (`docker/8.4/Dockerfile`); path em `Service::pdf`: `/usr/bin/google-chrome-stable` + `noSandbox()`
- **Node (Browsershot):** dependência `puppeteer` no `package.json` (API do Browsershot); `.npmrc` com `puppeteer_skip_download=true` (Chrome do sistema, não o bundle do Puppeteer)
- **Produção:** Chromium instalado no servidor + mesmo path no Browsershot
- **Testes:** sempre `Pdf::fake()` — não depender de Chromium no CI

---

## Frontend

### Pages Inertia

| Arquivo | Responsabilidade |
|---------|------------------|
| `Pages/Visita/Relatorio/Index.tsx` | Listagem por visita |
| `Pages/Visita/Relatorio/Create.tsx` | Formulário de criação |
| `Pages/Visita/Relatorio/Edit.tsx` | Formulário de edição |
| `Pages/Visita/Relatorio/Show.tsx` | Detalhe + PDF + editar |
| `components/Painel/Visita/Relatorio/Formulario/Form.tsx` | Campos compartilhados Create/Edit |
| `components/Painel/Visita/Relatorio/Contexto/Show.tsx` | Contexto read-only da visita |

**Sem** `resources/js/Queries/Visita/Relatorio` nem `Services/Visita/Relatorio` — formulários usam Inertia `useForm`; PDF via link/`window.location.href`.

### Entrada na UI

- Modal de detalhes do calendário (`Calendario/Detalhes/Modal/Show.tsx`): **Ver relatórios** e **Criar relatório** (oculto se visita cancelada)
- Tela `Pages/Visita/Edit.tsx`: mesmas ações na seção Relatórios

### Tipos TS

`resources/js/types/index.d.ts` — `TipoRelatorio`, `VisitaRelatorio`.

Helpers em `lib/visita.ts`: `labelTipoRelatorio`, `podeEditarRelatorio`, `formatarDataHora`, `TIPOS_RELATORIO`.

### Botão Editar (client)

`visita.status !== 'cancelada' && (relatorio.autor_id === auth.user.id || podeEditarVisita(auth.user, visita))`

### Download PDF

Botão desabilitado com estado local `baixando` enquanto a navegação para a rota `pdf` está em andamento.

---

## Testes

| Arquivo | Cobertura |
|---------|-----------|
| `tests/Feature/Visita/Relatorio/RelatorioStoreTest.php` | Store, prazo 48h, cancelada, auth |
| `tests/Feature/Visita/Relatorio/RelatorioUpdateTest.php` | Permissões de edição |
| `tests/Feature/Visita/Relatorio/RelatorioIndexShowTest.php` | Index/show, cancelada |
| `tests/Feature/Visita/Relatorio/RelatorioPdfTest.php` | PDF com `Pdf::fake()` |
| `tests/Feature/Visita/Relatorio/RelatorioExclusaoVisitaTest.php` | Visita com relatório não pode ser excluída fisicamente |

```bash
vendor/bin/sail artisan test --compact --filter=Relatorio
```

---

## Fora de escopo (v1)

- Fila, Job ou polling para PDF
- Tabela `visitas_relatorios_pdf` ou persistência de arquivo PDF
- `destroy` de relatório
- Alteração de status da visita ao criar/editar relatório
- Queries/Services JS dedicados no frontend
- Menu global de relatórios (entrada só pela visita)
- Puppeteer/Node no container (apenas Chromium via apt)

---

## Resumo

| Pergunta | Resposta curta |
|----------|----------------|
| Onde ficam os relatórios? | Tabela `visitas_relatorios`, model `VisitaRelatorio`. |
| Quem pode criar? | Qualquer autenticado, exceto visita cancelada. |
| Quem pode editar? | Autor ou quem `podeEditarVisita`. |
| Visita cancelada? | Ver e PDF sim; criar/editar não. |
| Prazo 48h bloqueia? | Não — só aviso e flag `fora_do_prazo`. |
| PDF onde? | Gerado na request, view Blade, download síncrono. |
