#!/usr/bin/env sh
set -eu

cd /var/www/html

if [ ! -f vendor/autoload.php ]; then
    echo "Composer dependencies are missing. Run ./scripts/bootstrap.sh first." >&2
    exit 1
fi

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -Eq '^APP_KEY=base64:.+' .env; then
    php artisan key:generate --force --no-interaction
fi

exec apache2-foreground
