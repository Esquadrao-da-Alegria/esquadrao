# Dashboard de visitas por hospital — especificação para desenvolvedores

Dashboard gerencial para acompanhar a atuação do Esquadrão por cidade, hospital e ala. A visita é a fonte institucional da métrica e permanece contabilizada mesmo sem relatório.

## Acesso e escopo

- `administrador` e `coordenador_geral`: visão global e filtro por cidade.
- Perfis globais com cidade-base abrem inicialmente no recorte dessa cidade, mas podem selecionar outra cidade ou **Todas as cidades**.
- Contas administrativas de suporte sem cidade-base abrem na visão global.
- `coordenador_local`: somente a cidade de `user.voluntario.cidade_base_id`.
- Coordenador local sem cidade base: acesso bloqueado com HTTP 403.
- `diretor` e demais cargos: sem acesso.
- Múltiplos cargos concedem a união das permissões; um cargo global prevalece sobre o escopo local.
- A rota usa o Gate `dashboard.visitas_por_hospital`. A restrição municipal também é aplicada aos filtros e consultas no backend.

## Rota e camadas

| Camada | Caminho |
|---|---|
| Rota | `GET /dashboards/visitas-por-hospital` (`dashboards.visitas-por-hospital`) |
| Controller | `App\Http\Controllers\Web\Dashboard\Visita\Hospital\Controller` |
| Request | `App\Http\Requests\Web\Dashboard\Visita\Hospital\IndexRequest` |
| Service | `App\Services\Dashboard\Visita\Hospital\Service` |
| Query | `App\Queries\Dashboard\Visita\Hospital\Queries` |
| Página | `resources/js/Pages/Dashboard/Visita/Hospital/Index.tsx` |

## Filtros

- `mes_inicio` e `mes_fim`: intervalo mensal inclusivo no formato `YYYY-MM`.
- Padrão: janeiro do ano atual até o mês atual.
- `cidade_id`: opcional para escopo global e fixo para coordenador local.
- `visao_global`: registra a escolha explícita por **Todas as cidades**, diferenciando-a do carregamento inicial que aplica a cidade-base por padrão.
- `hospital_id`: aceito somente quando pertence à cidade selecionada.
- `ala_id`: aceito somente quando pertence ao hospital selecionado.
- Alterar cidade na interface limpa hospital e ala; alterar hospital limpa ala.
- Os controles seguem o padrão compacto da listagem de voluntários e atualizam a consulta diretamente, preservando estado e posição da página. Não existe botão adicional para aplicar os filtros.

## Contabilização

1. Considerar visitas cujo `inicio_em` esteja no intervalo e `status = realizada`.
2. Contabilizar a visita uma única vez, independentemente da quantidade de relatórios.
3. Considerar somente participações com `status_participacao = confirmado`.
4. A média de participantes é `participações confirmadas / visitas`, incluindo visitas com zero confirmações.
5. A ala do agrupamento é `visitas.ala_unidade_id`. Em hospitais com alas, valor nulo aparece como **Sem ala informada**.
6. Hospitais sem alas permanecem somente no nível do hospital; o detalhamento informa que não existem alas cadastradas.

A visita realizada é contabilizada institucionalmente mesmo sem relatório. Visitas agendadas, pendentes de relatório, canceladas ou com outro status não entram. Regra confirmada pela coordenação em 10/08/2026.

## Impacto estimado

`pessoas_impactadas` pertence aos relatórios e pode existir em mais de um relatório da mesma visita. Enquanto não houver uma regra institucional definitiva:

1. calcular `AVG(pessoas_impactadas)` por visita, ignorando valores nulos;
2. somar as médias das visitas para o indicador do conjunto;
3. apresentar o resultado como **Impacto estimado**, nunca como total exato;
4. manter visitas sem impacto em todas as demais métricas e indicar **Impacto não informado** na listagem.

## Consultas

Participantes e impactos são agregados em subconsultas por `visita_id` antes de serem relacionados à consulta-base. Isso impede produto cartesiano entre participantes e relatórios. Indicadores, evolução e agrupamentos são calculados no banco; somente a sequência de meses sem registros é completada no Service.

O detalhamento de visitas é paginado em 15 registros e inclui data, ala, status, participantes confirmados e impacto estimado.

## Interface

- Mantém a identidade visual âmbar do painel.
- Usa filtros nativos e encadeados, cartões de indicadores, barras mensais, tabela de hospitais e detalhamento responsivo.
- Não adiciona biblioteca de gráficos.
- Oferece estados vazios e textos explícitos para ausência de relatório, impacto ou alas.

## Testes

Os testes cobrem autorização dos perfis, escopo municipal, visitas sem relatório, canceladas, múltiplos relatórios, participações confirmadas, impacto estimado, intervalo mensal e filtros encadeados.

## Fora de escopo

- Exportação.
- Metas ou previsões.
- Edição de visitas e relatórios.
- Consolidação institucional definitiva de pessoas impactadas.
