# GameStore

Sistema de inventario y ventas para una tienda de figuras de videojuegos. Arquitectura de 3 capas: PostgreSQL como base de datos relacional (donde vive la lógica transaccional), Laravel como backend REST API, y React (Vite) como frontend SPA.

## 🌟 Características principales

**Base de datos**
- 14 tablas con PK, FK, CHECK constraints (3FN)
- Función transaccional `registrar_compra()` con doble protección (PL/pgSQL + CHECK)
- Función `anular_compra()` para reversar ventas y restaurar inventario
- Trigger de auditoría sobre cambios de precio
- Vistas para encapsular agregaciones (`vw_producto_stock`, `vw_compra_total`)

**Backend (Laravel API)**
- API REST completa con autenticación SPA (Laravel Sanctum + cookies)
- RBAC: dos roles (`admin` / `empleado`) con permisos diferenciados
- Manejo de errores con códigos HTTP correctos y mensajes JSON legibles

**Frontend (React + Vite)**
- React Router con rutas protegidas por sesión y por rol
- Auth global con Context (`AuthProvider` + `useAuth`)
- Carrito de líneas en "Nueva compra" con `useReducer` + `useMemo` + `useCallback`
- Formularios controlados con validación cliente
- 11 pruebas con Vitest (reducer, validación, componentes)
- ESLint sin warnings

**Infraestructura**
- Despliegue reproducible con `docker compose up` (3 servicios: db + api + web)
- Vite proxy hacia el backend → mismo origen desde el navegador (sin CORS para el usuario final)

## 📦 Cómo levantar el proyecto

### 📋 Requisitos

- Docker Desktop (Windows/macOS) o Docker Engine + Compose v2 (Linux)
- Puertos libres: `5173` (frontend Vite) · `8000` (API Laravel) · `5432` (PostgreSQL)

> ⚠️ No necesitas PHP, Composer, Node ni PostgreSQL instalados localmente. Todo corre dentro de los contenedores.

### 🚀 Inicio rápido

```bash
git clone -b WEB-React-API https://github.com/SrCharlied/24531-GameStore.git
cd 24531-GameStore
docker compose up --build -d
```

Los servicios quedan disponibles en:

| Servicio | URL | Notas |
|---|---|---|
| Frontend (React) | `http://localhost:5173` | **Abrir aquí.** Es la app que usa el usuario final. |
| API (Laravel) | `http://localhost:8000` | Solo JSON. Se accede a través del proxy del frontend. |
| PostgreSQL | `localhost:5432` | `proy2` / `secret`, base `gamestore` |

> 💡 Las credenciales fijas de la rúbrica (`proy2` / `secret`) están definidas en `.env.example`. El archivo `.env` ya viene listo en el repositorio para que `docker compose up` funcione sin pasos previos.

### 💡 Comandos útiles

```bash
# Ver estado y logs
docker compose ps
docker compose logs -f api

# Detener todo (mantiene datos)
docker compose down

# Detener y borrar volumen (recarga datos desde scripts SQL)
docker compose down -v && docker compose up -d

# Conectarse a PostgreSQL
docker exec -it gamestore_db psql -U proy2 -d gamestore

# Aplicar un script SQL manualmente
docker exec -i gamestore_db psql -U proy2 -d gamestore < database/sql/04-views.sql
```

## 🖥️ Frontend (React)

La app de React vive en `web/` y consume la API vía un **proxy de Vite**: el navegador solo habla con `localhost:5173`, y Vite reenvía internamente las llamadas `/api/*` y `/sanctum/*` al servicio `api`. Eso elimina problemas de CORS y de cookies cross-port en desarrollo.

### Páginas y rutas

| Ruta | Acceso | Componente |
|---|---|---|
| `/login` | público | `LoginPage` |
| `/` | admin | `DashboardPage` (métricas) |
| `/productos` | admin | `ProductosListPage` |
| `/productos/nuevo` | admin | `ProductoFormPage` |
| `/productos/:id/editar` | admin | `ProductoFormPage` |
| `/compras` | empleado + admin | `ComprasListPage` (con botón Exportar CSV) |
| `/compras/nueva` | empleado + admin | `CompraNuevaPage` (**carrito con `useReducer`**) |
| `/reportes` | admin | `ReportesPage` |

### Hooks y patrones

- **`useState`** para el estado local de formularios y listas.
- **`useEffect`** para hidratar datos al montar (catálogos, sesión, etc.).
- **`useReducer`** en `CompraNuevaPage` para administrar las líneas del carrito con acciones `ADD_LINE`, `REMOVE_LINE`, `UPDATE_LINE`, `RESET`. El reducer vive en `src/reducers/carritoReducer.js` y se testea de forma aislada.
- **`useMemo`** para calcular el total de la compra (suma de cantidad × precio) y para mapear precios por producto, sin recalcular en cada render.
- **`useCallback`** para estabilizar los handlers `updateLine` / `removeLine` que se pasan a componentes hijos (`CompraLine`).
- **`useContext`** vía `AuthContext` para el usuario logueado.

### Comandos del frontend

```bash
# Tests (Vitest)
docker exec gamestore_web npm test

# Linter
docker exec gamestore_web npm run lint
```

## 📁 Estructura del proyecto

Todo el proyecto vive bajo `24531-GameStore/`:

```text
24531-GameStore/
├── docker-compose.yml          # Orquesta db + api + web
├── .env / .env.example         # Variables de entorno (incluye proy2/secret)
├── Dockerfile                  # Backend: PHP 8.3 + pdo_pgsql
├── README.md
├── fases-v2.md                 # Plan de migración a React
├── app/
│   ├── Http/Controllers/Api/   # Controllers JSON (Auth, Producto, Compra, ...)
│   ├── Http/Controllers/Controller.php  # Base con humanizeDbError()
│   ├── Http/Middleware/RequireAuth.php
│   └── Models/User.php         # Modelo Eloquent → tabla usuario
├── routes/
│   ├── api.php                 # 15 endpoints REST
│   └── web.php                 # Solo / informativo (la app es API-only)
├── database/sql/               # 9 scripts cargados por Postgres al init
├── docker/start.sh             # Entrypoint del contenedor api
├── web/                        # ── Frontend React + Vite ──
│   ├── Dockerfile
│   ├── package.json
│   ├── vite.config.js          # Incluye proxy a la API
│   ├── eslint.config.js
│   ├── index.html
│   └── src/
│       ├── main.jsx / App.jsx / index.css
│       ├── api/client.js       # axios + CSRF interceptor
│       ├── context/            # AuthContext + useAuth
│       ├── components/         # Layout, RequireAuth (gate por rol)
│       ├── pages/              # 7 páginas
│       ├── reducers/carritoReducer.js
│       ├── utils/validation.js
│       └── test/               # 3 archivos Vitest (11 tests)
└── _legacy/                    # Blade views/controllers archivados (no se ejecuta)
```

## 🔐 Autenticación

El backend usa **Laravel Sanctum en modo SPA**: el cliente recibe una cookie de sesión `HttpOnly` y una cookie CSRF `XSRF-TOKEN`. No hay tokens en JavaScript — más seguro que tokens en `localStorage` y menos código del lado del cliente.

### 🔑 Credenciales de prueba

| Usuario | Contraseña | Rol | Acceso |
|---|---|---|---|
| `admin` | `admin123` | admin | Todos los endpoints |
| `empleado` | `empleado123` | empleado | Solo `/api/compras*` y `/api/catalogos` |

### 🔄 Flujo de login (lo que hará el frontend)

```
1. GET  /sanctum/csrf-cookie
   → Status 204. Setea cookie XSRF-TOKEN (URL-encoded).

2. POST /api/login
   Headers: X-XSRF-TOKEN: <valor decodificado de la cookie>
            Content-Type: application/json
   Body:    { "username": "admin", "password": "admin123" }
   → 200: { "user": { "id": 1, "username": "admin", "rol": "admin" } }
   → 401: { "message": "Usuario o contraseña incorrectos." }

3. Resto de requests
   El navegador envía la cookie de sesión automáticamente.
   Para POST/PUT/DELETE, añadir header X-XSRF-TOKEN.

4. POST /api/logout
   → 200: { "message": "Sesión cerrada." }
```

> 💡 Si usas `axios`, agrega `axios.defaults.withCredentials = true`. Axios manda `X-XSRF-TOKEN` automáticamente si la cookie `XSRF-TOKEN` está presente.

## 📡 Endpoints REST

Base URL: `http://localhost:8000`

Todos los endpoints `/api/*` devuelven JSON. El header `Accept: application/json` no es estrictamente necesario, pero recomendado.

### Autenticación

| Método | Endpoint | Auth | Descripción |
|---|---|---|---|
| `GET` | `/sanctum/csrf-cookie` | — | Setea cookie CSRF para POST/PUT/DELETE |
| `POST` | `/api/login` | — | Inicia sesión. Body: `{username, password}` |
| `POST` | `/api/logout` | sesión | Cierra sesión e invalida cookie |
| `GET` | `/api/me` | sesión | Devuelve usuario actual |

### Catálogos (cualquier rol autenticado)

| Método | Endpoint | Descripción |
|---|---|---|
| `GET` | `/api/catalogos` | Una sola respuesta con franquicias, categorías, locales, clientes, empleados, métodos de pago y productos. Usado para llenar dropdowns. |

### Compras (cualquier rol autenticado)

| Método | Endpoint | Descripción |
|---|---|---|
| `GET` | `/api/compras` | Listado con totales calculados (consume `vw_compra_total`) |
| `POST` | `/api/compras` | Registra compra dentro de transacción. Llama `registrar_compra()` |
| `DELETE` | `/api/compras/{id}` | Anula compra. Llama `anular_compra()`, devuelve inventario |
| `GET` | `/api/compras/export.csv` | Exporta líneas de compra en CSV (UTF-8 con BOM) |

Body de `POST /api/compras`:
```json
{
  "cliente": 1,
  "empleado": 1,
  "local": 1,
  "metodo": 1,
  "productos": [
    { "id": 1, "cantidad": 2, "precio": 150.00 },
    { "id": 5, "cantidad": 1, "precio": 89.99 }
  ]
}
```

Respuestas:
- `201`: `{ "message": "Compra #N registrada exitosamente.", "id_compra": N }`
- `422`: `{ "errors": { "cliente": [...], "productos.*.cantidad": [...] } }` (validación)
- `422`: `{ "message": "Stock insuficiente de \"X\" en el local \"Y\": disponibles N, solicitados M" }` (error de la función SQL)

### Productos (solo `admin`)

| Método | Endpoint | Descripción |
|---|---|---|
| `GET` | `/api/productos` | Listado con stock total agregado (consume `vw_producto_stock`) |
| `GET` | `/api/productos/{id}` | Detalle + categorías + stock por local (para edit form) |
| `POST` | `/api/productos` | Crear producto con categorías y stock por local (UPSERT) |
| `PUT` | `/api/productos/{id}` | Actualizar |
| `DELETE` | `/api/productos/{id}` | Eliminar (CASCADE limpia inventario y categorías) |

Body de `POST /api/productos`:
```json
{
  "nombre": "Producto X",
  "descripcion": "Texto opcional",
  "precio_actual": 99.99,
  "franquicia": 1,
  "categorias": [1, 3, 7],
  "stock": { "1": 10, "2": 5, "3": null }
}
```

> El objeto `stock` mapea `id_local → cantidad`. Valores `null` o vacíos se ignoran (no se crea fila en `INVENTARIO`).

### Reportes y métricas (solo `admin`)

| Método | Endpoint | Descripción |
|---|---|---|
| `GET` | `/api/dashboard` | Métricas globales (productos, compras, locales, unidades totales) + últimas 5 compras |
| `GET` | `/api/reportes` | Top 5 locales por ingreso, top 5 productos, clientes destacados (con CTE + subqueries) |

## 🚦 Códigos HTTP utilizados

| Código | Significado |
|---|---|
| `200` | OK — operación exitosa con cuerpo JSON |
| `201` | Created — recurso creado (responde con su `id`) |
| `204` | No Content — usado por `/sanctum/csrf-cookie` |
| `401` | Unauthorized — sin sesión |
| `403` | Forbidden — sesión válida pero rol insuficiente |
| `404` | Not Found — recurso no existe |
| `419` | Page Expired — falta o no coincide el `X-XSRF-TOKEN` |
| `422` | Unprocessable Entity — validación fallida o error semántico (stock insuficiente, etc.) |

## 🗄️ Base de datos

PostgreSQL carga 9 scripts al inicializar el volumen (`database/sql/`):

| Archivo | Contenido |
|---|---|
| `01-init.sql` | DDL de 13 tablas de dominio con PK, FK, CHECK |
| `02-contenidos.sql` | Datos base de prueba |
| `03-indexes.sql` | 7 índices sobre FKs y columnas frecuentes |
| `04-views.sql` | `vw_producto_stock`, `vw_compra_total` |
| `05-functions.sql` | `registrar_compra()` (PL/pgSQL transaccional) |
| `06-audit-trigger.sql` | Tabla `LOG_PRECIOS_PRODUCTO` y trigger `audit_precio_producto` |
| `07-anular-compra.sql` | `anular_compra()` que reversa ventas |
| `08-extra-data.sql` | Inventario completo + 40 compras adicionales en Ene-Abr 2026 |
| `09-usuarios.sql` | Tabla `USUARIO` + `pgcrypto` para bcrypt |

### 📊 Volumen actual

| Tabla | Filas |
|---|---|
| 7 maestros (Franquicia, Categoría, Cliente, Empleado, Local, etc.) | 25 c/u |
| Producto | 26 |
| Inventario | 650 |
| Compra | 65 |
| Compra_Productos | 145 |
| Usuario | 2 |

## 🧪 Probar la API end-to-end con curl

```bash
# 1. Iniciar sesión
COOKIES=/tmp/gs-cookies.txt
curl -s -c $COOKIES http://localhost:8000/sanctum/csrf-cookie
XSRF=$(grep XSRF-TOKEN $COOKIES | awk '{print $7}' | sed 's/%3D/=/g')

curl -s -b $COOKIES -c $COOKIES \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: $XSRF" \
  -X POST http://localhost:8000/api/login \
  -d '{"username":"admin","password":"admin123"}'

# 2. Listar productos
curl -s -b $COOKIES http://localhost:8000/api/productos

# 3. Registrar una compra
curl -s -b $COOKIES \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: $XSRF" \
  -X POST http://localhost:8000/api/compras \
  -d '{"cliente":1,"empleado":1,"local":1,"metodo":1,
       "productos":[{"id":1,"cantidad":1,"precio":150.00}]}'

# 4. Exportar CSV
curl -b $COOKIES http://localhost:8000/api/compras/export.csv -o compras.csv

# 5. Cerrar sesión
curl -s -b $COOKIES \
  -H "X-XSRF-TOKEN: $XSRF" \
  -X POST http://localhost:8000/api/logout
```

## ⚙️ Lógica en la base de datos

El proyecto pone deliberadamente la lógica transaccional en PostgreSQL en lugar de PHP.

### 🔐 `registrar_compra(p_cliente, p_empleado, p_metodo, p_local, p_productos[], p_cantidades[], p_precios[])`

En una sola operación atómica: inserta en `COMPRA`, agrega líneas a `COMPRA_PRODUCTOS`, descuenta `INVENTARIO`. Valida explícitamente y lanza `RAISE EXCEPTION` con mensaje legible (incluye nombre de producto y local) en cualquier inconsistencia. Si algo falla, rollback automático.

### 🔄 `anular_compra(p_id_compra)`

Devuelve cantidades al inventario del local correspondiente y borra la compra. Lanza excepción si la compra no existe.

### 🔔 Trigger `audit_precio_producto`

Sobre `PRODUCTO`. Cualquier `UPDATE` que cambie `Precio_Actual` se registra automáticamente en `LOG_PRECIOS_PRODUCTO` con valor anterior, nuevo y timestamp.

## 🛠️ Troubleshooting

### `docker compose up` falla con "container name already in use"

```bash
docker rm -f gamestore_db gamestore_api
docker compose up -d
```

### Las funciones SQL no aparecen

Los scripts de `database/sql/` solo corren cuando el volumen es nuevo:
```bash
docker compose down -v && docker compose up -d
```

### Recibes `HTTP 419 CSRF token mismatch`

El cliente debe primero hacer `GET /sanctum/csrf-cookie` y luego enviar el header `X-XSRF-TOKEN` con el valor decodificado de la cookie en cada POST/PUT/DELETE.

### El servidor responde lento en Docker Desktop (Windows)

Los bind mounts de Windows hacia Linux son lentos. Para desarrollo activo, considera usar volúmenes con cache delegada o trabajar dentro de WSL2.

## 🎓 Curso

Universidad — Quinto Semestre — Base de Datos / Programación Web

## 📝 Credenciales fijas (rúbrica)

```env
DB_USERNAME=proy2
DB_PASSWORD=secret
DB_DATABASE=gamestore
```

Definidas en `.env` y `.env.example`. El `docker-compose.yml` las lee con fallback hardcoded a los mismos valores, así que el stack arranca incluso si el `.env` se borra.
