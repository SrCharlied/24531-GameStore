# Deployment a producción

Guía para deployar GameStore en el VPS `vmi3246735` bajo `https://gamestore.servigtdev.com`.

## 🏗️ Arquitectura

- **Caddy en el host** (ya instalado, ya maneja SSL) hace de edge y reverse-proxy.
- **2 contenedores Docker**: `db` (Postgres, sin puerto expuesto) + `api` (Laravel en `127.0.0.1:8082`).
- **Frontend**: build estático de React servido por Caddy directo desde `/var/www/gamestore-client`.

```
Internet
   ↓ 443
Caddy (host) ──┬── /var/www/gamestore-client  (React estático)
               │
               └── /api/* + /sanctum/* → 127.0.0.1:8082
                                              ↓
                                         gamestore_api_prod
                                              ↓ red interna
                                         gamestore_db_prod
```

## 📋 Prerequisitos

- VPS con Docker, Compose v2 y Caddy en el host (ya está).
- DNS: `gamestore.servigtdev.com` apuntando al VPS (ya está).
- Acceso `sudo` para editar `/etc/caddy/Caddyfile` y `/var/www/`.
- Puerto `8082` libre en el host (verificá con `sudo ss -tlnp | grep :8082`).

## 🚀 Deploy inicial (primera vez)

### 1. Clonar el repo en el server

Convención del VPS (igual que `seelescans-api`): los repos viven en `/opt/`.

```bash
sudo mkdir -p /opt && sudo chown $USER:$USER /opt   # solo si /opt no es tuyo aún
cd /opt
git clone -b REACT-API-Deploy <url-del-repo> gamestore
cd gamestore/24531-GameStore
```

### 2. Configurar el `.env` de producción

```bash
cp .env.production.example .env
nano .env  # revisar si hay algo que cambiar; las credenciales DB ya están
```

### 3. Generar APP_KEY

```bash
docker run --rm -v "$(pwd):/app" -w /app composer:2 sh -c \
  "composer install --quiet --no-dev --no-interaction && php artisan key:generate --show"
```

Copiá el string `base64:...` que imprime y pegalo como `APP_KEY=` en `.env`.

### 4. Levantar la base de datos y la API

```bash
docker compose -f docker-compose.prod.yml up -d --build db api
```

Primera vez tarda 2-3 min (descarga PHP, Postgres, instala dependencias).

Verificá:

```bash
docker compose -f docker-compose.prod.yml ps
docker compose -f docker-compose.prod.yml logs api --tail=30
curl -s http://127.0.0.1:8082/api/me   # debe dar {"message":"No autenticado."}
```

### 5. Construir el frontend

```bash
docker compose -f docker-compose.prod.yml --profile build run --rm web-build
```

Tarda 1-2 min. Cuando termine, el build está en `./web/dist/`.

### 6. Copiar el build a Caddy

```bash
sudo mkdir -p /var/www/gamestore-client
sudo rsync -a --delete web/dist/ /var/www/gamestore-client/
```

### 7. Agregar el bloque al Caddyfile del host

Tomar el contenido de `deploy/Caddyfile.snippet` y pegarlo al final de `/etc/caddy/Caddyfile`:

```bash
sudo cat deploy/Caddyfile.snippet >> /etc/caddy/Caddyfile
sudo nano /etc/caddy/Caddyfile   # revisar que quedó bien
```

Validar y recargar:

```bash
sudo caddy validate --config /etc/caddy/Caddyfile
sudo systemctl reload caddy
```

Caddy auto-provisiona el certificado Let's Encrypt al primer hit. Mirá los logs:

```bash
sudo journalctl -u caddy -f
```

Cuando veas `certificate obtained successfully`, abrir `https://gamestore.servigtdev.com` en el navegador.

## 🔄 Updates posteriores

Después del deploy inicial, cualquier cambio se aplica con:

```bash
cd /opt/gamestore/24531-GameStore
git checkout REACT-API-Deploy
./deploy/deploy.sh
```

El script hace `git pull`, rebuild del frontend, rebuild del contenedor api, copia estáticos, reload de Caddy.

Si solo cambiás el frontend, `docker compose ... web-build && rsync` alcanza. Si solo el backend, `docker compose ... up -d --build api`.

## 🧪 Verificación end-to-end

Después del deploy, probar el flujo completo:

```bash
# El frontend carga
curl -sI https://gamestore.servigtdev.com | head -1   # HTTP/2 200

# La API responde a través del proxy
curl -s https://gamestore.servigtdev.com/api/me        # {"message":"No autenticado."}

# Login funciona (con cookies)
COOKIES=/tmp/test.txt
curl -s -c $COOKIES https://gamestore.servigtdev.com/sanctum/csrf-cookie
XSRF=$(grep XSRF-TOKEN $COOKIES | awk '{print $7}' | sed 's/%3D/=/g')
curl -s -b $COOKIES -c $COOKIES \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: $XSRF" \
  -X POST https://gamestore.servigtdev.com/api/login \
  -d '{"username":"admin","password":"admin123"}'
# Debe responder: {"user":{"id":1,"username":"admin","rol":"admin"}}
```

## 🛠️ Troubleshooting

### `502 Bad Gateway` desde Caddy

El contenedor api no está respondiendo. Revisar:

```bash
docker compose -f docker-compose.prod.yml ps        # estado
docker compose -f docker-compose.prod.yml logs api  # errores
sudo ss -tlnp | grep :8082                          # confirma que está bound
```

### `419 Page Expired` (CSRF mismatch)

El bloque de Caddy debe forwardear `X-Forwarded-Proto: https` (ya está en el snippet). Verificar que `.env` tenga:

```
APP_URL=https://gamestore.servigtdev.com
SESSION_DOMAIN=gamestore.servigtdev.com
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=gamestore.servigtdev.com
```

Después de modificar `.env`:

```bash
docker compose -f docker-compose.prod.yml restart api
```

### `Mixed content` / requests a `http://...`

Significa que Laravel está generando URLs sin HTTPS. Confirmá que `trustProxies(at: '*')` está activo en `bootstrap/app.php` y que `APP_URL` empieza con `https://`.

### El frontend muestra el dashboard pero `/api/...` da 404

Caddy no está enrutando bien. Revisar:

```bash
sudo caddy validate --config /etc/caddy/Caddyfile
sudo journalctl -u caddy -n 50
```

El matcher `@api path /api/* /sanctum/*` debe estar ANTES del `handle` default.

### Backup de la DB

Manual:

```bash
docker exec gamestore_db_prod pg_dump -U proy2 gamestore > backup-$(date +%Y%m%d).sql
```

Restore:

```bash
docker exec -i gamestore_db_prod psql -U proy2 gamestore < backup.sql
```

Para un cron diario, agregar a `crontab -e`:

```cron
0 3 * * *  docker exec gamestore_db_prod pg_dump -U proy2 gamestore | gzip > ~/backups/gamestore-$(date +\%Y\%m\%d).sql.gz
```

## 🔒 Notas de seguridad

- `proy2/secret` queda como credenciales DB por requerimiento de la rúbrica. El puerto 5432 **no** está expuesto al exterior, así que el riesgo es solo si alguien ya tiene acceso al host.
- El contenedor `api` solo binda a `127.0.0.1:8082` — no es accesible directo desde internet, solo vía Caddy.
- HTTPS forzado por Caddy. Las cookies van con `Secure` y `SameSite=Lax`.
