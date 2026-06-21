# História 2 — Visualizar visitas no calendário

Design validado em brainstorming (2026-06-15). Referência de domínio: `docs/visitas/specs.md`.

---

## Princípios desta implementação

1. **RESTful** — `Route::resource` como Hospitais/Voluntários; nesta história só `index` é implementado.
2. **KISS** — copiar estrutura existente (`HospitalController` → `Service` → `Queries`); sem abstrações extras, hooks ou partial reload.
3. **Testes simples** — um arquivo Feature com helpers privados (mesmo estilo de `VisitaModelTest` e `DashboardTest`); sem testes unitários de Query isolada.

---

## Objetivo

Calendário mensal em `/visitas` para usuários autenticados visualizarem visitas do mês, com cards no calendário e modais de detalhes/lista completa. Dados vêm do Inertia — sem rota `show` e sem tela de criação nesta história.

---

## Decisões de produto

| # | Tópico | Decisão |
|---|--------|---------|
| 1 | Transporte | Inertia — `router.visit` na troca de mês |
| 2 | Status | Todos os 6 enums de `VisitaStatus` |
| 3 | Contagem participantes | Frontend: `papel_na_visita === 'participante'` → agrupa por `tipo_participacao` |
| 4 | Modal detalhes | Dados em memória; sem rota `show` |
| 5 | Cores cards | Grupos semânticos (ver Frontend) |
| 6 | Overflow | N = 2; "+X mais" → modal lista completa |
| 7 | Nova visita | Botão no header (padrão Hospitais); toast info *"Função disponível em breve ;)"* |
| 8 | Auth | `auth` + `verified` — todos autenticados |
| 9 | Menu | "Visitas" no `PainelLayout` para todos autenticados |
| 10 | Filtro mês | Mês de `inicio_em` |
| 11 | Campo texto | **`observacoes`** (canônico) |
| 12 | Payload | `participantes[]` com `voluntario` eager-loaded |

---

## Backend

### Rota (RESTful)

Registrar no grupo `auth` + `verified` (fora de `administrador`), igual aos demais recursos autenticados:

```php
Route::resource('/visitas', VisitaController::class)
    ->parameters(['visitas' => 'visita'])
    ->only(['index']);
```

| Método | URI | Action | Nome |
|--------|-----|--------|------|
| GET | `/visitas` | `index` | `visitas.index` |

Query string: `?mes=YYYY-MM` (filtro, não altera a rota REST).

Histórias futuras expandem o mesmo `Route::resource` com `create`, `store`, etc.

### Camadas (espelho de Hospital)

| Arquivo | Responsabilidade |
|---------|------------------|
| `app/Http/Controllers/Web/VisitaController.php` | `index` — normaliza `mes`, chama service, renderiza Inertia |
| `app/Services/Visita/Service.php` | Delega para Queries; flash de erro; retorno padronizado |
| `app/Queries/Visita/Queries.php` | Eloquent — filtro `mes`, eager load, ordenação |

### `VisitaController@index`

Copiar fluxo de `HospitalController@index`:

```php
public function index(Request $request)
{
    $mes = $this->normalizarMes($request->query('mes'));

    $filtrosBusca = [
        'mes'            => $mes,
        'retornar_lista' => true,
    ];

    $retorno = $this->service->index($filtrosBusca);

    return Inertia::render('Visita/Index', [
        'visitas' => $retorno['dados'],
        'mes'     => $mes,
    ]);
}
```

`normalizarMes(?string $mes): string` — método privado no controller:
- Ausente → `now()->format('Y-m')`
- Inválido → mês corrente
- Válido → retorna `YYYY-MM`

Sem Form Request nesta história (só leitura com query string).

### `Service::index`

Idêntico ao padrão de `Hospital\Service::index`:

```php
public function index(array $filtros): array
{
    try {
        $retorno = $this->queries->index($filtros);

        if (!$retorno['sucesso']) {
            session()->flash('mensagem_erro', 'Erro ao listar visitas!');
        }

        return $retorno;
    } catch (\Throwable $th) {
        return [
            'sucesso' => false,
            'dados'   => [],
            'erros'   => [formatarMensagemErro($th)],
        ];
    }
}
```

### `Queries::index`

Mesmo shape de `Hospital/Queries::index` — `retornar_lista` no `$filtros`, filtro no `aplicarFiltros`:

```php
public function index(array $filtros): array
{
    $retornarLista = $filtros['retornar_lista'];

    try {
        $query = Visita::query()
            ->with([
                'hospital:id,nome',
                'alaUnidade:id,nome',
                'lider:id,name',
                'participantes.voluntario:id,name',
            ]);

        $this->aplicarFiltros($query, $filtros);

        $query->orderBy('inicio_em');

        $dados = $retornarLista ? $query->get() : $query->first();

        return ['sucesso' => true, 'dados' => $dados, 'erros' => []];
    } catch (\Throwable $th) {
        // mesmo padrão Hospital
    }
}

private function aplicarFiltros(Builder $query, array $filtros): void
{
    foreach ($filtros as $campo => $valor) {
        if (empty($valor)) {
            continue;
        }

        switch ($campo) {
            case 'mes':
                $inicio = Carbon::createFromFormat('Y-m', $valor)->startOfMonth();
                $fim    = $inicio->copy()->endOfMonth();
                $query->whereBetween('inicio_em', [$inicio, $fim]);
                break;
        }
    }
}
```

Sem filtro por status. Sem DTO/transformer — Eloquent serializado direto para o Inertia.

---

## Frontend

### Page — `resources/js/Pages/Visita/Index.tsx`

- `PainelLayout`
- Props: `{ visitas: Visita[], mes: string }`
- Header igual `Hospital/Index.tsx`: título + seletor mês + setas + botão "Nova visita"
- Troca de mês: `router.visit(index({ query: { mes } }).url, { preserveScroll: true })` via Wayfinder `@/routes/visitas`
- Botão nova visita: `toastInfo('Função disponível em breve ;)')`
- Estado dos modais (visita selecionada, dia overflow) fica **na Page** — componentes filhos recebem props + callbacks

### Componentes

```
resources/js/components/Painel/Visita/
├── Card/Show.tsx
├── Calendario/Show.tsx
├── Calendario/ListaCompleta/Modal/Show.tsx
└── Calendario/Detalhes/Modal/Show.tsx
```

Convenção: `{Componente}/Show.tsx` — nunca `Card.tsx` solto.

| Componente | Faz | Não faz |
|------------|-----|---------|
| `Calendario/Show.tsx` | Grade mensal, agrupa visitas por dia | Fetch, estado global |
| `Card/Show.tsx` | Horário + hospital + cor por status | Lógica de modal |
| `ListaCompleta/Modal/Show.tsx` | Lista visitas de um dia | Buscar dados |
| `Detalhes/Modal/Show.tsx` | Exibe campos da visita + badge | Botões de ação |

### Helpers frontend (KISS)

Um único arquivo utilitário, se a lógica se repetir:

`resources/js/lib/visita.ts`

```typescript
export function contarParticipantes(visita: Visita) {
    const lista = visita.participantes?.filter((p) => p.papel_na_visita === 'participante') ?? [];

    return {
        palhaco: lista.filter((p) => p.tipo_participacao === 'palhaco').length,
        paisana: lista.filter((p) => p.tipo_participacao === 'paisana').length,
    };
}

export function classeCardPorStatus(status: VisitaStatus): string {
    // mapa status → classes Tailwind (grupos semânticos)
}
```

Sem hook customizado. Sem biblioteca de calendário externa — grade manual com array de dias.

### Cores dos cards

| Grupo | Status | Estilo |
|-------|--------|--------|
| Ativas | `agendada` | Amber primário |
| Concluídas | `realizada`, `contabilizada` | Verde suave |
| Pós-visita | `pendente_relatorio`, `nao_contabilizada` | Laranja suave |
| Inativas | `cancelada` | Cinza discreto |

### Menu — `PainelLayout`

- Item "Visitas" (`CalendarDays`), `visivel: true`
- Ativo quando `pathname === '/visitas'`

### Toast

Adicionar `toastInfo` em `resources/js/lib/utils/toast.ts` — SweetAlert2 com `icon: 'info'`, mesmo padrão de `toastSucesso`/`toastErro`.

---

## Types & docs

- `resources/js/types/index.d.ts` — `observacao` → `observacoes`; `VisitaStatus` com 6 valores
- `docs/visitas/specs.md` — alinhar `observacoes`

---

## Testes

**Um arquivo:** `tests/Feature/Visita/VisitaIndexTest.php`

Estilo KISS — copiar helpers de `VisitaModelTest` (`criarHospital`, `criarVisita`) como métodos privados na mesma classe de teste. Sem factories extras, sem mocks.

| Teste | O que verifica |
|-------|----------------|
| `test_convidado_e_redirecionado_para_login` | GET `/visitas` sem auth → redirect login |
| `test_filtra_visitas_pelo_mes_informado` | `?mes=2026-06` → Inertia com só visitas de jun/2026 |
| `test_usa_mes_corrente_quando_mes_nao_informado` | Sem query → visitas do mês atual |
| `test_visita_cancelada_aparece_no_resultado` | Status `cancelada` no mês → presente em `visitas` |
| `test_visita_de_outro_mes_nao_aparece` | Visita em jul/2026 → ausente ao filtrar jun/2026 |

Assertivas com `assertInertia` (padrão `TwoFactorAuthenticationTest`):

```php
$this->actingAs($user)
    ->get(route('visitas.index', ['mes' => '2026-06']))
    ->assertOk()
    ->assertInertia(fn (Assert $page) => $page
        ->component('Visita/Index')
        ->where('mes', '2026-06')
        ->has('visitas', 1)
        ->where('visitas.0.id', $visitaJunho->id)
    );
```

Setup mínimo por teste: cria só os registros necessários (1 user, 1 hospital, 1–2 visitas).

```bash
vendor/bin/sail artisan test --compact --filter=VisitaIndexTest
```

**Fora de escopo de testes nesta história:** testes unitários de Query, testes de componente React, testes E2E.

---

## Fora de escopo

- Actions REST além de `index` (`show`, `create`, `store`, …)
- Botões de ação no modal
- FAB

---

## Arquivos

| Ação | Arquivo |
|------|---------|
| Criar | `app/Http/Controllers/Web/VisitaController.php` |
| Criar | `app/Services/Visita/Service.php` |
| Criar | `app/Queries/Visita/Queries.php` |
| Modificar | `routes/web.php` |
| Criar | `resources/js/Pages/Visita/Index.tsx` |
| Criar | `resources/js/components/Painel/Visita/Card/Show.tsx` |
| Criar | `resources/js/components/Painel/Visita/Calendario/Show.tsx` |
| Criar | `resources/js/components/Painel/Visita/Calendario/ListaCompleta/Modal/Show.tsx` |
| Criar | `resources/js/components/Painel/Visita/Calendario/Detalhes/Modal/Show.tsx` |
| Criar | `resources/js/lib/visita.ts` |
| Modificar | `resources/js/layouts/PainelLayout.tsx` |
| Modificar | `resources/js/types/index.d.ts` |
| Modificar | `resources/js/lib/utils/toast.ts` |
| Modificar | `docs/visitas/specs.md` |
| Criar | `tests/Feature/Visita/VisitaIndexTest.php` |
