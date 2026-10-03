# Architecture and decisions

## Initial shape

Use one Laravel application with Filament as the admin UI framework and MySQL as its relational database. Docker Compose runs only the PHP/Apache application and MySQL. Keep the pilot small and avoid external services until a concrete requirement justifies them.

## Decision record

### ADR-001: Laravel 13 application with Filament 5 and Livewire 4, backed by MySQL 8.4

- **Status:** Accepted for the experiment.
- **Context:** The team is evaluating agent-assisted development with a small, reviewable application.
- **Decision:** The Gate 1 target baseline is PHP 8.4, Laravel 13, Filament 5, Livewire 4, MySQL 8.4 LTS, Composer 2, and Docker Compose. The exact resolved Composer patch versions live in `composer.lock`.
- **Consequences:** Keep business logic in Laravel and UI concerns in Filament. Do not add Redis, queue workers, microservices, or orchestration platforms for this pilot. Keep the eventual interface within 10 screens. Gate 1 runtime certification results are recorded in `.agent/STATE.md`.

### ADR-002: Provider-neutral project instructions

- **Status:** Accepted.
- **Context:** The repository must work with Codex and Claude Code, in cloud and local VS Code environments.
- **Decision:** Keep shared behavior in `AGENTS.md`, `policies/`, `skills/`, `scripts/`, and `docs/`; keep `CLAUDE.md` and any future provider folders as thin adapters.
- **Consequences:** Essential policy and verification cannot depend on a single AI vendor.

### ADR-003: Compose for the development runtime

- **Status:** Accepted.
- **Context:** Developers need a repeatable `docker compose up -d` workflow with Laravel and MySQL.
- **Decision:** Define only `app` and `mysql` services, with a named volume for MySQL data and a health check for startup ordering.
- **Consequences:** Application dependencies are locked by Composer. Local `.env` and secrets stay outside Git. Verification uses a deterministic script.

Record future material decisions here with status, context, decision, and consequences. Do not rewrite a consolidated migration to express a later schema change; add a new migration.
