# Usuários e cargos — especificação para desenvolvedores

Documento de referência sobre o modelo de **voluntários** (usuários autenticados) e **cargos** (papéis no projeto). Objetivo: regras de negócio claras e decisões técnicas simples de manter.

---

## Vocabulário

| Termo        | Significado |
|-------------|-------------|
| **User**    | Registro na tabela `users` — pessoa que pode fazer login (Fortify, etc.). |
| **Voluntário** | No domínio, o voluntário **é** o usuário. A coluna na pivot chama-se `voluntario_id` e aponta para `users.id`. Não existe tabela `voluntarios` separada. |
| **Cargo**   | Tipo de papel/função (ex.: Artista, Coordenador Local). Catálogo na tabela `cargos`. |
| **Atribuição** | Ligação usuário ↔ cargo na tabela pivot `voluntario_cargo`. Um usuário pode ter **vários** cargos. |

---

## Regras de negócio

1. **Cargos são um catálogo controlado**  
   Os cargos iniciais vêm do banco via seeder. **Slug** identifica o cargo de forma estável no código (policies, condições, testes). **Nome** é para exibição (telas, e-mails) e pode ser ajustado sem quebrar lógica, desde que o slug permaneça o mesmo.

2. **Um voluntário pode ter múltiplos cargos**  
   Relação **N:N** entre `users` e `cargos` via `voluntario_cargo`. Ex.: alguém pode ser Artista e Coordenador Local ao mesmo tempo.

3. **Não repetir o mesmo cargo no mesmo usuário**  
   Existe restrição única em `(voluntario_id, cargo_id)`. Tentar inserir o mesmo par duas vezes falha no banco.

4. **Remoção em cascata**  
   - Se um **usuário** for excluído, suas linhas em `voluntario_cargo` são removidas.  
   - Se um **cargo** for excluído, as atribuições àquele cargo são removidas.  
   Na prática, cargos devem ser tratados como dados de referência: evitar apagar cargo em produção se ainda houver vínculos; preferir descontinuar no processo se no futuro existir flag de “ativo”.

5. **Lista inicial de cargos**  
   Definida em `Database\Seeders\CargoSeeder::CARGOS`: Administrador, Diretor, Coordenador Geral, Coordenador Local, Artista, Psicologia, Apoio, Voluntário (ver slugs na constante no código).

6. **O que ainda não é regra automática no código**  
   Hoje **não** há middleware, Gate ou Policy padrão ligados a cargos no repositório. Autorização (quem pode acessar qual rota ou ação) deve ser implementada de forma explícita usando `User::temCargo()` e/ou `$user->cargos()` (ou consultas equivalentes). Isso evita “mágica” escondida e mantém o fluxo legível.

---

## Modelo de dados

### Tabela `cargos`

| Coluna       | Descrição |
|-------------|-----------|
| `id`        | Chave primária. |
| `nome`      | Texto para interface (até 120 caracteres). |
| `slug`      | Identificador estável, **único** (até 120 caracteres). Usar em código, não o `id`, quando possível. |
| `created_at`, `updated_at` | Auditoria simples. |

### Tabela `voluntario_cargo` (pivot)

| Coluna          | Descrição |
|-----------------|-----------|
| `id`            | Chave primária da linha da pivot. |
| `voluntario_id` | FK → `users.id` |
| `cargo_id`      | FK → `cargos.id` |
| `created_at`, `updated_at` | Quando a atribuição foi criada/atualizada. |

Migrations: `2026_05_04_000000_create_cargos_table.php`, `2026_05_04_000001_create_voluntario_cargo_table.php`.

---

## Decisões técnicas (simplicidade e manutenção)

### Slug em `snake_case`

Slugs usam **snake_case** (ex.: `coordenador_geral`), não kebab-case. Motivo: alinhar com convenções comuns em PHP/Laravel (chaves, colunas, leitura em `temCargo('coordenador_geral')`). Não são slugs de URL pública; são chaves internas.

### Fonte da verdade dos cargos fixos: `CargoSeeder::CARGOS`

A lista canônica fica em **uma constante** no seeder. Benefícios:

- Leitura única ao adicionar cargo novo.
- Testes e documentação podem referenciar os mesmos slugs.
- `upsert` por `slug`: reexecutar o seeder **atualiza** `nome` (e `updated_at`) se mudar o rótulo, sem duplicar cargo; inserts criam novos slugs.

Ao criar um cargo novo: incluir em `CARGOS`, rodar o seeder (ou deploy que rode seeders idempotentes).

### Eloquent em vez de só SQL na aplicação

- `App\Models\Cargo` — tabela `cargos`, relação `voluntarios()`.
- `App\Models\User` — `cargos()` com `withTimestamps()` na pivot, e `temCargo(string $slug): bool` para checagens pontuais.

Manter regras de “pode ou não pode” em **Policies/Gates** que internamente chamem `temCargo` ou inspecionem `cargos` tende a ficar mais testável do que espalhar slugs em controllers.

### Por que não um pacote de RBAC pronto?

O escopo atual é **cargos + pivot**, sem permissões granulares (ex.: `posts.edit`). Pacotes como Spatie Permission acrescentam conceitos e migrações extras. Enquanto a necessidade for “usuário tem um ou mais papéis”, o modelo atual permanece **fácil de entender**. Se no futuro surgirem dezenas de permissões nomeadas, reavaliar um pacote ou uma tabela `permissoes` separada.

### Nome da pivot `voluntario_cargo`

Deixa explícito no banco que o vínculo é “voluntário (user) possui cargo”, em português do domínio, em vez de nomes genéricos só em inglês.

---

## Operações comuns

### Migrations e seed

```bash
php artisan migrate
php artisan db:seed --class=CargoSeeder
```

O `DatabaseSeeder` já inclui `CargoSeeder` na cadeia principal.

### Atribuir cargos a um usuário

```php
use App\Models\Cargo;
use App\Models\User;

$user = User::find($id);

// Por id de cargo
$user->cargos()->attach($cargoId);

// Por slug (exemplo)
$cargoId = Cargo::query()->where('slug', 'artista')->value('id');
$user->cargos()->syncWithoutDetaching([$cargoId]);

// Substituir todos os cargos do usuário
$user->cargos()->sync([$id1, $id2]);
```

### Verificar cargo no backend

```php
if ($user->temCargo('administrador')) {
    // ...
}

// Evitar N+1 em listagens
$users = User::query()->with('cargos')->get();
```

---

## Checklist ao evoluir o recurso

- [ ] Novo cargo: entrada em `CargoSeeder::CARGOS` + rodar seeder em ambientes que precisem do dado.
- [ ] Nova rota/ação restrita: Policy ou Gate + teste que cubra com/sem cargo.
- [ ] UI: exibir `nome` do cargo; nunca pedir ao usuário final que digite `slug`.
- [ ] Se alterar slug de um cargo existente em produção: planejar migração de dados ou compatibilidade (código antigo pode ainda referenciar o slug anterior).

---

## Resumo

| Pergunta | Resposta curta |
|----------|------------------|
| Onde está o voluntário? | Na tabela `users`; `voluntario_id` na pivot é FK para `users`. |
| Como identificar cargo no código? | Pelo **`slug`** (snake_case), preferencialmente via `CargoSeeder::CARGOS` ou `temCargo()`. |
| Um usuário pode ter vários cargos? | Sim. |
| Onde não mexer à toa? | Slugs usados em policies e testes — mudança implica refatoração ou migração. |
