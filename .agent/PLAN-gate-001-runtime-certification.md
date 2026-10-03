# ExecPlan: Gate 1 runtime certification

## Goal and scope

Certify the requested PHP 8.4, Laravel 13, Filament 5, Livewire 4, and MySQL 8.4 development runtime from Gate 0 commit `a447700302dab51652db3853dae86792cb316f44`. Do not add business-domain entities, rules, seed data, or screens. Do not merge this branch.

Acceptance requires the full checklist in the Gate 1 request, including a successful bootstrap, database connection and migrations, panel HTTP smoke test, automated tests, and a completely passing `scripts/verify.sh`.

## Current state

- Audit completed on clean `origin/work` at the Gate 0 commit.
- Existing scaffold is Laravel 12.12.2-era; its lock resolves framework 12.69.3, Filament 4.14.0, and Livewire 3.8.10.
- Existing Docker setup uses PHP 8.3 and MySQL 8.4. No application or database service was active at audit time.
- Packagist and GitHub codeload access succeeded; full Composer resolution and installation completed over TLS.
- New work branch: `chore/gate-001-runtime-certification` from `origin/work`.

## Strategy

Upgrade the existing minimal Laravel scaffold in place. It contains only framework boilerplate and no domain code, so replacing its Composer constraints, PHP image, and thin panel wiring is lower risk than regenerating the entire repository and disturbing the harness.

## Steps

1. Record baseline decision and update Composer constraints for PHP 8.4, Laravel 13, Filament 5, and Livewire 4; regenerate and validate the lockfile.
2. Update the PHP Docker image to 8.4, keep only the app and MySQL 8.4 services, and make bootstrap install, migrate, start, and wait deterministically.
3. Install a minimal empty Filament panel without domain resources or administrative user setup.
4. Add runtime-only tests for HTTP health, MySQL connectivity, and the empty panel.
5. Extend `scripts/verify.sh` to check tool and package versions, Compose containers and health, migrations, tests, HTTP health, and panel response; run bootstrap and verify end to end.
6. Update `.agent/STATE.md` with measured versions and exact outcomes. Commit and push this branch only; do not merge.

## Validation

- `composer validate --strict`
- `./scripts/bootstrap.sh`
- `./scripts/verify.sh`
- Direct confirmation of `php artisan --version`, MySQL `SELECT 1`, migrations, `/up`, and `/admin`.
- `git diff --check` and final Git status.

## Risks and recovery

- Composer or Docker egress may still fail in this task. Preserve TLS and checksums; identify the blocked hostname and stop as `GATE 1 — BLOCKED` if an external environment change is required.
- Major-version constraints may expose scaffold incompatibilities. Correct supported framework configuration and rerun the affected checks; never change tests to mask broken behavior.
- If a partial install or failed migration leaves local state, keep synthetic local volumes and diagnose them; do not remove unrelated user data or reset Git history.

## Progress

- [x] Read project instructions, policies, architecture, manifests, Docker setup, and Git state.
- [x] Confirm `origin/work` is the Gate 0 commit; create the requested feature branch.
- [x] Update and document the version baseline.
- [x] Install dependencies and prepare Docker runtime.
- [x] Configure minimal Filament panel and runtime checks.
- [x] Run full bootstrap and certification verification.
- [x] Record result, commit, and push without merge.
