# Feature Spec — Central de Ajuda e Tutoriais Interativos

## 1. Visão Geral

A **Central de Ajuda e Tutoriais (`/ajuda`)** é uma funcionalidade interna do painel do Esquadrão da Alegria projetada para centralizar manuais operacionais, tutoriais de uso do sistema e o roteiro de implantação para a expansão de grupos nas cidades (Santa Maria, Pelotas e Porto Alegre).

## 2. Acesso e Permissões

- **Rota**: `/ajuda` (`ajuda.index`)
- **Middleware**: `auth`, `verified`
- **Público-alvo**: Voluntários, Líderes de Visita e Coordenadores/Administradores autenticados.
- **Navegação**:
  - Acessível através do menu lateral (Sidebar) e menu Mobile no `PainelLayout`.
  - Opcionalmente redirecionado via atalhos e links no rodapé interno.

## 3. Estrutura do Conteúdo

A página é dividida em três eixos principais de conhecimento:

### 3.1. Manual do Voluntário
Instruções focadas no uso diário do voluntário:
- Aceite de convite e conclusão de cadastro.
- Inscrição e cancelamento em reuniões, oficinas e treinamentos.
- Consulta ao calendário de visitas hospitalares e identificação de líderes.

### 3.2. Manual do Coordenador / Administrador
Instruções focadas no gerenciamento do grupo local:
- Envio, reencontro e cancelamento de convites via e-mail/token.
- Criação de eventos (oficinas e reuniões), lista de chamada e finalização.
- Agendamento de visitas hospitalares, cadastro de hospitais/alas e gestão de escalas.
- Criação e download dos Relatórios de Visita em PDF.

### 3.3. Guia de Implantação de Grupos
Estratégia e checklist de rollout para a expansão em 3 cidades:
- **Fase 1**: Piloto em **Santa Maria** (validação inicial de convites, escalas e relatórios PDF).
- **Fase 2**: Expansão para **Pelotas** (onboarding e hospitais locais).
- **Fase 3**: Expansão para **Porto Alegre** (onboarding e hospitais da região metropolitana).
- **Checklist de Ativação**: Passos formais para liberação de um novo grupo/cidade no sistema.

## 4. Recursos Interativos da Interface

- **Busca Global em Tempo Real**: Filtro dinamizado por palavras-chave (tags, títulos, descrições).
- **Abas de Categoria**: Alternância rápida entre Todos os Tutoriais, Voluntário, Coordenador e Implantação.
- **Componente Accordion**: Passos expansíveis contendo numeração sequencial, descrição técnica e caixa de destaque com "Dicas de Uso".
- **Links Rápidos de Ação**: Botões diretos para páginas operacionais (ex: "Ver Eventos", "Minhas Visitas", "Gerenciar Voluntários").
