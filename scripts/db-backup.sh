#!/usr/bin/env bash
set -euo pipefail

# Fase 5 - Respaldo de PostgreSQL desde Docker Compose.
# Uso:
#   ./scripts/db-backup.sh
#   DB_CONTAINER=gamestore_db DB_USERNAME=proy3 DB_DATABASE=gamestore ./scripts/db-backup.sh

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUT_DIR="${BACKUP_DIR:-$ROOT_DIR/database/backups}"
CONTAINER="${DB_CONTAINER:-gamestore_db}"
DB_USER="${DB_USERNAME:-proy3}"
DB_NAME="${DB_DATABASE:-gamestore}"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
OUT_FILE="$OUT_DIR/${DB_NAME}_${TIMESTAMP}.dump"

mkdir -p "$OUT_DIR"

if ! docker ps --format '{{.Names}}' | grep -qx "$CONTAINER"; then
  echo "Error: el contenedor '$CONTAINER' no está corriendo." >&2
  echo "Levanta el stack con: docker compose up -d" >&2
  exit 1
fi

echo "Creando respaldo de '$DB_NAME' desde '$CONTAINER'..."
docker exec "$CONTAINER" pg_dump \
  -U "$DB_USER" \
  -d "$DB_NAME" \
  --format=custom \
  --clean \
  --if-exists \
  --no-owner \
  --no-privileges \
  > "$OUT_FILE"

echo "Respaldo creado: $OUT_FILE"
