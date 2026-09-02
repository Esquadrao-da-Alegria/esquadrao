# Hospitais — metas e liberação de agendas

Documento de referência sobre **metas mensais/semanais por hospital** e **liberação de agenda por cidade/mês** para cadastro de visitas hospitalares.

**Visitas (validação de agenda, formulário):** ver [`docs/features/visitas/specs.md`](../visitas/specs.md).

---

## Vocabulário

| Termo | Significado |
|-------|-------------|
| **Meta mensal** | Quantidade opcional de visitas previstas para um hospital em um mês (`metas_mensais_hospitais`). |
| **Meta semanal** | Distribuição opcional da meta mensal por semana do mês (`metas_semanais_hospitais`). |
| **Realizadas** | Visitas contabilizadas agregadas de `visitas` — status `realizada`, `pendente_relatorio` ou `contabilizada`. |
| **Liberação de agenda** | Registro por cidade/ano/mês indicando se visitas **hospitalares** podem ser agendadas (`agenda_liberacoes_cidades.liberado`). |

---

## Regras de negócio — Metas

1. **Metas são opcionais** — hospital sem meta mensal no mês não exige configuração; não bloqueia nem libera agenda.
2. **Limites de quantidade** — meta mensal: máximo **10** visitas; meta semanal (hospital ou por ala): máximo **5** visitas por semana.
3. **Meta semanal exige meta mensal** — não persiste semanais sem quantidade mensal preenchida.
4. **Soma semanal = meta mensal** — quando houver metas semanais, a soma de todas as semanas (e alas, se aplicável) deve ser **exatamente** igual à meta mensal.
5. **Modo hospital ou por ala** — por hospital/mês, apenas um modo: semanas do hospital (`ala_unidade_id` nulo) **ou** semanas por ala (`ala_unidade_id` preenchido). Trocar o modo remove registros do modo anterior.
6. **Ala da meta** — meta por ala só aceita alas do próprio hospital. Visita **sem** ala conta no resumo hospitalar, não na meta da ala.
7. **Semanas do mês** — semanas completas de **domingo a sábado** (fecham no sábado). A **primeira** semana é quebrada quando o mês não começa no domingo (ex.: quarta → sábado). A **última** é quebrada quando o mês não termina no sábado. Demais semanas começam no domingo. Helper: `App\Helpers\MetaHospital::semanasDoMes()`.
8. **Escopo geográfico** — coordenadores locais e diretores configuram apenas hospitais **ativos** da cidade-base. Administradores e coordenadores gerais podem acessar hospitais de outras cidades.
9. **Apoio ao agendamento** — na agenda de visitas, o progresso das metas soma visitas já realizadas e visitas agendadas para evitar planejamento acima da meta. A meta mensal considera todas as alas do hospital. Quando a meta semanal é por ala, somente visitas vinculadas à respectiva ala entram no progresso semanal; visitas sem ala não cumprem uma meta específica de ala.
10. **Semana de referência na agenda** — o acompanhamento compacto apresenta somente uma semana. Para o mês atual usa a semana que contém o dia de hoje; para mês futuro usa a primeira semana com meta ainda não contemplada; para mês passado usa a última semana do mês.

---

## Regras de negócio — Liberação de agenda

1. **Independente das metas** — liberação/bloqueio não depende de meta configurada.
2. **Por cidade/mês** — um registro por `(cidade_id, ano, mes)` com coluna booleana `liberado`.
3. **Implantação** — migration cria registros de janeiro do ano corrente até +5 anos para todas as cidades existentes; **somente o mês corrente** inicia com `liberado = true`; demais meses com `liberado = false`.
4. **Alteração** — gestores podem liberar ou bloquear **mês atual e futuros**. Meses passados são somente leitura.
5. **Bloquear** — `UPDATE liberado = false` (registro permanece). **Liberar** — `UPDATE liberado = true` e `liberado_por_id` = usuário que liberou; seed inicial mantém `liberado_por_id = null`.
6. **Impacto em visitas** — validação de agenda aplica-se **somente** a visitas tipo `hospital`, pela **cidade do hospital** (backend). Frontend restringe datas pelos meses liberados da **cidade-base** do usuário.

---

## Permissões

Helper: `App\Helpers\User::ehGestor(User)`.

| Requisito | Detalhe |
|-----------|---------|
| Cargos | `administrador`, `diretor`, `coordenador_geral`, `coordenador_local` |
| Cidade-base | Obrigatória para diretor e coordenador local; administrador e coordenador geral podem operar com escopo global sem cidade-base |
| Escopo | Administrador e coordenador geral possuem escopo global; diretor e coordenador local ficam limitados à cidade-base |

Shared Inertia: `eh_gestor` (via `HandleInertiaRequests`).

Navegação: **Hospitais** lista os hospitais permitidos e abre a aba **Metas de visitas** de cada hospital. O controle de agenda fica na página **Visitas**, associado ao mês e à cidade selecionados.

Administradores continuam sendo os únicos que alteram os dados cadastrais dos hospitais. Os demais gestores podem consultar a listagem para acessar metas, sem receber acesso ao formulário cadastral.

---

## Modelo de dados

### `metas_mensais_hospitais`

Migration: `2026_08_29_000000_create_metas_mensais_hospitais_table.php`  
Model: `App\Models\MetaMensalHospital`

| Coluna | Descrição |
|--------|-----------|
| `hospital_id` | FK → `hospitais` |
| `ano`, `mes` | Período (1–12) |
| `quantidade` | Meta mensal |

Unique: `(hospital_id, ano, mes)`.

### `metas_semanais_hospitais`

Migration: `2026_08_29_000001_create_metas_semanais_hospitais_table.php`  
Model: `App\Models\MetaSemanalHospital`

| Coluna | Descrição |
|--------|-----------|
| `hospital_id` | FK → `hospitais` |
| `ala_unidade_id` | FK → `alas_hospitais`, nullable (null = meta do hospital) |
| `ano`, `mes`, `semana` | Período; semana 1–5 |
| `quantidade` | Meta da semana |

### `agenda_liberacoes_cidades`

Migration: `2026_08_29_000002_create_agenda_liberacoes_cidades_table.php`  
Model: `App\Models\AgendaLiberacaoCidade`

| Coluna | Descrição |
|--------|-----------|
| `cidade_id` | FK → `cidades` |
| `ano`, `mes` | Período |
| `liberado` | boolean — mês liberado para visitas hospitalares |
| `liberado_por_id` | FK → `users`, nullable |

Unique: `(cidade_id, ano, mes)`.

---

## Backend

| Área | Service | Rotas |
|------|---------|-------|
| Metas | `App\Services\Hospital\Meta\Service` | `GET\|PUT /hospitais/{hospital}/metas` (`hospitais.metas.index\|update`) |
| Liberação | `App\Services\Visita\Agenda\Liberacao\Service` | `PUT /visitas/agenda-liberacao` (`visitas.agenda-liberacao.update`) |

Helpers auxiliares:

- `App\Helpers\User` — permissões de gestor (metas e liberação de agenda)
- `App\Helpers\Visita::statusRealizadas()` / `statusRealizadasValores()` — status contabilizáveis nas realizadas

**Metas — index:** carrega somente o hospital da rota e agrega realizadas em uma consulta SQL sobre `visitas` (por hospital, semana e opcionalmente ala).
**Metas — update:** substitui metas do hospital/mês (delete + insert); valida soma semanal, alas e escopo geográfico.
**Liberação — `mesEstaLiberado(cidadeId, ano, mes)`** e **`listarMesesLiberados(cidadeId)`** usados por `Visita\Service` e `Visita\Form\Service`.

---

## Frontend

| Página | Caminho |
|--------|---------|
| Dados e acesso às metas | `resources/js/Pages/Hospital/Index.tsx` e `resources/js/Pages/Hospital/Edit.tsx` |
| Metas de um hospital | `resources/js/Pages/Hospital/Meta/Index.tsx` |
| Liberar ou bloquear agendamento | `resources/js/Pages/Visita/Index.tsx` |

A configuração do hospital usa as abas **Dados do hospital** e **Metas de visitas**, com rotas independentes. Gestores sem permissão cadastral veem somente a aba de metas.

Em telas pequenas, a configuração de metas não usa tabelas com rolagem horizontal. A meta mensal é apresentada em um resumo compacto, e cada semana ocupa um bloco vertical com período, quantidade realizada e meta. Metas mensais e semanais possuem controles **−** e **+**, com incremento unitário e respeito aos limites da validação, sem impedir a digitação direta. Quando **Metas por ala** está ativo, cada ala é apresentada em um acordeão independente no mobile e no desktop; a primeira inicia aberta, outras podem ser abertas simultaneamente e o cabeçalho informa a soma das metas semanais distribuídas naquela ala. No desktop, as semanas abertas permanecem organizadas em tabela.

Na página de Visitas, gestores veem a situação do mês selecionado e ações explícitas **Liberar agendamento** ou **Bloquear agendamento**. A ação exige uma cidade específica; em **Todas as cidades**, a interface orienta selecionar uma cidade. Meses passados são somente leitura e um mês sem registro é considerado bloqueado.

Formulário de visita (`Form.tsx`): prop `meses_liberados` (lista `YYYY-MM`); para tipo `hospital`, valida mês no change e aplica `min`/`max` derivados dos meses liberados (`lib/visita.ts`).

---

## Testes

| Arquivo | Cobertura mínima |
|---------|------------------|
| `tests/Feature/Hospital/MetaTest.php` | auth, 403, acesso à tela, salvar meta mensal, rejeitar soma semanal divergente |
| `tests/Feature/Visita/AgendaLiberacaoTest.php` | auth, 403, listagem de meses, liberar mês futuro, rejeitar mês passado |

Validação visita + agenda: `VisitaStoreTest`, `VisitaUpdateTest`.

```bash
php artisan test tests/Feature/Hospital/MetaTest.php tests/Feature/Visita/AgendaLiberacaoTest.php
```

---

## Fora de escopo (v1)

- Metas liberarem ou bloquearem agenda automaticamente
- Metas para visitas sem hospital
- Dashboard gerencial de metas de cobertura
- Status visual OK/PENDENTE por semana
- Liberação ou bloqueio em lote para várias cidades
