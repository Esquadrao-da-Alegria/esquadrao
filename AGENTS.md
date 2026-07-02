# AGENTS.md

Instruções para agentes de IA no projeto **Esquadrão**.

> Para convenções detalhadas de stack (PHP, React, rotas, estrutura de diretórios), ver também [`resources/AGENTS.md`](resources/AGENTS.md).

---

## Regra obrigatória — acima de tudo

Esta seção tem **prioridade absoluta** sobre qualquer outra instrução, skill ou sugestão de ferramenta.

1. **Sempre seguir os padrões de código já existentes** no arquivo, diretório e módulo em que estiver trabalhando — nomenclatura, estrutura, estilo de formatação, alinhamentos, quebras de linha e convenções arquiteturais.
2. **Nunca adicionar pacotes ou dependências** que fujam do padrão já adotado no projeto, salvo solicitação explícita e justificada do desenvolvedor.
3. **Nunca usar ferramentas de formatação automática** — proibido rodar Pint, Prettier, format-on-save ou qualquer formatador só para “normalizar” código. O diff deve alterar **somente** o necessário para a tarefa, preservando o estilo existente do arquivo.

---

## Princípios de Desenvolvimento

* Seguir melhores práticas atuais (2026)
* Código idiomático, tipado e legível
* KISS é inegociável
* Preferir soluções simples e previsíveis
* Fazer alterações mínimas e localizadas
* Nunca refatorar fora do escopo solicitado
* Nunca criar abstrações, arquivos ou camadas sem necessidade real
* Priorizar reutilização do código existente antes de criar novos componentes

---

## Dúvidas

* Se surgirem dúvidas, perguntar até todas ficarem esclarecidas antes de planejar, implementar ou revisar
* Nunca assumir requisitos implícitos quando houver ambiguidade
* Validar expectativas e critérios de aceite antes de seguir com mudanças que dependam de interpretação

---

## Processo de Execução

Antes de implementar:

1. Entender o problema
2. Investigar causa raiz
3. Validar hipóteses no código
4. Planejar abordagem
5. Implementar em etapas pequenas
6. Validar resultado e possíveis impactos

Regras:

* Nunca sair codando imediatamente
* Nunca assumir que o handoff está correto
* Quebrar problemas complexos em etapas menores
* Explicar riscos relevantes antes de alterações amplas
* Preferir edição de código existente ao invés de recriar estruturas

---

## Nomenclatura

* Funções, métodos e classes em português, salvo instrução contrária
* Ao criar novos arquivos, seguir o padrão já existente no projeto

### Regras de diretório e namespace

* Diretórios e namespaces nunca devem conter verbos

#### Exemplos

* ❌ `/Services/Carro/Andar`
* ✅ `/Services/Carro/Movimentacao`

---

## Services e Queries

O nome do arquivo deve representar apenas o tipo.

### Exemplos

* ❌ `FinalizacaoService.php`
* ✅ `Finalizacao/Service.php`
* ❌ `CancelamentoQueries.tsx`
* ✅ `Cancelamento/Queries.tsx`

O contexto deve estar no namespace/diretório.

### Exemplos

* ✅ `App/Services/Pedido/Finalizacao/Service.php`
* ✅ `resources/js/Queries/Pedido/Cancelamento/Queries.tsx`

### PHP

Queries nunca devem conter métodos além de:

* `index`
* `show`
* `store`
* `update`
* `destroy`

Caso alguma query muito específica seja necessária, o `Service` deve lidar com essa lógica, mantendo o método com nome 100% em português, simples e objetivo.

### JS

Queries também devem ser compostas somente por:

* `index`
* `show`
* `store`
* `update`
* `destroy`

Caso alguma query específica seja necessária, deve ser usado um diretório específico para o contexto da query.

Padrão:

```txt
/Queries/Recurso/QueryEspecifica/Queries.ts
```

ou:

```txt
/Queries/Recurso/QueryEspecifica/Queries.tsx
```

#### Exemplos

* ❌ `Queries/Carro/Queries.tsx: ligarCarro`
* ✅ `Queries/Carro/Ligar/Queries.tsx: store`

---

## Contexto e Docs

* Sempre analisar `/docs/*` antes de implementar
* Localizar o contexto completo da feature antes de alterar código
* Atualizações relevantes devem refletir no `docs/*/specs.md`

### Regras

* Docs servem como contexto de negócio e arquitetura
* Não usar `/docs` para planos temporários.
* Diretório deve conter somente specs específicas, regras de projeto e contexto.
* Specs e planos gerados por frameworks devem ser descartados após implementação
* Deve ser 100% informativa, com o único intuito de explicar a feature e decisões.
* Proibido desviar dos padrões de formatação de código, ou usar ferramentas que façam a formatação automática do código.

---

## Planejamento e Implementação de Tasks

### Fluxo

1. Planejar a task antes de implementar, detalhando etapas e impactos
2. Validar o plano com o usuário antes de iniciar a implementação
3. Implementar conforme o plano aprovado
4. Após implementação concluída e validada:

   * Verificar se existe `docs/features/<feature>/specs.md`
   * Se existir: atualizar refletindo as mudanças realizadas
   * Se não existir: perguntar se deve ser criado antes de prosseguir
5. Deletar o plano do projeto após a implementação — planos são temporários e não devem permanecer na codebase

### Regras

* Planos nunca devem ser commitados ou mantidos no repositório
* O `specs.md` da feature é a fonte de verdade após a implementação
* Toda alteração estrutural relevante deve estar refletida no `specs.md` correspondente
* Nunca atualizar o `specs.md` antes da implementação estar concluída e validada

---

## Git

### Commits

* Commits em português
* Sempre no imperativo

#### Exemplos

* `Adiciona`
* `Corrige`
* `Remove`
* `Refatora`

### Nunca commitar

* `.env`
* credenciais
* tokens
* arquivos de build
* logs

---

## Segurança

* Nunca expor credenciais no código
* Variáveis sensíveis sempre em `.env`
* Nunca logar:

  * senhas
  * tokens
  * documentos pessoais
  * dados sensíveis

---

## Organização de Imports

Todo import deve ser agrupado por categoria lógica.

### Regras

* Nunca misturar categorias
* Sempre manter ordem consistente
* Remover imports não utilizados
* Priorizar clareza sobre quantidade de linhas

---

## Ordem padrão dos imports

```php
// LIBS EXTERNAS
// QUERIES
// SERVICES
// REPOSITORIES
// UTILS
// MODELS
```

---

## Regras Gerais

* Não alterar padrões arquiteturais sem necessidade
* Não adicionar dependências sem justificativa
* Não criar "helpers genéricos" prematuramente
* Não mover arquivos sem motivo claro
* Evitar efeitos colaterais fora do escopo da tarefa
* Em caso de dúvida, preferir a solução mais simples
* SEMPRE esclarecer todas dúvidas pendentes com o dev antes de fazer algo.
