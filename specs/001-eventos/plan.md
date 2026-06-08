# Implementation Plan: Sistema de Eventos

**Branch**: `dev-mauro` | **Date**: 2026-06-03 | **Spec**: specs/001-eventos/spec.md

**Input**: Feature specification from `specs/001-eventos/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Adicionar o módulo de eventos ao sistema existente, com cadastro de oficinas e reuniões, listagem filtrável, detalhes de evento e fluxo de inscrições/presenças. A implementação deve aproveitar a arquitetura Laravel + Inertia atual, reutilizar `User` e `Cidade`, e seguir o padrão Service/Query/Controller observado nas demais áreas do app.

## Technical Context

**Language/Version**: PHP 8.x com Laravel 10

**Primary Dependencies**: Laravel, Inertia, React/TSX, Fortify, Eloquent ORM

**Storage**: Banco relacional MySQL/MariaDB via Eloquent

**Testing**: PHPUnit com testes de Feature e Unit do Laravel

**Target Platform**: Aplicação web servida em Linux/PHP

**Project Type**: Aplicação web monolítica Laravel + Inertia

**Performance Goals**: consultas de eventos filtradas no backend; evitar carregar listas completas sem necessidade; manter consultas a cidades/participantes sem N+1

**Constraints**: reutilizar autenticação, autorização, `User` e `Cidade`; seguir o padrão de controllers, services, queries e requests já presente no repositório; suportar filtros por tipo/cidade/semestre/status sem expor todo o catálogo ao frontend

**Scale/Scope**: sistema de ONG com centenas de eventos e inscrições; foco em administrador/responsável e voluntário, sem multitenancy ou alta escala além do uso interno do app

## Constitution Check

A constituição em `.specify/memory/constitution.md` está em branco/placeholder e não define gates concretos. Este plano segue o padrão atual do repositório e presume que os critérios de revisão serão validados nas políticas de PR existentes.

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)
<!--
  ACTION REQUIRED: Replace the placeholder tree below with the concrete layout
  for this feature. Delete unused options and expand the chosen structure with
  real paths (e.g., apps/admin, packages/something). The delivered plan must
  not include Option labels.
-->

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── Web/EventoController.php
│   ├── Requests/
│   │   └── Web/Evento/
│   │       ├── StoreRequest.php
│   │       └── UpdateRequest.php
│   └── ...
├── Models/Evento.php
├── Models/EventoParticipante.php
├── Queries/Evento/Queries.php
├── Services/Evento/Service.php
├── Services/Evento/Form/Service.php
└── ...

resources/js/Pages/Evento/
├── Index.tsx
├── Create.tsx
├── Edit.tsx
├── Show.tsx
├── Presencas.tsx
└── Dashboard.tsx

routes/web.php
database/migrations/
tests/Feature/
tests/Unit/
```

**Structure Decision**: utilizar a arquitetura Laravel existente. O recurso de eventos seguirá o mesmo layout do repositório atual com modelos, serviços, queries e controllers no backend, requests específicos para validação, e páginas Inertia TSX no frontend.

## Complexity Tracking

Nenhuma violação de constituição identificada; o recurso usa o padrão já presente no projeto, sem necessidade de uma nova camada arquitetural.
