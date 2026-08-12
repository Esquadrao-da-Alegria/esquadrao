# Usuários e cargos — especificação para desenvolvedores

Documento de referência sobre o modelo de **usuários do sistema** e **cargos** (papéis no projeto). Objetivo: regras de negócio claras e decisões técnicas simples de manter.

---

## Vocabulário

| Termo        | Significado |
|-------------|-------------|
| **User**    | Registro na tabela `users` — conta que pode fazer login (Fortify, etc.). |
| **Voluntário** | Pessoa da ONG registrada na tabela `voluntarios`. Pode possuir uma conta `User` vinculada. |
| **Cargo**   | Tipo de papel/função (ex.: Artista, Coordenador Local). Catálogo na tabela `cargos`. |
| **Atribuição** | Ligação usuário ↔ cargo na tabela pivot histórica `voluntario_cargo`. Um usuário pode ter **vários** cargos. |

---

## Regras de negócio

1. **Cargos são um catálogo controlado**  
   Os cargos iniciais vêm do banco via seeder. **Slug** identifica o cargo de forma estável no código (policies, condições, testes). **Nome** é para exibição (telas, e-mails) e pode ser ajustado sem quebrar lógica, desde que o slug permaneça o mesmo.

2. **Uma conta de usuário pode ter múltiplos cargos**
   Relação **N:N** entre `users` e `cargos` via `voluntario_cargo`. Ex.: a conta vinculada a um voluntário pode ser Artista e Coordenador Local ao mesmo tempo.

   Atualmente, todo cargo vinculado à conta é considerado ativo. A pivot não guarda vigência nem histórico de ativação. Se o sistema passar a exigir histórico de cargos, essa evolução deverá adicionar campos de vigência ou status à atribuição sem alterar o significado dos slugs existentes.

3. **Não repetir o mesmo cargo no mesmo usuário**  
   Existe restrição única em `(voluntario_id, cargo_id)`. Tentar inserir o mesmo par duas vezes falha no banco.

4. **Remoção em cascata**  
   - Se um **usuário** for excluído, suas linhas em `voluntario_cargo` são removidas.  
   - Se um **cargo** for excluído, as atribuições àquele cargo são removidas.  
   Na prática, cargos devem ser tratados como dados de referência: evitar apagar cargo em produção se ainda houver vínculos; preferir descontinuar no processo se no futuro existir flag de “ativo”.

5. **Lista inicial de cargos**  
   Definida em `Database\Seeders\CargoSeeder::CARGOS`: Administrador, Diretor, Coordenador Geral, Coordenador Local, Artista, Psicologia, Apoio, Voluntário (ver slugs na constante no código).

6. **Autorização administrativa atual**
   O middleware `administrador` verifica o cargo de slug `administrador`. As demais decisões de autorização devem continuar explícitas, usando middleware, Policies, Gates, `User::temCargo()` ou consultas equivalentes.

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
| `voluntario_id` | FK → `users.id`. O nome é legado e não referencia a tabela `voluntarios`. |
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

### Nome legado da pivot `voluntario_cargo`

O nome foi preservado para evitar uma migração destrutiva. Apesar de `voluntario_id`, o vínculo pertence à conta e referencia `users.id`; a pessoa voluntária separada fica em `voluntarios`.

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
| Onde está o voluntário? | Na tabela `voluntarios`; a conta correspondente usa `users.voluntario_id`. |
| A quem pertencem os cargos? | Ao `User`; `voluntario_cargo.voluntario_id` ainda é FK para `users.id`. |
| Como identificar cargo no código? | Pelo **`slug`** (snake_case), preferencialmente via `CargoSeeder::CARGOS` ou `temCargo()`. |
| Um usuário pode ter vários cargos? | Sim. |
| Onde não mexer à toa? | Slugs usados em policies e testes — mudança implica refatoração ou migração. |
