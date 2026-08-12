# Visão Geral — especificação para desenvolvedores

A Visão Geral é a porta de entrada autenticada do painel na rota `dashboard`. Seu foco é operacional e imediato: agenda pessoal, pendências acionáveis, avisos e um resumo compacto. Histórico, filtros, metas detalhadas, compensações e gráficos permanecem no Meu Dashboard.

## Acesso e privacidade

- Rota: `GET /dashboard` (`dashboard`).
- Middlewares: `auth` e `verified`.
- A requisição não aceita identificador de voluntário.
- Controller e Service recebem exclusivamente o usuário autenticado.
- Atividades, pendências e métricas pertencem somente ao usuário autenticado.
- Contas administrativas sem voluntário vinculado mantêm acesso, recebem estado informativo e não recebem dados pessoais simulados.

## Navegação

O submenu **Dashboards** possui esta ordem:

1. **Visão Geral**, sempre visível e apontando para `/dashboard`;
2. **Meu Dashboard**, sempre visível conforme o Gate pessoal;
3. dashboards gerenciais, exibidos somente quando a permissão compartilhada correspondente for verdadeira.

O logotipo do painel também aponta para a Visão Geral. As mesmas regras são aplicadas no menu desktop e mobile.

## Camadas

| Camada | Caminho |
|---|---|
| Controller | `App\Http\Controllers\Web\Dashboard\Controller` |
| Service | `App\Services\Dashboard\Service` |
| Query | `App\Queries\Dashboard\Queries` |
| Página | `resources/js/Pages/Dashboard.tsx` |
| Tipos | `resources/js/types/dashboard-home.ts` |
| Avisos | `config/dashboard.php` |

## Saudação

- Usa `voluntario.nome_completo`, com fallback para `user.name`.
- Exibe a cidade-base somente quando disponível.
- A orientação prioriza pendência acionável, próxima atividade e, por último, acesso às atividades disponíveis.
- Não utiliza placeholders ou textos de construção.

## Próximas atividades

O bloco combina e ordena cronologicamente até seis atividades:

- visitas futuras com status `agendada` e participação `confirmado`;
- reuniões e oficinas futuras com status `agendado` e inscrição `inscrito`.

Cada item contém categoria, título, início, fim, local, cidade, situação e URL de detalhes. Eventos apontam para `eventos.show`. Visitas apontam para o calendário no mês correspondente com `visita_id`; o calendário abre o modal somente quando a visita pertence ao resultado autorizado daquele mês e cidade.

## Pendências de relatório

São exibidas somente visitas encerradas com participação confirmada e sem relatório aplicável:

- para paisana, o relatório deve ser do próprio participante;
- para palhaço, um relatório de qualquer palhaço confirmado resolve a pendência do grupo;
- relatório de paisana não resolve a pendência dos palhaços;
- visitas canceladas e participações não confirmadas não geram pendência.

O prazo é calculado com `Visita\Relatorio\Prazo\Service::HORAS`, atualmente 48 horas após o fim da visita:

- mais de 12 horas restantes: `em_prazo`;
- até 12 horas restantes: `prazo_proximo`;
- prazo encerrado: `atrasado`.

O limiar de 12 horas altera somente o destaque visual. Não altera a regra persistida de relatório fora do prazo.

Convites de atividade e cadastro obrigatório incompleto não são apresentados enquanto não existir fluxo acionável e regra de obrigatoriedade no domínio.

## Avisos

Os avisos da primeira versão são administrados por deploy em `config/dashboard.php`. Cada aviso pode definir cidade, início, expiração, tipo, link e prioridade.

O Service entrega avisos gerais e avisos da cidade-base, respeita o período e limita o bloco a três itens. Uma falha de leitura gera lista vazia e log sem dados pessoais; os demais blocos continuam disponíveis.

## Resumo pessoal

- visitas válidas no mês atual seguem a mesma regra coletiva para palhaços e pessoal para paisanas, incluindo aceite administrativo de relatório atrasado;
- oficinas e reuniões frequentadas usam eventos finalizados com presença `presente` no semestre atual;
- a meta reutiliza `Dashboard\Visita\Participante\Meta\Service`;
- apoio recebe isenção; ausência de classificação segura recebe dados insuficientes;
- contas sem vínculo recebem `null`, nunca zero enganoso.

## Estados vazios e linguagem

- Sem agenda: orienta a consultar atividades disponíveis.
- Sem pendências: informa que está tudo em dia.
- Sem avisos: informa que não há avisos no momento.
- Sem métricas calculáveis: informa que ainda não há dados suficientes.
- Sem vínculo: explica a condição da conta sem simular informações pessoais.

A interface não usa linguagem disciplinar, comparação entre voluntários, cards vazios, valores fabricados ou placeholders.

## Interface

- Layout mobile first e identidade visual âmbar do painel.
- Agenda e pendências precedem o resumo.
- Estados de prazo possuem texto e cor.
- Datas chegam em ISO 8601 e são formatadas em português na interface.
- Não existe biblioteca visual adicional.

## Garantias

- Consultas são somente leitura e limitadas no banco.
- Relatórios são avaliados por subconsultas por visita, sem duplicar atividades.
- Nenhum model completo é enviado pelo contrato da home.
- Usuário não seleciona outro voluntário ou cidade para os blocos pessoais.
- Avisos municipais não vazam para outra cidade.
- A rota e o nome `dashboard` permanecem compatíveis com login e verificação de e-mail.
