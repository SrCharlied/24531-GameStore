#!/bin/sh
# Entrypoint del contenedor api en PRODUCCIÓN.
# Diferencias con start.sh (dev):
#   - No crea .env desde example (asume montado / configurado)
#   - Hace cache de config + rutas (más rápido en cada request)
#   - composer install corre sin --no-dev pero con optimizaciones

set -e

cd /var/www/html

# El volumen de storage puede estar vacío al primer arranque (named volume
# nuevo) y sobrescribir la estructura del image. Asegurar que existan
# las carpetas que Laravel necesita.
mkdir -p storage/framework/sessions \
         storage/framework/cache/data \
         storage/framework/views \
         storage/app/public \
         storage/logs

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader
fi

if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# Limpiar antes para que cualquier cambio en config/.env se refleje
php artisan config:clear
php artisan route:clear

# Cachear en producción
php artisan config:cache
php artisan route:cache

exec php artisan serve --host=0.0.0.0 --port=8000
