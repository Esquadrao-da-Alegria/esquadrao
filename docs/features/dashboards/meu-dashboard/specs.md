# Meu dashboard — especificação para desenvolvedores

Dashboard individual e informativo da participação do voluntário. A página não altera cadastro, aplica advertência ou conclui que alguém está irregular.

O histórico identifica visitas validadas por aceite administrativo auditável de relatório atrasado, mantendo a regra coletiva para palhaços/artistas e individual para paisanas.

Inscrições e presenças de eventos corrigidas administrativamente aparecem nos mesmos indicadores, pois o ajuste atualiza a participação original e conserva sua auditoria separadamente.

## Acesso e privacidade

- Todo usuário autenticado e verificado acessa a rota `dashboards.meu`.
- Contas administrativas de suporte sem voluntário vinculado recebem um estado vazio e informativo. Elas não recebem dados pessoais de outro voluntário.
- A rota usa `Dashboard\Meu\Controller` e o Gate `dashboard.meu`.
- A rota inicial `dashboard` permanece uma página neutra e independente de vínculo com voluntário. Sua definição definitiva pertence a uma task separada, permitindo que contas administrativas de suporte entrem no sistema.
- O Controller entrega exclusivamente `$request->user()` ao Service.
- Não existe parâmetro de rota ou filtro para selecionar outro voluntário. Parâmetros desconhecidos são ignorados pela validação.
- Administradores e coordenadores veem somente os próprios dados nesta página.
- Nomes de outros voluntários aparecem apenas como companheiros de visitas válidas compartilhadas.

## Camadas

| Camada | Caminho |
|---|---|
| Controller | `App\Http\Controllers\Web\Dashboard\Meu\Controller` |
| Request | `App\Http\Requests\Web\Dashboard\Meu\IndexRequest` |
| Service | `App\Services\Dashboard\Meu\Service` |
| Query | `App\Queries\Dashboard\Meu\Queries` |
| Página | `resources/js/Pages/Dashboard/Meu.tsx` |
| Modal | `components/Painel/Dashboard/Meu/Historico/Modal/Show.tsx` |

## Regras compartilhadas

O dashboard reutiliza as fontes do dashboard gerencial de participação:

- `Dashboard\Visita\Participante\Meta\Service`: tipo de atuação, meta de duas visitas e meta de 50% de presença por tipo de evento;
- `Dashboard\Visita\Participante\Compensacao\Service`: créditos, débitos e janela de compensação;
- `Visita\Relatorio\Prazo\Service`: prazo de relatório preenchido em `visitas_relatorios.fora_do_prazo`.

Uma visita é válida quando a participação está confirmada e:
- o status é `contabilizada` (já validada previamente); ou
- o status é `realizada` e a regra de relatório foi atendida no prazo (ou aceita por ajuste administrativo auditável). Para palhaços, um relatório de qualquer palhaço confirmado valida o grupo; para paisanas, o relatório deve ser pessoal. O `tipo_participacao` daquela visita define a regra, independentemente dos cargos permanentes. Múltiplos relatórios não duplicam a visita.

Visitas com status `nao_contabilizada` ou `cancelada` não contam como válidas para a meta. Regra confirmada pela coordenação em 10/08/2026.

## Classificação de atuação

Todo usuário com cargo vinculado recebe meta de visitas, inclusive administradores, diretores, coordenadores e psicologia. A única exceção é quem possui a tag/cargo `apoio`, que prevalece em combinações de múltiplos cargos e isenta o voluntário da meta. Usuários sem qualquer cargo permanecem como dados insuficientes.

## Filtros

- Mês, semestre, ano ou período personalizado.
- Período personalizado limitado a 24 meses no Request e antecipado por validação visual na página.
- Cidade limitada às cidades em que o usuário possui visitas ou eventos.
- Tipo de atividade: visitas, reuniões ou oficinas.
- Filtros usuais atualizam automaticamente; datas personalizadas usam **Consultar período** para evitar requisições com intervalo incompleto.
- Cidade separa as atividades exibidas. Meta e compensação continuam pessoais e globais, pois intercâmbios também integram a meta do voluntário.

## Indicadores e visualizações

O topo mantém quatro informações prioritárias:

- visitas válidas;
- eventos documentados;
- saldo ou aplicabilidade da meta;
- relatórios pendentes.

Detalhes complementares apresentam evolução mensal, compensação, presença, impacto estimado, hospitais, cidades, companheiros e última atividade.

### Impacto estimado

Usa a mesma regra do dashboard por hospital: calcular `AVG(pessoas_impactadas)` por visita, ignorando nulos, e somar as médias. A página sempre usa o termo **Impacto estimado**. Visitas sem esse dado permanecem nas demais métricas.

### Presença

- Denominador: reuniões ou oficinas finalizadas da cidade-base no período.
- Numerador: eventos em que o usuário possui `presenca = presente`.
- Eventos com presença institucional ainda incompleta deixam o percentual indisponível.
- A lista **Atividades consideradas** explica a inclusão ou a espera por dados.

### Companheiros

Exibe até cinco voluntários com mais visitas válidas compartilhadas. É uma informação de convivência, sem posição, prêmio, comparação de desempenho ou exposição pública.

## Histórico

O histórico une participações em visitas, reuniões e oficinas, ordena por data e pagina em 12 registros. O resumo mobile apresenta atividade, data, cidade e situação. O modal detalha local, ala, participação, relatório, impacto e motivo da contabilização.

Motivos de visita não contabilizada:

- visita cancelada;
- participação não confirmada;
- relatório pessoal do paisana pendente ou fora do prazo;
- relatório do grupo de palhaços pendente ou fora do prazo;
- visita ainda não marcada como realizada.

Relatório pendente oferece acesso à rota de criação, cuja autorização real permanece no Service de relatórios.

## Próximas atividades

- Visitas: futuras, não canceladas e com participação confirmada.
- Reuniões e oficinas: futuras, agendadas e com inscrição `inscrito`.
- Ações apontam para as páginas existentes de visita ou evento; nenhuma autorização é reproduzida no React.

## Linguagem

Mensagens são orientativas e calculadas somente para exibição. Termos como “irregular”, “deve ser afastado” e “deve ser desligado” são proibidos. Situações dependentes de contexto orientam o voluntário a conferir os dados ou conversar com a coordenação.

## Pontos reservados para dados futuros

| Dado futuro | Ponto de integração |
|---|---|
| Tipo explícito de atuação | `Dashboard\Visita\Participante\Meta\Service::tipo` |
| Horas administrativas | indicador de meta, evolução e histórico pessoal |
| Núcleos | classificação da meta e filtro de atividade |
| Afastamentos e férias | cálculo mensal de meta e compensação |
| Justificativas | motivo no histórico e mensagens orientativas |
| Histórico de cargos/status | classificação aplicável em cada mês |
| Assembleias | Query de eventos e cálculo de presença |
| Nova regra de impacto | subconsulta agregada `impactos` em `Dashboard\Meu\Queries` |

Até essas modelagens existirem, nenhum dado ausente produz penalidade ou alteração cadastral.

## Garantias

- O Service e a Query são somente leitura.
- Nenhum Job, evento de domínio ou atualização cadastral é disparado.
- Relatórios são agregados antes do relacionamento com visitas, evitando duplicação e produto cartesiano.
- O dashboard não recebe um identificador de voluntário.
- A interface é mobile first, mantém a identidade âmbar e não adiciona biblioteca.
