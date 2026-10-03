# Architecture and decisions

## Initial shape

Start with one Laravel application using Filament as the future admin UI framework and MySQL as its relational database. Docker Compose runs only the PHP/Apache application and MySQL. Keep the pilot small and avoid external services until a concrete requirement justifies them.

## Decision record

### ADR-001: Laravel application with Filament, backed by MySQL

- **Status:** Accepted for the experiment.
- **Context:** The team is evaluating agent-assisted development with a small, reviewable application.
- **Decision:** Use Laravel and Filament in one application, with MySQL as the database and Docker Compose for local/cloud parity.
- **Consequences:** Keep business logic in Laravel and UI concerns in Filament. Do not add Redis, queue workers, microservices, or orchestration platforms for this pilot. Keep the eventual interface within 10 screens.

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
