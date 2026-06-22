# AGENTS.md

Convenções de código e arquitetura do projeto **Esquadrão**.  
Regras de **negócio** ficam em `docs/features/<feature>/specs.md` — não duplicar aqui.

---

## Pilares

**SRP em tudo** — cada arquivo, função e serviço tem um único motivo de mudar.

**Queries + Services** — separação clara entre acesso a dados e lógica de caso de uso. Nomenclatura REST nos métodos (`index`, `show`, `store`, `update`, `destroy`) replicável em qualquer stack.

**Retorno padronizado** — envelope consistente em todos os fluxos:

```json
{ "sucesso": true,  "dados": {}, "erros": [] }
{ "sucesso": false, "dados": {}, "erros": ["mensagem"] }
```

**Docs como contexto para IA** — specs em `docs/features/<feature>/specs.md` reduzem ambiguidade e tornam o código gerado mais previsível.

**SDD (Specification-Driven Development)** — define regras e fronteiras antes de implementar.

---

## Por que padronizar em 2026?

Com IA gerando e refatorando código o tempo todo, uma base bem definida importa mais do que nunca:

- Prompts produzem resultados consistentes quando a arquitetura é previsível
- User Rules globais no Cursor fixam convenções em todos os projetos automaticamente
- Specs em `/docs` funcionam como memória de contexto para agentes

---

## Referência rápida — nomenclatura REST

Services e Queries devem seguir essa nomenclatura ao máximo. Desvie apenas quando a operação for genuinamente específica e não se encaixar em nenhum dos cinco verbos — e mesmo assim, prefira compor (`indexAtivos`, `showComRelacoes`) antes de inventar um nome novo.

| Método | HTTP | Descrição |
|--------|------|-----------|
| `index` | GET /recursos | Lista |
| `show` | GET /recursos/{id} | Um registro |
| `store` | POST /recursos | Cria |
| `update` | PUT/PATCH /recursos/{id} | Atualiza |
| `destroy` | DELETE /recursos/{id} | Remove |

Se você está criando um método chamado `buscar`, `listar`, `salvar` ou `deletar` — quase certamente é um dos cinco acima com outro nome.

### Princípios que guiam tudo aqui

- **SRP** → um motivo de mudar por unidade
- **OCP** → aberto para extensão, fechado para modificação
- **DIP** → dependa de abstrações, não implementações

---

## Fluxo de trabalho

- Implementar **sempre** na branch `dev-arthur` (local).
- **Não** criar branches `feature/*` nem worktrees para entregas.
- Antes de implementar: ler `docs/features/<feature>/specs.md` da área afetada.
- Após implementação validada: atualizar o `specs.md` correspondente.
- Planos temporários **não** entram no repositório.

---

## Princípios

- KISS — solução simples e previsível.
- Alterações **mínimas e localizadas**; não refatorar fora do escopo.
- Não criar abstrações, arquivos ou camadas sem necessidade real.
- Reutilizar código existente antes de criar componentes novos.
- Código idiomático, tipado e legível.
- Comentários só para lógica não óbvia.
- Testes só quando agregam cobertura de comportamento real (não assertar o trivial).
- Em dúvida sobre requisito: **perguntar** antes de implementar.

---

## Estrutura de diretórios

### PHP

```
app/
  Http/Controllers/Web/[Modulo]/[Contexto]/[Nome]Controller.php
  Http/Requests/Web/[Modulo]/...
  Services/[Entidade]/[Contexto]/Service.php
  Queries/[Entidade]/[Contexto]/Queries.php
  Models/
  Enums/
routes/web.php
tests/Feature/[Modulo]/
tests/Unit/[Modulo]/
```

### Frontend

```
resources/js/
  Pages/[Modulo]/
  components/
  Services/[Entidade]/[Contexto]/Service.tsx
  Queries/[Entidade]/[Contexto]/Queries.tsx
  lib/
  utils/
  types/index.d.ts
```

---

## Nomenclatura

### Geral

- Funções, métodos e classes em **português**, salvo convenção do framework (ex.: `handle`, lifecycle React).
- Diretórios e namespaces **sem verbos** (`Services/Carro/Acao`, não `Services/Carro/Andar`).

### Arquivos por tipo

| Tipo | Nome do arquivo | Contexto |
|------|-----------------|----------|
| Service | `Service.php` / `Service.tsx` | No diretório/namespaces |
| Queries | `Queries.php` / `Queries.tsx` | No diretório/namespaces |
| Controller | `[Entidade]Controller.php` | Ex.: `VisitaParticipanteController.php` |

❌ `FinalizacaoService.php` → ✅ `Services/Pedido/Finalizacao/Service.php`  
❌ `CancelamentoQueries.tsx` → ✅ `Queries/Pedido/Cancelamento/Queries.tsx`

### PHP

- Classes, controllers, models, enums: `PascalCase`
- Métodos e variáveis: `camelCase`
- Colunas e arquivos PHP: `snake_case`
- Constantes: `UPPER_SNAKE_CASE`
- Enum keys: `TitleCase`

### JavaScript / TypeScript

- Componentes React e classes: `PascalCase` (arquivo `.tsx` igual ao componente)
- Variáveis e funções: `camelCase`
- Constantes: `UPPER_SNAKE_CASE`
- Arquivos não-componente: `kebab-case` ou `snake_case` conforme pasta existente

---

## Retorno padronizado

Todo **Service** e **Controller** (JSON) retorna o envelope dos Pilares.

Sucesso:

```php
return ['sucesso' => true, 'dados' => [...], 'erros' => []];
```

```json
{ "sucesso": true, "dados": {}, "erros": [] }
```

Erro:

```php
return ['sucesso' => false, 'dados' => [], 'erros' => ['mensagem']];
```

```json
{ "sucesso": false, "dados": {}, "erros": ["mensagem"] }
```

Frontend (Queries, Services, respostas esperadas): mesmo envelope em JS/TS.

---

## PHP / Laravel

### Arquitetura

```
Controller → Service → Queries → Model
```

- **Models** — Eloquent, casts, relacionamentos; sem lógica de negócio.
- **Queries** — SQL/Eloquent simples; sem regra de negócio.
- **Services** — regras, orquestração, validações, transações.
- **Controllers** — delegação; Inertia ou JSON; sem regra de negócio.
- **Form Requests** — validação de input HTTP quando existir UI de formulário.

### Controllers

- Sem lógica de negócio e sem mapeamento manual de mensagens de erro.
- Injetar Service via construtor (`private Service $service`).
- Endpoints JSON: traduzir `sucesso` em HTTP — `true` → `200`, `false` → `422`.
- Endpoints Inertia: montar `$dadosView` e `Inertia::render(...)`.
- Controllers Web ficam em `App\Http\Controllers\Web\`.

Exemplo JSON:

```php
public function store(Request $request, Visita $visita): JsonResponse
{
    $retorno = $this->service->store($visita, $request->all());
    $status  = $retorno['sucesso'] ? 200 : 422;

    return response()->json($retorno, $status);
}
```

### Services

- Toda operação pública em `try/catch`.
- Erros: `logarErro()` + `formatarMensagemErro($th)` (helper global em `app/helpers.php`).
- `update` retorna model com `$model->fresh()` quando aplicável.
- Lógica específica de domínio (filtros compostos, limites, duplicatas) fica no **Service**, não na Query.
- Service monta `$filtros` e interpreta retorno de `Queries::index()`.

```php
public function store(array $dados): array
{
    try {
        // regras...
        return ['sucesso' => true, 'dados' => ['model' => $model], 'erros' => []];
    } catch (\Throwable $th) {
        $this->logarErro($dados, 'criar', formatarMensagemErro($th));
        return ['sucesso' => false, 'dados' => [], 'erros' => [formatarMensagemErro($th)]];
    }
}

private function logarErro(array $dados, string $acao, string $mensagemErro): void
{
    Log::error("Erro ao {$acao} {entidade}!", [
        'sucesso' => false,
        'dados'   => $dados,
        'erros'   => ["Erro ao {$acao} {entidade}: {$mensagemErro}"],
    ]);
}
```

### Queries (`app/Queries/`)

**Somente** os métodos padrão:

| Método | Usa `::query()`? | Responsabilidade |
|--------|------------------|------------------|
| `index($filtros)` | Sim | Pesquisa via `aplicarFiltros` |
| `show($id)` | Sim | Busca por ID |
| `store($dados)` | Não — `Model::create()` | Insere |
| `update($id, $dados)` | Não — `findOrFail` + `update()` | Atualiza |
| `destroy($id)` | Não — `findOrFail` + `delete()` | Remove |

- **Proibido:** métodos de domínio na Query (ex.: `contarAtivos`, `existeAtivo`).
- `aplicarFiltros` traduz chaves genéricas em `where` — sem regra de negócio.
- `index` suporta `retornar_lista` nos filtros (`true` → `get()`, default → `first()`).
- Retorno sempre no formato padronizado com `formatarMensagemErro()` no catch.

### Models

- Serialização via Eloquent; enums backed string em `app/Enums/`.
- **Proibido** DTO — serialização JSON nos Models ou props Inertia tipadas no front.

### Estilo PHP

- Chaves em todos os control structures.
- Constructor property promotion (PHP 8).
- Return types e type hints explícitos em métodos públicos.
- PHPDoc com array shapes quando útil.

### Imports PHP

Agrupar por categoria, **nunca misturar**:

```php
// LIBS EXTERNAS
// QUERIES
// SERVICES
// REPOSITORIES
// UTILS
// MODELS
```

Remover imports não utilizados.

### Comandos

```bash
vendor/bin/sail artisan make:* --no-interaction
vendor/bin/sail artisan test --compact --filter=NomeTest
vendor/bin/sail bin pint --dirty --format agent   # após alterar PHP
```

---

## Rotas (`routes/web.php`)

- Rotas nomeadas com `route()` — **sempre** usar `->name()`.
- Agrupar rotas relacionadas com `prefix()` + `name()`:

```php
Route::prefix('visitas')->name('visitas.')->group(function () {
    Route::get('/', [VisitaController::class, 'index'])->name('index');
    Route::post('{visita}/participantes', [VisitaParticipanteController::class, 'store'])
        ->name('participantes.store');
});
```

- Middleware comum: `auth`, `verified` para área autenticada; `administrador` para admin.
- Controllers JSON auxiliares: prefixo `json.` (ex.: `json.cidades.index`).
- `use` statements no topo do arquivo, junto aos demais controllers.

---

## JavaScript / React / TypeScript

### Arquitetura

```
Page → Service → Queries
```

- **Pages** — UI; chamam Services; exibem erros via helpers de modal/toast.
- **Services** — orquestram Queries, toasts, `router` Inertia.
- **Queries** — único lugar com `fetch`.
- **Sem** lógica de negócio nos componentes.
- **Sem** chamadas HTTP nos componentes.
- Preferência: **TypeScript** (`.tsx` / `.ts`).

### Queries frontend

| Método | Assinatura |
|--------|------------|
| `store` | `(dados)` |
| `update` | `(id, dados)` |

- Classe exportada `Queries` com métodos `static async`.
- URLs via **Wayfinder** (`@/routes/...`), geradas com `vendor/bin/sail npm run build`.
- Headers obrigatórios: `Accept`, `Content-Type`, `X-XSRF-TOKEN` (via `obterCsrfToken()` em `@/utils/form`).
- `catch` retorna `{ sucesso: false, dados: [], erros: ['...'] }`.

```tsx
static async store(dados: DadosStore): Promise<RetornoPadrao> {
    try {
        const url = store({ visita: dados.visita_id }).url
        const retorno = await fetch(url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': obterCsrfToken(),
            },
            body: JSON.stringify({ ... }),
        })
        return await retorno.json()
    } catch (error) {
        console.error(error)
        return { sucesso: false, dados: [], erros: ['Erro ao ...!'] }
    }
}
```

**Proibido:** `$.ajax`, `axios`, `XMLHttpRequest`.

### Services frontend

- Montam `dados` e chamam `Queries`.
- Feedback: `toastSucesso`, `toastErro`, `toastInfo` (`@/lib/utils/toast`).
- Reload Inertia após mutação quando necessário: `router.reload()`.

### Componentes React

- Um componente por arquivo; `function` ou arrow em `PascalCase`.
- Hooks customizados: `use[Nome]` em arquivos separados.
- Widgets estáticos: `const` quando possível.
- Listas longas: `ListView.builder` / virtualização equivalente (`ListView.builder`, paginação).
- Estado local: `useState`; efeitos: `useEffect`.

### Tipos

- Tipos de domínio compartilhados: `resources/js/types/index.d.ts`.
- Props Inertia compartilhadas: `SharedData` (`auth`, flash messages, etc.).

### Vanilla JS (quando existir)

- `$(function(){ ... })` como entry point.
- Função `inicializar()` no fim do arquivo.
- Handlers no fim do arquivo.

---

## Formatação e diffs

- Diff mínimo — mudar **só** o necessário para a tarefa.
- **Não** rodar Pint, Prettier ou format-on-save só para reformatar arquivos tocados.
- **Preservar** estilo existente no arquivo:
  - Alinhamento de `=` em atribuições consecutivas.
  - Alinhamento de `=>` em arrays quando o trecho já usa esse padrão.
  - `if` com chaves e quebra de linha para `continue` / `return` antecipado — não colapsar em uma linha se o arquivo usa bloco.

---

## Documentação

- `docs/features/<feature>/specs.md` — fonte de verdade de **negócio** e decisões da feature.
- Atualizar `specs.md` **após** implementação validada, não antes.
- Não usar `/docs` para planos temporários.

---

## Segurança

- Credenciais e tokens **sempre** em `.env`.
- **Nunca** commitar: `.env`, credenciais, tokens, logs, builds.
- **Nunca** logar senhas, tokens, documentos pessoais ou dados sensíveis.
- **Nunca** expor credenciais no código.

---

## Git

- Commits em **português**, imperativo: `Adiciona`, `Corrige`, `Remove`, `Refatora`.
- Commits **somente** quando solicitado explicitamente.
- **Nunca** commitar `.env`, credenciais, tokens ou artefatos de build.

---

## Checklist rápido (agente)

- [ ] Li o `docs/features/<feature>/specs.md` da área?
- [ ] Trabalho na `dev-arthur`?
- [ ] Controller sem lógica de negócio?
- [ ] Query só com CRUD padrão?
- [ ] Service com try/catch e retorno padronizado?
- [ ] `fetch` só em Queries frontend?
- [ ] Rotas com `name()` e agrupamento `prefix`/`name` quando couber?
- [ ] Diff mínimo, estilo do arquivo preservado?