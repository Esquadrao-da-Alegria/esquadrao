# Visitas — especificação para desenvolvedores

Documento de referência sobre o modelo de **visitas** e **participantes** (inscrições de voluntários em visitas). Objetivo: regras de negócio claras e decisões técnicas simples de manter.

---

## Vocabulário

| Termo | Significado |
|-------|-------------|
| **Visita** | Registro na tabela `visitas` — evento agendado ou realizado (hospital, residência, oficina, etc.). |
| **VisitaParticipante** | Linha na pivot enriquecida `visita_participante` — liga visita a um voluntário com tipo, papel e status de participação. |
| **Voluntário** | No domínio, o voluntário **é** o usuário. A coluna na pivot chama-se `voluntario_id` e aponta para `users.id`. |
| **Líder** | Usuário responsável pela visita, referenciado em `visitas.lider_id` (nullable). **Não** é um valor de `PapelNaVisita`. |
| **Hospital** | Toda visita exige `hospital_id` NOT NULL — o local cadastrado onde a visita ocorre ou está vinculada. |
| **Ala** | Unidade/setor do hospital (`alas_hospitais`), opcional via `visitas.ala_unidade_id`. Model `Ala`. |

---

## Regras de negócio

1. **Toda visita exige hospital**  
   `hospital_id` é NOT NULL. Mesmo visitas de tipo `residencia` ou `acao_especial` ficam vinculadas a um hospital cadastrado.

2. **Líder só via `visitas.lider_id`**  
   O líder da visita é um `User` nullable em `visitas.lider_id`. Papéis na pivot (`PapelNaVisita`) são apenas `participante` e `relator`.

3. **Inscritos via `participantes()`**  
   Não existe `voluntarios()` belongsToMany. Acesso aos inscritos:

   ```php
   foreach ($visita->participantes as $participante) {
       $participante->tipo_participacao;  // enum
       $participante->voluntario->name;    // User
   }

   $visita->lider; // User — líder da visita
   ```

4. **Não repetir o mesmo voluntário na mesma visita**  
   Restrição única em `(visita_id, voluntario_id)`. Tentar inserir o mesmo par duas vezes falha no banco.

5. **Políticas de exclusão (onDelete)**  
   - **restrict:** `hospital_id`, `criado_por_id`, `voluntario_id` — não é possível excluir hospital/user com visitas ou participações vinculadas. Preferir inativar no service.  
   - **nullOnDelete:** `ala_unidade_id`, `lider_id` — FK vira `null` se o registro referenciado for removido.  
   - **cascade:** `visita_id` em `visita_participante` — excluir visita remove participantes.

6. **Validação de datas**  
   Não há constraint `fim_em > inicio_em` no banco. Validar no service/form request quando existir UI de cadastro.

---

## Modelo de dados

### Tabela `visitas`

Migration: `2026_06_15_000000_create_visitas_table.php`

| Coluna | Descrição |
|--------|-----------|
| `id` | Chave primária. |
| `hospital_id` | FK → `hospitais.id` (NOT NULL, restrictOnDelete). |
| `ala_unidade_id` | FK → `alas_hospitais.id` (nullable, nullOnDelete). |
| `criado_por_id` | FK → `users.id` (NOT NULL, restrictOnDelete). |
| `lider_id` | FK → `users.id` (nullable, nullOnDelete). |
| `inicio_em` | Timestamp de início. |
| `fim_em` | Timestamp de fim. |
| `tipo` | varchar(50) — valores em `VisitaTipo`. |
| `status` | varchar(50) — valores em `VisitaStatus`. |
| `origem` | varchar(50) — valores em `VisitaOrigem`. |
| `observacao` | Texto livre (nullable). |
| `created_at`, `updated_at` | Auditoria. |

**Índices:** `visitas_inicio_em_index`, `visitas_status_index`. FKs criam índice automático em `hospital_id`, `criado_por_id`, `ala_unidade_id`, `lider_id`.

### Tabela `visita_participante`

Migration: `2026_06_15_000001_create_visita_participante_table.php`

| Coluna | Descrição |
|--------|-----------|
| `id` | Chave primária. |
| `visita_id` | FK → `visitas.id` (cascadeOnDelete). |
| `voluntario_id` | FK → `users.id` (restrictOnDelete). |
| `tipo_participacao` | varchar(50) — valores em `TipoParticipacao`. |
| `papel_na_visita` | varchar(50) — valores em `PapelNaVisita`. |
| `status_participacao` | varchar(50) — valores em `StatusParticipacao`. |
| `created_at`, `updated_at` | Auditoria. |

**Constraints:** unique `visita_participante_visita_voluntario_unique` em `(visita_id, voluntario_id)`.  
**Índice:** `visita_participante_status_participacao_index`.

---

## Enums (`app/Enums/`)

Valores persistidos como varchar(50) no banco; cast para enum PHP backed string nos models.

### VisitaTipo

| Case | Valor |
|------|-------|
| `Hospital` | `hospital` |
| `Residencia` | `residencia` |
| `AcaoEspecial` | `acao_especial` |
| `Oficina` | `oficina` |
| `Reuniao` | `reuniao` |
| `Outro` | `outro` |

### VisitaStatus

| Case | Valor |
|------|-------|
| `Agendada` | `agendada` |
| `Cancelada` | `cancelada` |
| `Realizada` | `realizada` |
| `Pendente` | `pendente` |

### VisitaOrigem

| Case | Valor |
|------|-------|
| `Sistema` | `sistema` |
| `Importacao` | `importacao` |
| `Outro` | `outro` |

### TipoParticipacao

| Case | Valor |
|------|-------|
| `Palhaco` | `palhaco` |
| `Paisana` | `paisana` |

### PapelNaVisita

| Case | Valor |
|------|-------|
| `Participante` | `participante` |
| `Relator` | `relator` |

> Sem case `Lider` — líder é `visitas.lider_id`.

### StatusParticipacao

| Case | Valor |
|------|-------|
| `Confirmado` | `confirmado` |
| `Pendente` | `pendente` |
| `Cancelado` | `cancelado` |
| `Falta` | `falta` |

> `pendente` existe também em `VisitaStatus` — cuidado em logs/UI para não confundir contextos.

---

## Models

### Visita (`app/Models/Visita.php`)

**fillable:** `hospital_id`, `ala_unidade_id`, `criado_por_id`, `lider_id`, `inicio_em`, `fim_em`, `tipo`, `status`, `origem`, `observacao`

**casts:** `inicio_em`/`fim_em` → datetime; `tipo` → `VisitaTipo`; `status` → `VisitaStatus`; `origem` → `VisitaOrigem`

**relacionamentos:**

| Método | Tipo | Destino |
|--------|------|---------|
| `hospital()` | BelongsTo | `Hospital` |
| `alaUnidade()` | BelongsTo | `Ala` (table `alas_hospitais`) |
| `criadoPor()` | BelongsTo | `User` |
| `lider()` | BelongsTo | `User` |
| `participantes()` | HasMany | `VisitaParticipante` |

### VisitaParticipante (`app/Models/VisitaParticipante.php`)

**fillable:** `visita_id`, `voluntario_id`, `tipo_participacao`, `papel_na_visita`, `status_participacao`

**casts:** `tipo_participacao` → `TipoParticipacao`; `papel_na_visita` → `PapelNaVisita`; `status_participacao` → `StatusParticipacao`

**relacionamentos:**

| Método | Tipo | Destino |
|--------|------|---------|
| `visita()` | BelongsTo | `Visita` |
| `voluntario()` | BelongsTo | `User` |

---

## Decisões técnicas

### varchar(50) + enum PHP backed string

O banco armazena strings; os models fazem cast para enums PHP. Evita enum nativo no MySQL e mantém flexibilidade para novos valores via migration + enum.

### Sem `voluntarios()` belongsToMany

A pivot `visita_participante` é enriquecida (tipo, papel, status). Usar model `VisitaParticipante` e `participantes()` HasMany em vez de belongsToMany genérico.

### Ala via `Ala::class`

O codebase usa model `Ala` com table `alas_hospitais`, não `AlaHospital`. Relacionamento `alaUnidade()` segue essa convenção.

### Eager load enxuto em listagens

`Hospital` pode carregar `alas` por default (`protected $with`). Em listagens de visitas, preferir:

```php
Visita::with(['hospital:id,nome'])->get();
```

---

## Operações comuns

### Migrations

```bash
vendor/bin/sail artisan migrate
```

### Criar visita com participante

```php
use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\Visita;
use App\Models\VisitaParticipante;

$visita = Visita::query()->create([
    'hospital_id' => $hospitalId,
    'criado_por_id' => auth()->id(),
    'lider_id' => $liderId,
    'inicio_em' => $inicio,
    'fim_em' => $fim,
    'tipo' => VisitaTipo::Hospital,
    'status' => VisitaStatus::Agendada,
    'origem' => VisitaOrigem::Sistema,
]);

VisitaParticipante::query()->create([
    'visita_id' => $visita->id,
    'voluntario_id' => $voluntarioId,
    'tipo_participacao' => TipoParticipacao::Palhaco,
    'papel_na_visita' => PapelNaVisita::Participante,
    'status_participacao' => StatusParticipacao::Confirmado,
]);
```

---

## Testes

| Arquivo | Escopo |
|---------|--------|
| `tests/Unit/Visita/EnumsTest.php` | Valores dos 6 enums (sem banco) |
| `tests/Feature/Visita/MigrationTest.php` | Tabelas criadas |
| `tests/Feature/Visita/VisitaModelTest.php` | Casts e relacionamentos de `Visita` |
| `tests/Feature/Visita/VisitaParticipanteModelTest.php` | Casts, relacionamentos e unique por comportamento |

```bash
vendor/bin/sail artisan test --compact tests/Unit/Visita tests/Feature/Visita
```

---

## Resumo

| Pergunta | Resposta curta |
|----------|------------------|
| Onde está o voluntário inscrito? | Em `visita_participante.voluntario_id` → `users.id`. |
| Quem é o líder? | `visitas.lider_id` → `users.id` (nullable). |
| Hospital é obrigatório? | Sim, `hospital_id` NOT NULL. |
| Como evitar duplicata de inscrito? | Unique `(visita_id, voluntario_id)` no banco. |
| Onde validar `fim_em > inicio_em`? | No service/form request (não no banco). |
