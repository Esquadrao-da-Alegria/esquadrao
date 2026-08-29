# Visitas — especificação para desenvolvedores

Documento de referência sobre o modelo de **visitas** e **participantes** (inscrições de voluntários em visitas). Objetivo: regras de negócio claras e decisões técnicas simples de manter.

**Relatórios:** ver [`docs/features/visitas/relatorios/specs.md`](relatorios/specs.md).

---

## Vocabulário

| Termo | Significado |
|-------|-------------|
| **Visita** | Registro na tabela `visitas` — evento agendado ou realizado (hospital, residência, ação especial, etc.). Oficinas e reuniões ficam no módulo de **eventos**, não em visitas. |
| **VisitaParticipante** | Linha na pivot enriquecida `visita_participante` — liga visita a um voluntário com tipo, papel e status de participação. |
| **Voluntário** | No domínio, o voluntário **é** o usuário. A coluna na pivot chama-se `voluntario_id` e aponta para `users.id`. |
| **Líder** | Usuário responsável pela visita, referenciado em `visitas.lider_id` (nullable). **Não** é um valor de `PapelNaVisita`. |
| **Hospital** | Visitas dos tipos `hospital` e `residencia` exigem `hospital_id`. Para `acao_especial` e `outro`, o `hospital_id` é opcional (`nullable`). |
| **Ala** | Unidade/setor do hospital (`alas_hospitais`), opcional via `visitas.ala_unidade_id` (apenas quando houver hospital vinculado). Model `Ala`. |

---

## Regras de negócio

1. **Hospital obrigatorio condicional**  
   Visitas dos tipos `hospital` e `residencia` exigem `hospital_id`. Para `acao_especial` e `outro`, `hospital_id` é `nullable`. Quando não houver hospital, `ala_unidade_id` também deve ser `null`.

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
   - **restrict:** `visita_id` em `visitas_relatorios` — não é possível excluir fisicamente visita que possui relatório(s); relatórios são histórico.  
   - **nullOnDelete:** `ala_unidade_id`, `lider_id` — FK vira `null` se o registro referenciado for removido.  
   - **cascade:** `visita_id` em `visita_participante` — excluir visita remove participantes.

6. **Validação de datas**  
   Não há constraint `fim_em > inicio_em` no banco. Validar no service/form request quando existir UI de cadastro.

7. **Limite de participantes**  
   Configurável por visita via `visitas.limite_participantes` (padrão 5 para hospital/residência). Se `null`, a visita não possui limite de vagas (ilimitada). O limite considera apenas inscrições ativas (`papel_na_visita = participante`, `status_participacao ∈ {confirmado, pendente}`).

8. **Inscrição self-service e gestão de participantes**  
   Voluntário autenticado e **ativo** com `voluntario_id` preenchido pode se inscrever em visita `agendada` via modal do calendário, desde que a visita não esteja concluída nem com horário passado. Na tela de edição (`Edit.tsx`), gestores e líderes com `podeEditarVisita` podem adicionar ou remover participantes.

9. **Líder elegível**  
   Líder da visita: `User` com `status = ativo` e `voluntario_id` NOT NULL. Cargo `voluntario` **não** é requisito.

10. **Participante automático na criação**  
    Ao criar visita (`store`), o service insere na mesma transaction uma linha em `visita_participante` para o `lider_id`: `papel_na_visita = participante`, `tipo_participacao = palhaco`, `status_participacao = confirmado` (via `create`, não `firstOrCreate`).

11. **Liberação de agenda (visitas hospitalares)**  
    Visitas tipo `hospital` só podem ser criadas (ou ter `inicio_em` alterado no `update`) se o mês estiver liberado para a **cidade do hospital**. Backend: `Visita\Service` + `Visita\Agenda\Liberacao\Service::mesEstaLiberado`. Frontend: prop `meses_liberados` (YYYY-MM, cidade-base do usuário) alimenta selects de **mês** (somente liberados) e **dia** (todos os dias do mês); o submit envia `data` em `YYYY-MM-DD` como antes. Demais tipos usam input `date` sem essa restrição. Detalhes: [`docs/features/hospitais/specs.md`](../hospitais/specs.md).

12. **Cancelamento lógico de inscrição**  
    `DELETE participantes` não remove a linha — atualiza `status_participacao = cancelado`. Participante pode auto-cancelar, exceto o líder atual (deve trocar o líder antes). Gestores com `podeEditarVisita` podem cancelar qualquer participante.

13. **Reativação de inscrição cancelada**  
    Nova inscrição self-service reutiliza a linha existente `(visita_id, voluntario_id)` via `update`, reativando com `status_participacao = confirmado` e novo `tipo_participacao`.

14. **Erro seguro ao validar vagas**  
    Falha ao consultar participantes ativos retorna: `Não foi possível validar as vagas desta visita. Tente novamente.` — nunca assume zero vagas ocupadas.

---

## Modelo de dados

### Tabela `visitas`

Migration: `2026_06_15_000000_create_visitas_table.php`, `2026_08_10_000000_make_visitas_hospital_id_nullable_and_add_limite_participantes.php`

| Coluna | Descrição |
|--------|-----------|
| `id` | Chave primária. |
| `hospital_id` | FK → `hospitais.id` (nullable, restrictOnDelete). Obrigatório para `hospital` e `residencia`. |
| `ala_unidade_id` | FK → `alas_hospitais.id` (nullable, nullOnDelete). |
| `criado_por_id` | FK → `users.id` (NOT NULL, restrictOnDelete). |
| `lider_id` | FK → `users.id` (nullable, nullOnDelete). |
| `inicio_em` | Timestamp de início. |
| `fim_em` | Timestamp de fim. |
| `tipo` | varchar(50) — valores em `VisitaTipo`. |
| `limite_participantes` | unsignedSmallInteger (nullable). Limite de vagas da visita (null = ilimitado). |
| `status` | varchar(50) — valores em `VisitaStatus`. |
| `origem` | varchar(50) — valores em `VisitaOrigem`. |
| `observacoes` | Texto livre (nullable). |
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
| `Outro` | `outro` |

> `oficina` e `reuniao` **não** são tipos de visita — pertencem ao módulo de eventos (`EventoTipo`).

### VisitaStatus

| Case | Valor |
|------|-------|
| `Agendada` | `agendada` |
| `Realizada` | `realizada` |
| `Cancelada` | `cancelada` |
| `PendenteRelatorio` | `pendente_relatorio` |
| `Contabilizada` | `contabilizada` |
| `NaoContabilizada` | `nao_contabilizada` |

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

---

## Models

### Visita (`app/Models/Visita.php`)

**fillable:** `hospital_id`, `ala_unidade_id`, `criado_por_id`, `lider_id`, `inicio_em`, `fim_em`, `tipo`, `status`, `origem`, `observacoes`

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

## Tipagem TypeScript (`resources/js/types/index.d.ts`)

Tipos do frontend para props Inertia e componentes React. Os **union types** espelham os enums PHP (`app/Enums/`): mesmos valores string persistidos no banco. As **interfaces** descrevem o shape dos models quando serializados para JSON.

Importação padrão no projeto:

```tsx
import type { Visita, VisitaStatus, VisitaParticipante } from '@/types';
```

### Union types (enums)

| Tipo TS | Valores | Enum PHP |
|---------|---------|----------|
| `VisitaTipo` | `'hospital' \| 'residencia' \| 'acao_especial' \| 'outro'` | `VisitaTipo` |
| `VisitaStatus` | `'agendada' \| 'realizada' \| 'cancelada' \| 'pendente_relatorio' \| 'contabilizada' \| 'nao_contabilizada'` | `VisitaStatus` |
| `VisitaOrigem` | `'sistema' \| 'importacao' \| 'outro'` | `VisitaOrigem` |
| `TipoParticipacao` | `'palhaco' \| 'paisana'` | `TipoParticipacao` |
| `PapelNaVisita` | `'participante' \| 'relator'` | `PapelNaVisita` |
| `StatusParticipacao` | `'confirmado' \| 'pendente' \| 'cancelado' \| 'falta'` | `StatusParticipacao` |

> `pendente` em `StatusParticipacao` é distinto de qualquer valor de `VisitaStatus`.

### Interface `Visita`

| Campo | Tipo TS | Obrigatório | Descrição |
|-------|---------|-------------|-----------|
| `id` | `number` | não | Presente após persistência. |
| `hospital_id` | `number` | sim | FK → hospital. |
| `ala_unidade_id` | `number \| null` | não | FK → ala (nullable). |
| `criado_por_id` | `number` | sim | FK → usuário criador. |
| `lider_id` | `number \| null` | não | FK → líder da visita (nullable). |
| `inicio_em` | `string` | sim | ISO 8601 (datetime serializado). |
| `fim_em` | `string` | sim | ISO 8601 (datetime serializado). |
| `tipo` | `VisitaTipo` | sim | Tipo da visita. |
| `status` | `VisitaStatus` | sim | Status da visita. |
| `origem` | `VisitaOrigem` | sim | Origem do registro. |
| `observacoes` | `string \| null` | não | Texto livre. |
| `created_at` | `string` | não | Auditoria. |
| `updated_at` | `string` | não | Auditoria. |
| `hospital` | `Hospital` | não | Relacionamento eager-loaded. |
| `alaUnidade` | `AlaHospital \| null` | não | Relacionamento eager-loaded (`alaUnidade()`). |
| `criadoPor` | `User` | não | Relacionamento eager-loaded (`criadoPor()`). |
| `lider` | `User \| null` | não | Relacionamento eager-loaded (`lider()`). |
| `participantes` | `VisitaParticipante[]` | não | Relacionamento eager-loaded (`participantes()`). |

### Interface `VisitaParticipante`

| Campo | Tipo TS | Obrigatório | Descrição |
|-------|---------|-------------|-----------|
| `id` | `number` | não | Presente após persistência. |
| `visita_id` | `number` | sim | FK → visita. |
| `voluntario_id` | `number` | sim | FK → usuário (voluntário). |
| `tipo_participacao` | `TipoParticipacao` | sim | Palhaço ou paisana. |
| `papel_na_visita` | `PapelNaVisita` | sim | Participante ou relator. |
| `status_participacao` | `StatusParticipacao` | sim | Status da inscrição. |
| `created_at` | `string` | não | Auditoria. |
| `updated_at` | `string` | não | Auditoria. |
| `visita` | `Visita` | não | Relacionamento eager-loaded (`visita()`). |
| `voluntario` | `User` | não | Relacionamento eager-loaded (`voluntario()`). |

### Convenções frontend

- **Colunas** do banco permanecem em `snake_case` (`hospital_id`, `inicio_em`); **relacionamentos** serializados pelo Laravel/Inertia usam `camelCase` (`criadoPor`, `alaUnidade`), espelhando os nomes dos métodos Eloquent.
- **Datas** chegam como `string` (ISO 8601), não como `Date` — converter no componente quando necessário.
- Campos de relacionamento são opcionais na interface: só existem quando o controller faz eager load.
- Ao alterar enums PHP, atualizar o union type correspondente em `index.d.ts` e esta seção.

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
Visita::with(['hospital:id,nome,cidade_id'])->get();
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
| `tests/Feature/Visita/VisitaIndexTest.php` | Rota index, filtro de mês, auth |
| `tests/Feature/Visita/ParticipanteStoreTest.php` | Inscrição self-service, limite, duplicata, visita cancelada, voluntário ativo, reativação, auth |
| `tests/Feature/Visita/ParticipanteDestroyTest.php` | Cancelamento lógico, auto-cancelamento, bloqueio do líder, gestor, visita errada |
| `tests/Feature/Visita/VisitaStoreTest.php` | Create visita, validação, hospital inativo, auth |
| `tests/Feature/Visita/VisitaUpdateTest.php` | Permissões `podeEditarVisita` (incl. coordenador_local por cidade), update, hospital imutável |

```bash
vendor/bin/sail artisan test --compact tests/Unit/Visita tests/Feature/Visita
```

---

## Rotas Web

Grupo `visitas.` (middleware `auth` + `verified`):

| Método | URI | Action | Nome |
|--------|-----|--------|------|
| GET | `/visitas` | `VisitaController@index` | `visitas.index` |
| GET | `/visitas/create` | `VisitaController@create` | `visitas.create` |
| POST | `/visitas` | `VisitaController@store` | `visitas.store` |
| GET | `/visitas/{visita}/edit` | `VisitaController@edit` | `visitas.edit` |
| PUT | `/visitas/{visita}` | `VisitaController@update` | `visitas.update` |
| POST | `/visitas/{visita}/participantes` | `VisitaParticipanteController@store` | `visitas.participantes.store` |
| DELETE | `/visitas/{visita}/participantes/{participante}` | `VisitaParticipanteController@destroy` | `visitas.participantes.destroy` |

Query string (index): `?mes=YYYY-MM&cidade_id=ID|todas` — filtra visitas pelo mês de `inicio_em` e opcionalmente pela cidade do hospital (padrão: `cidade_base_id` do voluntário logado).

Camadas index: `VisitaController` → `Visita\Service` → `Visita\Queries`

Camadas create/edit: `VisitaController` → `Visita\Form\Service` (props Inertia) + `Visita\Service` (store/update)

Camadas participantes: `VisitaParticipanteController` → `Visita\Participante\Service` → `Visita\Participante\Queries`

### Logs de erro (`Visita\Service`)

- Queries retornam o envelope; o Service grava em `$retornoDatabase`, valida `sucesso` e, se falso, chama `logarErro` antes de devolver o envelope (mantém rollback/flash onde já existem).
- Exceptions no Service também passam por `logarErro`.
- Payload do log via `payloadLogErro`: `visita_id` (quando existir) mais contexto operacional (`lider_id`, `hospital_id`, `ala_unidade_id`, `criado_por_id`, `status`, `tipo`, `origem`, `inicio_em`, `fim_em`, filtros de listagem) — **nunca** o conteúdo de campos de texto (ex.: `observacoes`).
- Detalhe técnico do erro: mensagem da query (`erros[0]`) ou `formatarMensagemErro($th)`.

---

## Permissões de edição

`podeEditarVisita(user, visita)` — verdadeiro se **qualquer**:

- `user.id === visita.lider_id` (quando `lider_id` não é null)
- cargo `administrador`, `diretor` ou `coordenador_geral`
- cargo `coordenador_local` **e** `user.voluntario.cidade_base_id === visita.hospital.cidade_id` (ambos não null)

**Visita sem líder (`lider_id = null`):** apenas administrador, diretor, coordenador_geral ou coordenador_local da cidade do hospital editam e veem o botão no modal.

Implementação: `App\Services\Visita\Service::podeEditarVisita` (backend) e `lib/visita.ts::podeEditarVisita` (frontend).

`HandleInertiaRequests` carrega `voluntario:id,cidade_base_id` no user compartilhado para espelhar a regra no frontend.

Sem permissão em `edit`/`update`: redirect `/visitas` + flash `mensagem_erro`.

---

## Frontend — Formulário (Create/Edit)

### Arquivos

| Arquivo | Responsabilidade |
|---------|------------------|
| `Pages/Visita/Create.tsx` | Cadastro — título "Cadastrar visita" |
| `Pages/Visita/Edit.tsx` | Edição — título "Alterar visita" |
| `components/Painel/Visita/Formulario/Form.tsx` | Campos compartilhados (create + edit) |
| `lib/visita.ts` | `podeEditarVisita`, `labelTipo`, `extrairData`, `extrairHora`, `hojeLocal`, `dataPermitidaVisitaHospital`, `intervaloDatasLiberadas`, `VISITA_TIPOS`, `VISITA_STATUS` |

### Campos

| Campo | Create | Edit |
|-------|--------|------|
| Hospital | select (ativos) | select **disabled** |
| Ala / Unidade | select opcional; limpa ao trocar hospital | select **disabled** |
| Data | tipo `hospital`: selects mês + dia (`meses_liberados`); submit `data` `YYYY-MM-DD` | tipo `hospital`: mês + dia; outros tipos: `date` |
| Horário início / fim | time; vazios no create | time |
| Tipo | select; default `hospital` | select |
| Líder | select; default = user logado | select |
| Status | oculto (backend: `agendada`) | select (6 valores) |
| Observações | textarea opcional | textarea |

### Regras create

- Qualquer usuário autenticado pode criar.
- Backend define `criado_por_id`, `origem = sistema`, `status = agendada`.
- `StoreRequest` rejeita hospital inativo e ala de outro hospital.
- Visitas tipo `hospital` exigem mês liberado na cidade do hospital (`Visita\Service::store`).
- Líderes: users **ativos** com `voluntario_id` preenchido (sem exigir cargo `voluntario`); líder atual e usuário logado sempre incluídos no select quando ausentes da lista.
- Na criação, o service insere automaticamente o líder como participante confirmado na mesma transaction.

### Regras edit

- `UpdateRequest` não aceita `hospital_id` nem `ala_unidade_id` — imutáveis.
- `Visita\Form\Service` inclui hospital atual mesmo se inativo.
- Horários extraídos via `extrairData`/`extrairHora` (slice na string ISO, sem `Date` no browser).
- Pós-salvar: redirect `/visitas` + flash `mensagem_sucesso`.

### Timezone

`APP_TIMEZONE=America/Sao_Paulo` — datetimes serializados refletem horário Brasil.

---

## Frontend — Calendário (Index)

Página `/visitas` com calendário mensal de visitas para usuários autenticados.

### Arquivos

| Arquivo | Responsabilidade |
|---------|------------------|
| `Pages/Visita/Index.tsx` | Page principal — estado de modais, navegação de mês e cidade, exibição conjunta de visitas e eventos |
| `components/Painel/Visita/Card/Show.tsx` | Card de visita no calendário |
| `components/Painel/Visita/Card/EventoCardShow.tsx` | Card de evento no calendário de visitas (estilo índigo diferenciado) |
| `components/Painel/Visita/Calendario/Show.tsx` | Grade mensal |
| `components/Painel/Visita/Calendario/Detalhes/Modal/Show.tsx` | Modal de detalhes + inscrição em 2 passos |
| `components/Painel/Visita/Calendario/ListaCompleta/Modal/Show.tsx` | Modal lista completa do dia |
| `lib/visita.ts` | Helpers: `contarParticipantes`, `contarParticipantesAtivos`, `usuarioJaInscrito`, `participacaoAtivaDoUsuario`, `usuarioEhLiderDaVisita`, `visitaAtingiuLimite`, `classeCardPorStatus`, `labelStatus`, `podeEditarVisita`, `labelTipo`, `extrairData`, `extrairHora` |
| `Queries/Visita/Participante/Queries.tsx` | `fetch` POST/DELETE para `visitas.participantes.store` / `visitas.participantes.destroy` |
| `Services/Visita/Participante/Service.tsx` | Orquestra inscrição/cancelamento, toasts e reload Inertia |
| `utils/form.ts` | Helpers `obterCsrfToken` e `obterCsrfHeaders` (extrai token CSRF de cookie/meta para requisições fetch) |

### Regras

- Sem rota `show` — dados de detalhes em memória (props Inertia)
- Overflow: máximo 2 cards por dia; "+X mais" abre modal de lista completa
- Navegação de mês via `router.visit` com Inertia (sem reload de página)
- Estado dos modais centralizado na Page

### Modal de detalhes — fluxo de inscrição

**Passo 1 (detalhes):** exibe dados da visita.

- Botão **Visualizar Detalhes** (acima): visível só para quem passa em `podeEditarVisita`; navega para `/visitas/{id}/edit` e fecha o modal.
- Botão **Participar** / **Cancelar inscrição** (abaixo):
- Oculto se `status !== agendada`
- Se inscrito e **não** líder: botão **Cancelar inscrição** (estado `cancelando`)
- Se inscrito e **líder**: botão desabilitado com texto "Altere o líder antes de cancelar"
- Se não inscrito: botão **Participar**
- Ao clicar em Participar: se limite de 5 atingido → `toastInfo`; senão → passo 2
- **Cancelar inscrição** chama `Services/Visita/Participante/Service.cancelar` → em sucesso: toast, reload Inertia, fecha modal

**Passo 2 (inscrição):** escolha palhaço/paisana + **Confirmar** + **Voltar**.
- **Confirmar** chama `Services/Visita/Participante/Service.participar` → em sucesso: toast, reload Inertia, fecha modal
- **Voltar** retorna ao passo 1
- Estado `enviando` evita double-submit
- Ao fechar modal, passo reseta para detalhes

---

## Resumo

| Pergunta | Resposta curta |
|----------|------------------|
| Onde está o voluntário inscrito? | Em `visita_participante.voluntario_id` → `users.id`. |
| Quem é o líder? | `visitas.lider_id` → `users.id` (nullable). |
| Hospital é obrigatório? | Sim, `hospital_id` NOT NULL. |
| Como evitar duplicata de inscrito? | Unique `(visita_id, voluntario_id)` no banco; reativação via `update` se status era `cancelado`. |
| Quem pode ser líder? | User ativo com `voluntario_id` (cargo voluntário não é requisito). |
| Como cancelar inscrição? | `DELETE participantes` → `status_participacao = cancelado` (lógico). |
| Onde validar `fim_em > inicio_em`? | No service/form request (não no banco). |
| Onde estão os tipos TS de visita? | `resources/js/types/index.d.ts` — seção `// VISITAS`. |
