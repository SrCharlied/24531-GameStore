#!/usr/bin/env bash
set -euo pipefail

# Fase 5 - Restauración de PostgreSQL desde un respaldo .dump.
# Uso:
#   ./scripts/db-restore.sh database/backups/gamestore_YYYYMMDD_HHMMSS.dump

if [ "$#" -ne 1 ]; then
  echo "Uso: $0 <archivo.dump>" >&2
  exit 1
fi

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKUP_FILE="$1"
CONTAINER="${DB_CONTAINER:-gamestore_db}"
DB_USER="${DB_USERNAME:-proy3}"
DB_NAME="${DB_DATABASE:-gamestore}"

if [[ "$BACKUP_FILE" != /* ]]; then
  BACKUP_FILE="$ROOT_DIR/$BACKUP_FILE"
fi

if [ ! -f "$BACKUP_FILE" ]; then
  echo "Error: no existe el archivo '$BACKUP_FILE'." >&2
  exit 1
fi

if ! docker ps --format '{{.Names}}' | grep -qx "$CONTAINER"; then
  echo "Error: el contenedor '$CONTAINER' no está corriendo." >&2
  echo "Levanta el stack con: docker compose up -d" >&2
  exit 1
fi

echo "Restaurando '$DB_NAME' en '$CONTAINER' desde: $BACKUP_FILE"
echo "Aviso: el respaldo fue generado con --clean, por lo que reemplaza objetos existentes."

docker exec -i "$CONTAINER" pg_restore \
  -U "$DB_USER" \
  -d "$DB_NAME" \
  --clean \
  --if-exists \
  --no-owner \
  --no-privileges \
  < "$BACKUP_FILE"

echo "Restauración completada."
