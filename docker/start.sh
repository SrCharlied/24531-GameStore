#!/bin/sh

set -e

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader
fi

if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

exec php artisan serve --host=0.0.0.0 --port=8000
