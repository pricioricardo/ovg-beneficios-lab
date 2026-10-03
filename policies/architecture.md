# Architecture policy

- Keep this pilot as one Laravel application with Filament and MySQL.
- Keep the eventual application at 10 screens or fewer.
- Do not add Redis, queues, Kubernetes, microservices, or unrelated infrastructure without an explicit, documented requirement.
- Put business behavior in application/domain code and keep Filament focused on presentation and interaction.
- Document material architecture choices in `docs/ARCHITECTURE.md` before or with the change.
- Keep shared instructions provider-neutral; provider folders may contain integrations only.
- Treat consolidated migrations as immutable and use additive migrations for later schema changes.
