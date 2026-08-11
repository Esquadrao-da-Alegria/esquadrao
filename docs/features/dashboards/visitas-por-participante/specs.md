# Dashboard de participação — especificação para desenvolvedores

Dashboard gerencial de apoio à decisão. Os indicadores organizam dados objetivos, mas nunca alteram o cadastro, aplicam advertência, afastamento ou desligamento.

## Acesso

- `administrador` e `coordenador_geral`: escopo global.
- Perfis globais com cidade-base abrem inicialmente no recorte dessa cidade, podendo selecionar outra cidade ou **Todas as cidades**.
- Contas administrativas de suporte sem cidade-base permanecem na visão global.
- `coordenador_local`: voluntários cuja cidade-base seja a sua; coordenador sem cidade recebe HTTP 403.
- `diretor` e demais cargos: sem acesso ao dashboard.
- Atividades em outras cidades permanecem no histórico do voluntário autorizado.
- Rota: `dashboards.visitas-por-participante`; detalhe: `dashboards.visitas-por-participante.show`.

## Classificação provisória da atuação

A fonte atual é `App\Services\Dashboard\Visita\Participante\Meta\Service`:

1. `apoio` ou `psicologia`: isento da meta de visitas;
2. `administrador`, `diretor`, `coordenador_geral` ou `coordenador_local`: administrativo;
3. `artista` ou `voluntario`: meta de visitas;
4. nenhum cargo reconhecido: dados insuficientes.

Um voluntário que não realiza visitas deve possuir a tag/cargo `apoio`. A classificação por cargo é provisória. Quando existir tipo de atuação explícito, ele deverá substituir somente o método `Meta\Service::tipo`, mantendo o restante do dashboard.

Administrativos possuem meta institucional de oito horas mensais, mas o sistema ainda não registra horas. Eles aparecem como **Dados insuficientes**, sem sinalização negativa.

## Visita válida

Uma visita conta no máximo uma vez por participante quando:

- possui `status = realizada`;
- a participação está `confirmado`;
- a regra de relatório correspondente ao `tipo_participacao` foi atendida no prazo.

Para `tipo_participacao = palhaco`, um relatório no prazo escrito por qualquer palhaço confirmado da mesma visita valida a participação de todos os palhaços confirmados. O relatório de um paisana não valida o grupo de palhaços.

Para `tipo_participacao = paisana`, o próprio participante precisa escrever um relatório no prazo. O relatório de outro paisana ou de um palhaço não valida sua participação.

O tipo registrado na participação da visita prevalece sobre os cargos permanentes do usuário. Múltiplos relatórios não duplicam a visita. Pendências e atrasos aparecem no histórico. Regra confirmada pela coordenação em 10/08/2026.

O prazo é centralizado em `App\Services\Visita\Relatorio\Prazo\Service`, atualmente 48 horas após `visita.fim_em`. Mudanças futuras devem ocorrer nesse serviço e continuar preenchendo corretamente `fora_do_prazo`.

## Meta e compensação

- Meta: duas visitas válidas por mês.
- Excedente máximo transferível: duas visitas.
- Crédito e débito valem somente no mês imediatamente seguinte.
- Excedente do mês seguinte compensa primeiro o débito anterior.
- Crédito anterior cobre falta do mês atual.
- Valores não utilizados expiram após essa janela.

O cálculo e sua trilha explicável ficam em `Compensacao\Service`. Se o Regimento mudar, alterar esse serviço, seus testes e esta seção. A interface recebe meta, visitas, saldo, crédito utilizado, débito compensado, transferências e expirações por mês.

## Reuniões e oficinas

- Denominador: eventos finalizados do tipo na cidade-base durante o período/semestre consultado.
- Numerador: eventos em que `evento_participantes.presenca = presente`.
- Eventos cancelados não entram.
- Ausência de eventos ou presenças ainda não registradas produz dados insuficientes.
- Meta: 50% em cada tipo separadamente. Percentual abaixo de 50% gera atenção, sem produzir sozinho **Requer análise**.
- Reuniões e oficinas não se compensam: em seis reuniões e seis oficinas, são necessárias ao menos três presenças em cada conjunto.
- Presenças em outras cidades aparecem no detalhe, mas não aumentam o denominador da cidade-base.

Se assembleias forem modeladas, sua inclusão deve ocorrer na seleção central de eventos da Query, não no React.

## Inatividade e situação

Atividade documentada é visita válida, reunião presente ou oficina presente. Sessenta dias sem atividade gera **Requer análise** para perfis mensuráveis.

Precedência:

1. dados insuficientes;
2. isento;
3. requer análise;
4. compensação pendente;
5. atenção;
6. dentro da meta.

Cores sempre acompanham texto. Nunca usar “irregular”, “deve ser afastado” ou linguagem conclusiva.

## Filtros e interface

- Período por mês, semestre ou ano.
- Busca por nome ou e-mail, cidade, cargo, tipo de atuação, situação e atividade documentada.
- A busca e os seletores atualizam a consulta sem exigir uma ação adicional. Período e cidade permanecem visíveis; filtros menos frequentes ficam na área expansível **Mais filtros**.
- O resumo possui somente quatro indicadores prioritários: acompanhados, dentro da meta, requer análise e dados insuficientes. Os demais valores continuam disponíveis por participante.
- A tabela paginada concentra voluntário, total de eventos documentados, situação e ações. **Eventos** agrega visitas válidas e presenças em reuniões e oficinas somente para simplificar a leitura; cada componente permanece separado no cálculo.
- A ação **Ver detalhes** abre modal com meta, saldo, visitas, reuniões, oficinas, relatórios e inatividade. O histórico completo continua acessível pelo modal.
- Identidade visual âmbar do painel, responsiva e sem biblioteca adicional.
- O detalhe apresenta meses, saldos, exclusões, relatórios e presenças.

## Pontos reservados para dados futuros

| Dado futuro | Ponto de integração |
|---|---|
| Tipo de atuação | `Meta\Service::tipo`; substitui inferência por cargos |
| Núcleos | filtros, tabela e configuração do Meta Service |
| Horas administrativas | indicadores mensais, tabela e detalhe; substitui “Dados insuficientes” |
| Afastamentos | suspensão proporcional das metas e linha do tempo |
| Justificativas | explica ausências e pode impedir classificação negativa |
| Histórico de status | seleção de voluntários ativos em cada período |
| Férias | ajuste ou suspensão da meta mensal no Service de Compensação |
| Assembleias | conjunto de eventos de reunião na Query |
| Novo prazo de relatório | `Visita\Relatorio\Prazo\Service` |
| Nova compensação | `Dashboard\Visita\Participante\Compensacao\Service` |

Até essas modelagens existirem, a seção **Afastamentos, justificativas e horas administrativas** do detalhe exibe “Dados ainda não disponíveis” e não gera penalização.

## Garantias

- Nenhum Job é criado.
- Nenhum Service deste dashboard executa `update`, `save`, `attach` ou altera status.
- Consultas são feitas em lotes, sem N+1 por voluntário.
- O dashboard é exclusivamente informativo e de apoio à análise humana.
