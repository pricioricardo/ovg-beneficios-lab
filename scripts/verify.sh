#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$repo_root"

# Cloud workspaces can mount the home directory read-only; keep BuildKit state writable.
export BUILDX_CONFIG="${BUILDX_CONFIG:-${TMPDIR:-/tmp}/ovg-beneficios-lab-buildx}"

echo "Checking Docker Compose configuration..."
docker compose config --quiet

echo "Checking shell script syntax..."
bash -n scripts/bootstrap.sh scripts/verify.sh
sh -n scripts/docker-entrypoint.sh

running_services="$(docker compose ps --status running --services)"
if ! grep -Fxq app <<<"$running_services" || ! grep -Fxq mysql <<<"$running_services"; then
    echo "Application or MySQL container is not running; bootstrapping required services..."
    ./scripts/bootstrap.sh
fi

docker compose exec -T app composer validate --strict

echo "Checking MySQL health..."
mysql_container="$(docker compose ps -q mysql)"
mysql_health="$(docker inspect --format '{{.State.Health.Status}}' "$mysql_container")"
[[ "$mysql_health" == healthy ]] || {
    echo "MySQL is not healthy (status: ${mysql_health:-unknown})." >&2
    exit 1
}

echo "Checking PHP, Composer, Laravel, Filament, and Livewire versions..."
php_version="$(docker compose exec -T app php -r 'echo PHP_MAJOR_VERSION,".",PHP_MINOR_VERSION;')"
[[ "$php_version" == 8.4 ]] || { echo "Expected PHP 8.4, found ${php_version}." >&2; exit 1; }
docker compose exec -T app composer --version | grep -Eq 'Composer version 2\.'
docker compose exec -T app php artisan --version | grep -Eq 'Laravel Framework 13\.'
docker compose exec -T app php -r '
require "vendor/autoload.php";
foreach (["filament/filament" => 5, "livewire/livewire" => 4] as $package => $major) {
    $version = Composer\InstalledVersions::getPrettyVersion($package);
    if ($version === null || ! preg_match("/^v?{$major}\./", $version)) {
        fwrite(STDERR, "Expected {$package} major {$major}, found {$version}.\n");
        exit(1);
    }
    echo "{$package}: {$version}\n";
}'

echo "Checking migrations are applied..."
docker compose exec -T app php artisan migrate:status --no-interaction

echo "Running Laravel test suite..."
docker compose exec -T app php artisan test

echo "Checking application HTTP health endpoint..."
port_mapping="$(docker compose port app 80)"
app_port="${port_mapping##*:}"
curl --fail --silent --show-error "http://127.0.0.1:${app_port}/up" >/dev/null
echo "HTTP health endpoint passed."
curl --fail --silent --show-error --location "http://127.0.0.1:${app_port}/admin" >/dev/null
echo "Filament admin panel HTTP smoke check passed."

echo "Checking whitespace errors..."
git diff --check
echo "All available runtime verifications passed."
