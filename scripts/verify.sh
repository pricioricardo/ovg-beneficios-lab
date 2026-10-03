#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$repo_root"

# Workspaces Cloud podem montar o diretório pessoal como somente leitura; mantenha gravável o estado do BuildKit.
export BUILDX_CONFIG="${BUILDX_CONFIG:-${TMPDIR:-/tmp}/ovg-beneficios-lab-buildx}"

echo "Verificando a configuração do Docker Compose..."
docker compose config --quiet

echo "Verificando a sintaxe dos scripts shell..."
bash -n scripts/bootstrap.sh scripts/verify.sh
sh -n scripts/docker-entrypoint.sh

running_services="$(docker compose ps --status running --services)"
if ! grep -Fxq app <<<"$running_services" || ! grep -Fxq mysql <<<"$running_services"; then
    echo "O container da aplicação ou do MySQL não está em execução; preparando os serviços necessários..."
    ./scripts/bootstrap.sh
fi

docker compose exec -T app composer validate --strict

echo "Verificando o health check do MySQL..."
mysql_container="$(docker compose ps -q mysql)"
mysql_health="$(docker inspect --format '{{.State.Health.Status}}' "$mysql_container")"
[[ "$mysql_health" == healthy ]] || {
    echo "MySQL não está saudável (status: ${mysql_health:-desconhecido})." >&2
    exit 1
}

echo "Verificando as versões de PHP, Composer, Laravel, Filament e Livewire..."
php_version="$(docker compose exec -T app php -r 'echo PHP_MAJOR_VERSION,".",PHP_MINOR_VERSION;')"
[[ "$php_version" == 8.4 ]] || { echo "Esperado PHP 8.4; encontrado ${php_version}." >&2; exit 1; }
docker compose exec -T app composer --version | grep -Eq 'Composer version 2\.'
docker compose exec -T app php artisan --version | grep -Eq 'Laravel Framework 13\.'
docker compose exec -T app php -r '
require "vendor/autoload.php";
foreach (["filament/filament" => 5, "livewire/livewire" => 4] as $package => $major) {
    $version = Composer\InstalledVersions::getPrettyVersion($package);
    if ($version === null || ! preg_match("/^v?{$major}\./", $version)) {
        fwrite(STDERR, "Esperada versão principal {$major} de {$package}; encontrada {$version}.\n");
        exit(1);
    }
    echo "{$package}: {$version}\n";
}'

echo "Verificando se as migrations foram aplicadas..."
docker compose exec -T app php artisan migrate:status --no-interaction

echo "Executando a suíte de testes do Laravel..."
docker compose exec -T app php artisan test

echo "Verificando o health check HTTP da aplicação..."
port_mapping="$(docker compose port app 80)"
app_port="${port_mapping##*:}"
curl --fail --silent --show-error "http://127.0.0.1:${app_port}/up" >/dev/null
echo "Health check HTTP passou."
curl --fail --silent --show-error --location "http://127.0.0.1:${app_port}/admin" >/dev/null
echo "Smoke test HTTP do painel Filament passou."

echo "Verificando erros de whitespace no diff..."
git diff --check
echo "Todas as verificações de runtime disponíveis passaram."
