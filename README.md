# GameStore

Sistema de inventario y ventas para una tienda de figuras de videojuegos. Arquitectura de 3 capas: PostgreSQL como base de datos relacional (donde vive la lógica transaccional), Laravel como backend REST API, y React (Vite) como frontend SPA.

## LINK A LA WEB

https://gamestore.servigtdev.com

## 🌟 Características principales

**Base de datos**
- 14 tablas con PK, FK, CHECK constraints (3FN)
- Función transaccional `registrar_compra()` con doble protección (PL/pgSQL + CHECK)
- Stored procedures `sp_registrar_compra()` y `sp_anular_compra()` para operaciones transaccionales de venta
- Función `anular_compra()` para reversar ventas y restaurar inventario
- Trigger de auditoría sobre cambios de precio
- Vistas para encapsular agregaciones (`vw_producto_stock`, `vw_compra_total`)
- Roles de base de datos (`rol_admin`, `rol_gerente`, `rol_vendedor`, `rol_bodega`, `rol_auditor`) con permisos granulares
- El backend aplica `SET LOCAL ROLE` en operaciones críticas para que PostgreSQL haga cumplir los permisos reales

**Backend (Laravel API)**
- API REST completa con autenticación SPA (Laravel Sanctum + cookies)
- RBAC: cinco roles de negocio (`admin`, `gerente`, `vendedor`, `bodega`, `auditor`) con responsabilidades diferenciadas y rutas protegidas por rol en backend/frontend
- Dashboard con alertas de inventario e ingresos del mes
- Reportes comerciales filtrables por local y rango de fechas
- Detalle completo de compras y export CSV de compras/reportes
- Endpoint y pantalla de auditoría para revisar cambios de precio registrados por trigger
- Manejo de errores con códigos HTTP correctos y mensajes JSON legibles

**Frontend (React + Vite)**
- React Router con rutas protegidas por sesión y por rol
- Auth global con Context (`AuthProvider` + `useAuth`)
- Carrito de líneas en "Nueva compra" con `useReducer` + `useMemo` + `useCallback`
- Formularios controlados con validación cliente
- Validación de rangos de fecha y bloqueo de acciones durante operaciones críticas
- 18 pruebas con Vitest (reducer, validación, permisos, componentes)
- ESLint sin warnings

**Infraestructura**
- Despliegue reproducible con `docker compose up` (3 servicios: db + api + web)
- Vite proxy hacia el backend → mismo origen desde el navegador (sin CORS para el usuario final)
- Scripts de respaldo/restauración de PostgreSQL con `pg_dump` y `pg_restore`

## 📦 Cómo levantar el proyecto

### 📋 Requisitos

- Docker Desktop (Windows/macOS) o Docker Engine + Compose v2 (Linux)
- Puertos libres: `5173` (frontend Vite) · `8000` (API Laravel) · `5432` (PostgreSQL)

> ⚠️ No necesitas PHP, Composer, Node ni PostgreSQL instalados localmente. Todo corre dentro de los contenedores.

### 🚀 Inicio rápido

```bash
git clone -b proyecto-3 https://github.com/SrCharlied/24531-GameStore.git
cd 24531-GameStore
docker compose up --build -d
```

Los servicios quedan disponibles en:

| Servicio | URL | Notas |
|---|---|---|
| Frontend (React) | `http://localhost:5173` | **Abrir aquí.** Es la app que usa el usuario final. |
| API (Laravel) | `http://localhost:8000` | Solo JSON. Se accede a través del proxy del frontend. |
| PostgreSQL | `localhost:5432` | `proy3` / `secret`, base `gamestore` |

> 💡 Las credenciales fijas de la rúbrica del Proyecto 3 (`proy3` / `secret`) están definidas en `.env.example`. Si `.env` no existe, el contenedor API lo crea automáticamente al iniciar y genera `APP_KEY`.

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
docker exec -it gamestore_db psql -U proy3 -d gamestore

# Aplicar un script SQL manualmente
docker exec -i gamestore_db psql -U proy3 -d gamestore < database/sql/04-views.sql

# Crear respaldo local de la base de datos
./scripts/db-backup.sh

# Restaurar un respaldo local
./scripts/db-restore.sh database/backups/gamestore_YYYYMMDD_HHMMSS.dump
```

## 🎯 Demo guiada (recorrido recomendado)

Pensado para revisar todo el sistema en ~5 minutos. Usar los usuarios de prueba listados en [🔐 Autenticación](#-autenticación).

1. **Entrar con `admin`.**
2. **Dashboard:** métricas globales, ingresos del mes, alertas de inventario.
3. **Productos:** filtros (búsqueda / franquicia / categoría / stock), crear o editar producto. El CRUD usa **Eloquent ORM**.
4. **Compras:** filtros por búsqueda / local / fechas, export CSV, detalle de una compra.
5. **Registrar compra:** carrito con varias líneas, validación frontend, ejecuta el stored procedure `sp_registrar_compra()` (función transaccional en PostgreSQL).
6. **Anular compra:** confirma la acción, llama `sp_anular_compra()`, devuelve el inventario.
7. **Reportes:** filtros por local y rango de fechas, export CSV, explican el uso de CTE y subqueries.
8. **Auditoría de precios:** cambios registrados automáticamente por trigger, con filtros por producto y fechas.
9. **Cerrar sesión y entrar con otro rol** para mostrar el RBAC en acción:
   - `vendedor` → solo compras (sin dashboard ni productos)
   - `bodega` → productos e inventario (sin compras)
   - `auditor` → todo en solo lectura + página de auditoría

## ✅ Checklist técnico de rúbrica

| Punto | Estado |
|---|---|
| Arquitectura 3 capas: PostgreSQL + Laravel API + React | ✅ |
| Docker Compose reproducible (`docker compose up`) | ✅ |
| Variables de entorno en `.env` / `.env.example` (credenciales `proy3` / `secret`) | ✅ |
| Autenticación con sesiones/cookies (Laravel Sanctum SPA) | ✅ |
| 5 roles de aplicación con rutas protegidas en backend y frontend | ✅ |
| 5 roles reales en PostgreSQL con `CREATE ROLE` + `GRANT/REVOKE` y `SET LOCAL ROLE` | ✅ |
| ORM (Eloquent) para CRUD de Producto, categorías e inventario | ✅ |
| 5 stored procedures invocados desde el backend | ✅ `sp_registrar_compra`, `sp_anular_compra`, `sp_actualizar_precio`, `sp_descontinuar_producto`, `sp_reabastecer_inventario` |
| Stored procedure con parámetros IN/OUT y manejo de excepciones | ✅ `sp_actualizar_precio` |
| Stored procedure con transacción explícita `ROLLBACK` interna | ✅ `sp_reabastecer_inventario` |
| Transacciones explícitas con `BEGIN / COMMIT / ROLLBACK` | ✅ |
| Función `registrar_compra()` y `anular_compra()` con `RAISE EXCEPTION` | ✅ |
| Trigger de auditoría sobre cambios de precio | ✅ |
| Vistas SQL para consultas agregadas | ✅ `vw_producto_stock`, `vw_compra_total` |
| Reportes con CTE, subqueries anidadas y agregaciones | ✅ |
| Export CSV (compras y reportes) | ✅ |
| Backup / restore | ✅ scripts en `scripts/` |
| Pruebas de frontend con Vitest | ✅ 18 pruebas |
| README de instalación, API, demo y rúbrica | ✅ |

## 🛠️ Comandos útiles durante la revisión

```bash
# Ver el estado de los servicios
docker compose ps

# Logs en tiempo real de la API Laravel
docker compose logs -f api

# Correr la suite de tests del frontend
docker exec gamestore_web npm test

# Linter del frontend
docker exec gamestore_web npm run lint

# Shell interactivo de PostgreSQL (como usuario proy3)
docker exec -it gamestore_db psql -U proy3 -d gamestore

# Crear un backup local de la BD
./scripts/db-backup.sh
```

## 🖥️ Frontend (React)

La app de React vive en `web/` y consume la API vía un **proxy de Vite**: el navegador solo habla con `localhost:5173`, y Vite reenvía internamente las llamadas `/api/*` y `/sanctum/*` al servicio `api`. Eso elimina problemas de CORS y de cookies cross-port en desarrollo.

### Páginas y rutas

| Ruta | Acceso | Componente |
|---|---|---|
| `/login` | público | `LoginPage` |
| `/` | admin, gerente, auditor | `DashboardPage` (métricas + alertas de inventario) |
| `/productos` | admin, gerente, bodega, auditor | `ProductosListPage` |
| `/productos/nuevo` | admin, bodega | `ProductoFormPage` |
| `/productos/:id/editar` | admin, bodega | `ProductoFormPage` |
| `/compras` | admin, gerente, vendedor, auditor | `ComprasListPage` (con botón Exportar CSV y acceso a detalle) |
| `/compras/:id` | admin, gerente, vendedor, auditor | `CompraDetallePage` (cabecera + líneas de productos) |
| `/compras/nueva` | admin, vendedor | `CompraNuevaPage` (**carrito con `useReducer`**) |
| `/reportes` | admin, gerente, auditor | `ReportesPage` (filtros por local/fechas y export CSV) |
| `/auditoria/precios` | admin, auditor | `AuditoriaPreciosPage` |

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
├── .env / .env.example         # Variables de entorno (incluye proy3/secret)
├── Dockerfile                  # Backend: PHP 8.3 + pdo_pgsql
├── README.md
├── ENTREGA.md                  # Guía rápida de demo y checklist de rúbrica
├── app/
│   ├── Http/Controllers/Api/   # Controllers JSON (Auth, Producto, Compra, Auditoria, ...)
│   ├── Http/Controllers/Controller.php  # Base con humanizeDbError()
│   ├── Http/Middleware/RequireAuth.php
│   ├── Models/                 # Modelos Eloquent (User, Producto, Inventario, Compra, catálogos)
│   └── Support/DatabaseRole.php # Mapea roles de app a roles reales del DBMS
├── routes/
│   ├── api.php                 # 18 endpoints REST
│   └── web.php                 # Solo / informativo (la app es API-only)
├── database/sql/               # 10 scripts cargados por Postgres al init
├── database/backups/           # Respaldos locales ignorados por Git
├── docker/start.sh             # Entrypoint del contenedor api
├── scripts/                    # Utilidades de respaldo/restauración
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
│       ├── pages/              # 8 páginas
│       ├── reducers/carritoReducer.js
│       ├── utils/validation.js / permissions.js
│       └── test/               # 4 archivos Vitest (15 tests)
└── _legacy/                    # Blade views/controllers archivados (no se ejecuta)
```

## 🔐 Autenticación

El backend usa **Laravel Sanctum en modo SPA**: el cliente recibe una cookie de sesión `HttpOnly` y una cookie CSRF `XSRF-TOKEN`. No hay tokens en JavaScript — más seguro que tokens en `localStorage` y menos código del lado del cliente.

### 🔑 Credenciales de prueba

Los roles de aplicación definidos para Proyecto 3 son:

| Rol | Responsabilidad |
|---|---|
| `admin` | Control total del sistema |
| `gerente` | Reportes, dashboard y consulta general |
| `vendedor` | Registrar ventas y consultar compras |
| `bodega` | Gestionar productos e inventario |
| `auditor` | Solo lectura y auditoría |

| Usuario | Contraseña | Rol | Acceso |
|---|---|---|---|
| `admin` | `admin123` | admin | Control total del sistema |
| `gerente` | `gerente123` | gerente | Dashboard, reportes y consulta de compras/productos |
| `vendedor` | `vendedor123` | vendedor | Registro/anulación de ventas y consulta de compras |
| `bodega` | `bodega123` | bodega | Gestión de productos e inventario |
| `auditor` | `auditor123` | auditor | Solo lectura: dashboard, reportes, compras, productos y auditoría |

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

### Compras

| Método | Endpoint | Roles | Descripción |
|---|---|---|---|
| `GET` | `/api/compras` | admin, gerente, vendedor, auditor | Listado con totales calculados (consume `vw_compra_total`) y filtros `q`, `local`, `desde`, `hasta` |
| `GET` | `/api/compras/{id}` | admin, gerente, vendedor, auditor | Detalle de compra con cabecera, método de pago, líneas, subtotales y total |
| `POST` | `/api/compras` | admin, vendedor | Registra compra dentro de transacción. Llama `registrar_compra()` |
| `DELETE` | `/api/compras/{id}` | admin, vendedor | Anula compra. Llama `anular_compra()`, devuelve inventario |
| `GET` | `/api/compras/export.csv` | admin, gerente, vendedor, auditor | Exporta líneas de compra en CSV (UTF-8 con BOM), respetando los mismos filtros del listado |

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

### Productos

| Método | Endpoint | Roles | Descripción |
|---|---|---|---|
| `GET` | `/api/productos` | admin, gerente, bodega, auditor | Listado con stock total agregado (consume `vw_producto_stock`) y filtros `q`, `franquicia`, `categoria`, `stock` |
| `GET` | `/api/productos/{id}` | admin, gerente, bodega, auditor | Detalle + categorías + stock por local (para edit form) |
| `POST` | `/api/productos` | admin, bodega | Crear producto con categorías y stock por local (UPSERT) |
| `PUT` | `/api/productos/{id}` | admin, bodega | Actualizar |
| `DELETE` | `/api/productos/{id}` | admin, bodega | Eliminar (CASCADE limpia inventario y categorías) |

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

### Uso de ORM en productos

El CRUD principal de productos usa Eloquent para cumplir la rúbrica de ORM:

| Modelo | Uso |
|---|---|
| `Producto` | Crear, consultar detalle, actualizar y eliminar productos |
| `Categoria` | Relación muchos-a-muchos con productos vía `producto_categoria` |
| `Inventario` | Crear/actualizar stock por local con `updateOrCreate` |
| `Franquicia` | Relación del producto con su franquicia |

Los listados agregados siguen consumiendo vistas SQL (`vw_producto_stock`) porque son consultas de reporte/lectura.

### Reportes y métricas

| Método | Endpoint | Roles | Descripción |
|---|---|---|---|
| `GET` | `/api/dashboard` | admin, gerente, auditor | Métricas globales, ingresos del mes, conteo de stock crítico, alertas de inventario y últimas 5 compras |
| `GET` | `/api/reportes` | admin, gerente, auditor | Top 5 locales por ingreso, top 5 productos, clientes destacados (con CTE + subqueries) y filtros `local`, `desde`, `hasta` |
| `GET` | `/api/reportes/export.csv` | admin, gerente, auditor | Exporta las secciones del reporte en CSV, respetando filtros `local`, `desde`, `hasta` |

### Auditoría de precios (admin, auditor)

| Método | Endpoint | Roles | Descripción |
|---|---|---|---|
| `GET` | `/api/auditoria/precios` | admin, auditor | Lista los últimos 100 cambios de precio registrados por `LOG_PRECIOS_PRODUCTO`, con filtros `q`, `desde`, `hasta` |

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

PostgreSQL carga 10 scripts al inicializar el volumen (`database/sql/`):

| Archivo | Contenido |
|---|---|
| `01-init.sql` | DDL de 13 tablas de dominio con PK, FK, CHECK |
| `02-contenidos.sql` | Datos base de prueba |
| `03-indexes.sql` | 7 índices sobre FKs y columnas frecuentes |
| `04-views.sql` | `vw_producto_stock`, `vw_compra_total` |
| `05-functions.sql` | `registrar_compra()` + procedure `sp_registrar_compra()` |
| `06-audit-trigger.sql` | Tabla `LOG_PRECIOS_PRODUCTO` y trigger `audit_precio_producto` |
| `07-anular-compra.sql` | `anular_compra()` + procedure `sp_anular_compra()` |
| `08-extra-data.sql` | Inventario completo + 40 compras determinísticas en Ene-Abr 2026 |
| `09-usuarios.sql` | Tabla `USUARIO`, cinco roles de aplicación y usuarios de prueba con `pgcrypto` para bcrypt |
| `10-permisos.sql` | Roles de base de datos y permisos granulares por perfil |

### 📊 Volumen actual

| Tabla | Filas |
|---|---|
| 7 maestros (Franquicia, Categoría, Cliente, Empleado, Local, etc.) | 25 c/u |
| Producto | 25 |
| Inventario | 625 |
| Compra | 65 |
| Compra_Productos | 145 |
| Usuario | 5 |

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

### 🧩 Aplicación de RBAC y roles DBMS desde Laravel

El archivo `app/Support/DatabaseRole.php` traduce el rol de aplicación (`admin`, `gerente`, `vendedor`, `bodega`, `auditor`) al rol real de PostgreSQL (`rol_admin`, `rol_gerente`, etc.).

El middleware `auth.session` acepta uno o varios roles separados por coma (`auth.session:admin,bodega`). Las rutas REST separan lectura y escritura para que el frontend no muestre acciones que PostgreSQL rechazaría por permisos.

En operaciones críticas de escritura, los controladores abren una transacción y ejecutan:

```sql
SET LOCAL ROLE rol_xxx;
```

De esa forma, PostgreSQL valida los permisos reales durante la transacción, no solo Laravel o React. Actualmente se aplica en:

- `CompraController@store`
- `CompraController@destroy`
- `ProductoController@store`
- `ProductoController@update`
- `ProductoController@destroy`

## 💾 Backup y restauración

La fase de respaldo usa herramientas nativas de PostgreSQL dentro del contenedor `gamestore_db`.

### Crear respaldo

```bash
./scripts/db-backup.sh
```

El archivo se guarda en `database/backups/` con formato:

```text
gamestore_YYYYMMDD_HHMMSS.dump
```

La carpeta conserva solo `.gitignore` y `.gitkeep`; los respaldos reales quedan ignorados por Git para no subir archivos pesados o datos locales.

### Restaurar respaldo

```bash
./scripts/db-restore.sh database/backups/gamestore_YYYYMMDD_HHMMSS.dump
```

> ⚠️ El respaldo se genera con `--clean --if-exists`, así que al restaurar reemplaza los objetos existentes de la base.

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
DB_USERNAME=proy3
DB_PASSWORD=secret
DB_DATABASE=gamestore
```

Definidas en `.env` y `.env.example`. El `docker-compose.yml` las lee con fallback hardcoded a los mismos valores, así que el stack arranca incluso si el `.env` se borra.
