#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$repo_root"

# Cloud workspaces can mount the home directory read-only; keep BuildKit state writable.
export BUILDX_CONFIG="${BUILDX_CONFIG:-${TMPDIR:-/tmp}/ovg-beneficios-lab-buildx}"

echo "Checking Docker Compose configuration..."
docker compose config --quiet

if ! docker compose ps --status running --services | grep -Fxq app; then
    echo "Application container is not running; bootstrapping required services..."
    ./scripts/bootstrap.sh
fi

echo "Running Laravel test suite..."
docker compose exec -T app php artisan test

echo "Checking application HTTP health endpoint..."
port_mapping="$(docker compose port app 80)"
app_port="${port_mapping##*:}"
curl --fail --silent --show-error "http://127.0.0.1:${app_port}/up" >/dev/null
echo "HTTP health endpoint passed."
