#!/bin/bash
# Script para actualizar GameStore en producción.
# Asume:
#   - El repo está clonado y vos estás en la rama de deploy
#   - El .env ya existe configurado
#   - Caddy del host ya tiene el bloque para gamestore.servigtdev.com
#
# Uso: ./deploy/deploy.sh

set -e

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WEB_TARGET="/var/www/gamestore-client"

cd "$REPO_ROOT"

echo "==> Pull de la rama actual"
git pull

echo "==> Build del frontend (Vite)"
docker compose -f docker-compose.prod.yml --profile build run --rm web-build

echo "==> Copiando build a $WEB_TARGET"
sudo mkdir -p "$WEB_TARGET"
sudo rsync -a --delete web/dist/ "$WEB_TARGET/"

echo "==> Reconstruyendo / reiniciando api y db"
docker compose -f docker-compose.prod.yml up -d --build api db

echo "==> Recargando Caddy (por si cambió el snippet)"
sudo systemctl reload caddy || true

echo "==> Listo. Visitar https://gamestore.servigtdev.com"
