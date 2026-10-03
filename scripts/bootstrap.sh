#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$repo_root"

# Workspaces Cloud podem montar o diretório pessoal como somente leitura; mantenha gravável o estado do BuildKit.
export BUILDX_CONFIG="${BUILDX_CONFIG:-${TMPDIR:-/tmp}/ovg-beneficios-lab-buildx}"

command -v docker >/dev/null 2>&1 || {
    echo "Docker é necessário. Instale Docker Desktop ou Docker Engine com Compose v2." >&2
    exit 1
}
docker compose version >/dev/null
command -v curl >/dev/null 2>&1 || {
    echo "curl é necessário para verificar a disponibilidade da aplicação." >&2
    exit 1
}

if [[ ! -f .env ]]; then
    cp .env.example .env
    echo "Arquivo .env local criado a partir de .env.example. Revise os valores locais antes de compartilhar a máquina."
else
    echo "O .env existente será mantido."
fi

# Persista o UID do workspace para permitir `docker compose up -d` diretamente após o bootstrap.
if ! grep -q '^APP_UID=' .env; then
    printf '\nAPP_UID=%s\n' "$(id -u)" >> .env
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

echo "Construindo a imagem PHP da aplicação..."
docker compose build app

echo "Instalando as dependências Composer fixadas no lockfile..."
if [[ -s .composer-ca-certificates.crt ]]; then
    docker compose run --rm --no-deps \
        -e COMPOSER_CAFILE=/var/www/html/.composer-ca-certificates.crt \
        -e GIT_SSL_CAINFO=/var/www/html/.composer-ca-certificates.crt \
        app composer install --no-interaction --prefer-dist --no-progress
else
    docker compose run --rm --no-deps \
        app composer install --no-interaction --prefer-dist --no-progress
fi

echo "Iniciando Laravel e MySQL..."
docker compose up -d

echo "Aplicando as migrations do Laravel..."
docker compose exec -T app php artisan migrate --force
echo "Criando somente o cenário sintético idempotente do laboratório..."
docker compose exec -T app php artisan db:seed --force

echo "Aguardando o health check HTTP do Laravel..."
port_mapping="$(docker compose port app 80)"
app_port="${port_mapping##*:}"
for attempt in {1..30}; do
    if curl --fail --silent "http://127.0.0.1:${app_port}/up" >/dev/null; then
        echo "Laravel está disponível em http://127.0.0.1:${app_port}"
        exit 0
    fi
    sleep 2
done

docker compose logs --tail=80 app mysql >&2
echo "Laravel não ficou disponível dentro do prazo." >&2
exit 1
