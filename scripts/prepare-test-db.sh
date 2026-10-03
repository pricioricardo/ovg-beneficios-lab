#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$repo_root"

# Somente um banco descartável é criado; nunca execute migrate:fresh no banco padrão.
docker compose exec -T mysql sh -ec '
    case "$MYSQL_USER" in
        ""|*[!A-Za-z0-9_]*) echo "Nome do usuário MySQL inválido para o banco de teste." >&2; exit 1 ;;
    esac
    printf "CREATE DATABASE IF NOT EXISTS ovg_beneficios_lab_test; GRANT ALL PRIVILEGES ON ovg_beneficios_lab_test.* TO '\''%s'\''@'\''%%'\'';\n" "$MYSQL_USER" |
        mysql --user=root --password="$MYSQL_ROOT_PASSWORD"
'
