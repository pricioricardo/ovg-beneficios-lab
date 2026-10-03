# Shared agent skills

This directory holds provider-neutral, task-oriented workflows that can be followed by Codex, Claude Code, or a developer. Keep each workflow concise and actionable; reference `AGENTS.md`, `policies/`, `docs/`, and deterministic scripts instead of duplicating them.

Add a skill only when a recurring task benefits from a stable sequence and clear validation. Provider-specific adapters may point here, but must not become the only place where essential behavior is defined.
