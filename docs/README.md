# Documentação de Especificações e Arquitetura — Esquadrão da Alegria

Este diretório contém a documentação técnica oficial, especificações de negócio e regras de arquitetura do sistema **Esquadrão**.

---

## 📚 Mapa Geral de Especificações

### 📊 Dashboards & Indicadores

| Módulo / Feature | Descrição | Arquivo de Especificação |
|---|---|---|
| **Meu Dashboard** | Dashboard pessoal do voluntário com histórico, métricas de visitas, presenças e relatórios pendentes. | [`docs/features/dashboards/meu-dashboard/specs.md`](file:///home/bprates/Documentos/projetos/esquadrao/docs/features/dashboards/meu-dashboard/specs.md) |
| **Visitas por Participante** | Dashboard gerencial de metas mensais, compensações de saldo, reuniões, oficinas e inatividade de voluntários. | [`docs/features/dashboards/visitas-por-participante/specs.md`](file:///home/bprates/Documentos/projetos/esquadrao/docs/features/dashboards/visitas-por-participante/specs.md) |
| **Visitas por Hospital** | Dashboard de acompanhamento de atendimentos e impacto por hospital, cidade e ala. | [`docs/features/dashboards/visitas-por-hospital/specs.md`](file:///home/bprates/Documentos/projetos/esquadrao/docs/features/dashboards/visitas-por-hospital/specs.md) |
| **Home / Gerencial** | Visão inicial e agregada de métricas gerais do sistema. | [`docs/features/home/specs.md`](file:///home/bprates/Documentos/projetos/esquadrao/docs/features/home/specs.md) |

---

### 🏥 Visitas, Relatórios & Ajustes

| Módulo / Feature | Descrição | Arquivo de Especificação |
|---|---|---|
| **Visitas** | Modelo de dados, agendamento, limites de vagas, papéis, inscrição de participantes e regras de hospital. | [`docs/features/visitas/specs.md`](file:///home/bprates/Documentos/projetos/esquadrao/docs/features/visitas/specs.md) |
| **Relatórios de Visita** | Criação, acompanhamento de prazo (48h), notificação e geração de PDF. | [`docs/features/visitas/relatorios/specs.md`](file:///home/bprates/Documentos/projetos/esquadrao/docs/features/visitas/relatorios/specs.md) |
| **Ajustes de Contabilização** | Correções auditáveis de participação e aceite administrativo de relatórios enviados fora do prazo. | [`docs/features/visitas/ajustes-contabilizacao/specs.md`](file:///home/bprates/Documentos/projetos/esquadrao/docs/features/visitas/ajustes-contabilizacao/specs.md) |

---

### 📅 Eventos & Ajustes

| Módulo / Feature | Descrição | Arquivo de Especificação |
|---|---|---|
| **Ajustes de Participação em Eventos** | Correção e justificativa auditável de presenças em reuniões e oficinas. | [`docs/features/eventos/ajustes-participacao/specs.md`](file:///home/bprates/Documentos/projetos/esquadrao/docs/features/eventos/ajustes-participacao/specs.md) |

---

### 👤 Voluntários, Usuários & Ajuda

| Módulo / Feature | Descrição | Arquivo de Especificação |
|---|---|---|
| **Voluntários** | Estrutura de dados `User` vs `Voluntario`, fluxo de convites, ativação e inativação. | [`docs/features/volunt%C3%A1rio/spec.md`](file:///home/bprates/Documentos/projetos/esquadrao/docs/features/volunt%C3%A1rio/spec.md) |
| **Ajuda & Suporte** | Central de ajuda, instruções de uso e perguntas frequentes. | [`docs/features/ajuda/specs.md`](file:///home/bprates/Documentos/projetos/esquadrao/docs/features/ajuda/specs.md) |

---

## ⚙️ Regras Gerais & Diretrizes

- **Regras Arquiteturais & Estilo de Código:** Consulte [`docs/regras/geral.md`](file:///home/bprates/Documentos/projetos/esquadrao/docs/regras/geral.md).
- **Diretrizes de Revisão de PRs:** Consulte [`docs/regras/revisao_pr.md`](file:///home/bprates/Documentos/projetos/esquadrao/docs/regras/revisao_pr.md).
- **AI Reviewer (Workflow Automatizado):** Consulte [`docs/ai-reviewer.md`](file:///home/bprates/Documentos/projetos/esquadrao/docs/ai-reviewer.md).

---

## 📌 Regras Obrigatórias para Atualização da Documentação

1. **Specs são Fontes de Verdade:** Todo `specs.md` reflete o estado **final** da funcionalidade, nunca como changelog.
2. **Sem Planos Temporários no Git:** Planos de execução e especificações de tasks não devem ser commitados no repositório.
3. **Consistência de Tipos:** Alterações em modelos Eloquent ou Enums PHP devem refletir nos tipos TypeScript (`resources/js/types/index.d.ts`) e no `specs.md` correspondente.
