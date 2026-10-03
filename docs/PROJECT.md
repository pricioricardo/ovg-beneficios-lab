# Project brief

OVG Benefícios Lab is a small experiment to assess agent-assisted software development as a possible future alternative to the team's current low-code approach. It is a fictional laboratory inspired by public descriptions of OVG social programs; it does not reproduce internal systems and does not assume access to them.

## Product and technical boundaries

- Use synthetic data only; never import or retain real beneficiary information.
- Keep the eventual application to no more than 10 screens.
- Use Laravel, Filament, MySQL, Docker, Git, and automated tests.
- Keep the engineering workflow usable in Codex Cloud, local VS Code with Docker, Codex, and Claude Code.
- Keep core instructions provider-neutral. `AGENTS.md` is primary; `CLAUDE.md` is only a short Claude Code adapter.
- Prefer deterministic scripts for setup and verification.
- Do not add Redis, queues, Kubernetes, microservices, or infrastructure outside this pilot's needs.

## Current milestone

Bootstrap the engineering harness and a reproducible Laravel + MySQL development base. This milestone does not include beneficiaries, benefit entities, eligibility requirements, evaluations, business rules, or business screens. Future domain work requires an explicit task.

## Development entry points

- `./scripts/bootstrap.sh` validates prerequisites and prepares local configuration.
- `docker compose up -d` starts the application and MySQL.
- `./scripts/verify.sh` is the single entry point for project checks.

See [`ARCHITECTURE.md`](ARCHITECTURE.md) and [`../policies/`](../policies/) for shared decisions and guardrails.
