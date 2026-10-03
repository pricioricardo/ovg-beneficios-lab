# OVG Benefícios Lab

## Purpose and scope

This repository is a small, experimental Laravel + Filament application for evaluating agent-assisted engineering. It uses synthetic data only and is inspired by public descriptions of social-benefit programs. It does not reproduce OVG internal systems or assume access to them. Keep the eventual product to at most 10 screens.

The current stage is engineering bootstrap and harness only. Do not add beneficiaries, benefit entities, eligibility requirements, evaluations, business rules, or business screens unless a later task explicitly authorizes that work.

## Portable working rules

- Treat this file and `policies/`, `skills/`, `scripts/`, and `docs/` as the shared source of truth across Codex, Claude Code, and local development.
- Read the applicable files in `policies/` before making a substantial change. Follow `docs/ARCHITECTURE.md` and record material decisions there.
- Use only synthetic data. Never put secrets in Git. Never assume access to OVG internal systems or make changes directly in production.
- Do not push directly to `main`. Work on a branch and use review before integration.
- Add or update meaningful automated tests for relevant behavior. Do not claim completion until `scripts/verify.sh` passes; report any unavailable checks precisely.
- Treat consolidated migrations as immutable. Add a new migration for a later schema change.
- Prefer deterministic scripts and repository-supported configuration over repeated prose instructions.
- For complex changes, follow `.agent/PLANS.md` and keep `.agent/STATE.md` concise and current.
- Keep provider-specific behavior in thin adapters such as `CLAUDE.md`; do not move shared policy into `.claude/` or `.codex/`.
- Use the existing checkout in cloud tasks. Do not create a Git worktree unless the user asks for one.

## Working in this repository

- Bootstrap or refresh local prerequisites with `./scripts/bootstrap.sh`.
- Start the application and MySQL with `docker compose up -d --build` on a fresh checkout, or `docker compose up -d` after its image has been built.
- Run the canonical checks with `./scripts/verify.sh`.
- Review `docker compose ps` and application logs when startup or checks fail. Stop only services started for the task when appropriate.

## Completion report

State the change, the checks actually run and their results, and any remaining blocker. Keep `.agent/STATE.md` aligned with the real repository state. Do not implement out-of-scope domain behavior as a bootstrap shortcut.
