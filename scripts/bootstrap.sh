#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$repo_root"

# Cloud workspaces can mount the home directory read-only; keep BuildKit state writable.
export BUILDX_CONFIG="${BUILDX_CONFIG:-${TMPDIR:-/tmp}/ovg-beneficios-lab-buildx}"

command -v docker >/dev/null 2>&1 || {
    echo "Docker is required. Install Docker Desktop or Docker Engine with Compose v2." >&2
    exit 1
}
docker compose version >/dev/null
command -v curl >/dev/null 2>&1 || {
    echo "curl is required for the readiness check." >&2
    exit 1
}

if [[ ! -f .env ]]; then
    cp .env.example .env
    echo "Created local .env from .env.example. Review local-only values before sharing the machine."
else
    echo "Keeping existing .env."
fi

certificate_bundle=""
for candidate in "${SSL_CERT_FILE:-}" "${REQUESTS_CA_BUNDLE:-}" /etc/ssl/certs/ca-certificates.crt /etc/ssl/cert.pem; do
    if [[ -n "$candidate" && -f "$candidate" ]]; then
        certificate_bundle="$candidate"
        break
    fi
done

if [[ -n "$certificate_bundle" ]]; then
    cp "$certificate_bundle" .composer-ca-certificates.crt
else
    : > .composer-ca-certificates.crt
fi
trap 'rm -f .composer-ca-certificates.crt' EXIT

echo "Building the PHP application image..."
docker compose build app

echo "Installing locked Composer dependencies..."
if [[ -s .composer-ca-certificates.crt ]]; then
    docker compose run --rm --no-deps \
        -e COMPOSER_CAFILE=/var/www/html/.composer-ca-certificates.crt \
        -e GIT_SSL_CAINFO=/var/www/html/.composer-ca-certificates.crt \
        app composer install --no-interaction --prefer-dist --no-progress
else
    docker compose run --rm --no-deps \
        app composer install --no-interaction --prefer-dist --no-progress
fi

echo "Starting Laravel and MySQL..."
docker compose up -d

echo "Waiting for Laravel health endpoint..."
app_port="${APP_PORT:-8080}"
for attempt in {1..30}; do
    if curl --fail --silent "http://127.0.0.1:${app_port}/up" >/dev/null; then
        echo "Laravel is ready at http://127.0.0.1:${app_port}"
        exit 0
    fi
    sleep 2
done

docker compose logs --tail=80 app mysql >&2
echo "Laravel did not become ready in time." >&2
exit 1
