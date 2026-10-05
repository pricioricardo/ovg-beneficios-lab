#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$repo_root"

./scripts/prepare-test-db.sh

# Primeiro rejeite configuração externa conflitante; depois neutralize DB_URL
# e repita a guarda no mesmo processo que executa migrate:fresh --seed.
docker compose exec -T -e APP_ENV=testing -e DB_CONNECTION=mysql -e DB_DATABASE=ovg_beneficios_lab_test app php scripts/test-db.php
docker compose exec -T -e APP_ENV=testing -e DB_CONNECTION=mysql -e DB_DATABASE=ovg_beneficios_lab_test -e DB_URL= app php scripts/test-db.php --reset
