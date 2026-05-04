# 24531-GameStore

Sistema de inventario y ventas para una tienda de figuras de videojuegos. Proyecto académico del curso de Base de Datos: el énfasis está en el diseño relacional y SQL explícito, con la lógica transaccional residiendo en PostgreSQL.

## 🌟 Características principales

- 13 tablas relacionales con PK, FK y CHECK constraints (3FN)
- Vistas SQL para encapsular consultas frecuentes
- Función transaccional `registrar_compra()` con manejo de errores y rollback automático
- Función `anular_compra()` para reversar ventas y restaurar inventario
- Trigger de auditoría que registra cambios de precio en `LOG_PRECIOS_PRODUCTO`
- CRUD completo de Producto (con stock por local mediante UPSERT) y Compra
- Reportes con CTE, subqueries anidadas, GROUP BY y agregación
- Despliegue reproducible con Docker Compose (1 comando)

## 📦 Cómo levantar el proyecto

### 📋 Requisitos del sistema

- Docker Desktop (Windows/macOS) o Docker Engine + Compose v2 (Linux)
- 4 GB de RAM libres recomendados
- Puerto 8000 (Laravel) y 5432 (PostgreSQL) disponibles

> ⚠️ Importante: el proyecto NO requiere PHP, Composer ni PostgreSQL instalados localmente. Todo corre dentro de los contenedores.

### 🔽 Obtener el proyecto

```bash
git clone <url-del-repo>
cd 24531-GameStore
```

### 🚀 Inicio rápido

```bash
docker compose up --build -d
```

La aplicación queda disponible en `http://localhost:8000`. PostgreSQL queda expuesto en `localhost:5432` (`gamestore` / `proy2` / `secret`).

> 💡 La primera vez tarda 2-5 minutos: Docker descarga PHP 8.3, PostgreSQL 15, instala extensiones y corre `composer install`.

### 💡 Comandos comunes

```bash
# Ver el estado de los contenedores
docker compose ps

# Ver logs en tiempo real (Laravel + Postgres)
docker compose logs -f

# Ver solo logs de la app Laravel
docker compose logs -f app

# Detener todo (mantiene datos)
docker compose down

# Detener y borrar volumen (recarga datos desde scripts SQL)
docker compose down -v && docker compose up -d

# Entrar al contenedor de la app
docker exec -it gamestore_app sh

# Conectarse a PostgreSQL
docker exec -it gamestore_db psql -U proy2 -d gamestore

# Ejecutar un script SQL contra la base existente
docker exec -i gamestore_db psql -U proy2 -d gamestore < ruta/al/archivo.sql
```

## 🗄️ Arquitectura de la base de datos

Al iniciar el volumen por primera vez, PostgreSQL ejecuta automáticamente todos los scripts en `database/sql/` por orden alfabético:

| Archivo | Contenido |
| ------- | --------- |
| `01-init.sql`         | DDL: 13 tablas con PK, FK, CHECK constraints |
| `02-contenidos.sql`   | Datos base: franquicias, productos, clientes, etc. |
| `03-indexes.sql`      | 7 índices sobre FKs y columnas de ordenamiento |
| `04-views.sql`        | Vistas `vw_producto_stock` y `vw_compra_total` |
| `05-functions.sql`    | Función transaccional `registrar_compra()` |
| `06-audit-trigger.sql`| Tabla `LOG_PRECIOS_PRODUCTO` y trigger de auditoría |
| `07-anular-compra.sql`| Función `anular_compra()` que reversa una venta |
| `08-extra-data.sql`   | Inventario completo y compras con fechas distribuidas |

> ⚠️ Los scripts solo se ejecutan al inicializar un volumen vacío. Si modificas un archivo y necesitas que se aplique, recrea el volumen con `docker compose down -v` o aplícalo manualmente con `docker exec -i gamestore_db psql ... < archivo.sql`.

### 📊 Volumen de datos cargado

| Tabla | Filas |
| ----- | ----- |
| FRANQUICIA / CATEGORIA / PROVEEDOR / CLIENTE / EMPLEADO / LOCAL / METODO_PAGO | 25 c/u |
| PRODUCTO | 26 |
| INVENTARIO | 650 (cobertura completa producto × local) |
| COMPRA | 65 (distribuidas en Ene-Abr 2026) |
| COMPRA_PRODUCTOS | 145 |

## 🌐 Rutas de la aplicación

| Método | Ruta | Descripción |
| ------ | ---- | ----------- |
| GET    | `/`                          | Dashboard con métricas globales |
| GET    | `/productos`                 | Listado (consume `vw_producto_stock`) |
| GET    | `/productos/crear`           | Formulario para crear producto |
| POST   | `/productos`                 | Inserta producto + categorías + stock por local |
| GET    | `/productos/{id}/editar`     | Formulario de edición pre-cargado |
| PUT    | `/productos/{id}`            | Actualiza producto, categorías y stock (UPSERT) |
| DELETE | `/productos/{id}`            | Elimina producto (CASCADE limpia inventario) |
| GET    | `/compras`                   | Historial (consume `vw_compra_total`) |
| GET    | `/compras/crear`             | Formulario con líneas dinámicas (JS vanilla) |
| POST   | `/compras`                   | Llama `registrar_compra()` dentro de transacción |
| DELETE | `/compras/{id}`              | Llama `anular_compra()` (devuelve inventario) |
| GET    | `/reportes`                  | Reportes con CTE, subqueries y JOINs múltiples |

## ⚙️ Lógica en la base de datos

El proyecto pone deliberadamente la lógica transaccional en PostgreSQL en lugar de en PHP. Los controllers son delgados: validan input, llaman SQL crudo y manejan respuesta.

### 🔐 `registrar_compra()`

Recibe arreglos paralelos `INT[]`, `INT[]`, `NUMERIC[]` con productos, cantidades y precios. En una sola operación atómica:

1. Inserta en `COMPRA`
2. Agrega líneas a `COMPRA_PRODUCTOS`
3. Descuenta `INVENTARIO` para cada producto en el local indicado

Si algo falla (longitudes inconsistentes, valores no positivos, sin inventario en el local, stock insuficiente) se hace rollback automático.

> 💡 Doble protección: la función valida explícitamente con `RAISE EXCEPTION`, y el `CHECK (Cantidad_Actual >= 0)` de `INVENTARIO` actúa como red de seguridad si el cálculo aritmético quedara negativo.

### 🔄 `anular_compra(id)`

Devuelve las cantidades al inventario del local correspondiente y borra la compra. Lanza excepción si la compra no existe.

### 🔔 Trigger `audit_precio_producto`

Sobre la tabla `PRODUCTO`. Registra automáticamente cualquier cambio de `Precio_Actual` en `LOG_PRECIOS_PRODUCTO` con precio anterior, nuevo y timestamp. Solo registra cuando el precio efectivamente cambia (UPDATE de otros campos no genera entrada).

### 👁️ Vistas

| Vista | Propósito |
| ----- | --------- |
| `vw_producto_stock` | Productos con stock total y categorías concatenadas. Usa subqueries en `SELECT` para evitar producto cartesiano cuando un producto tiene varias categorías y varios locales. |
| `vw_compra_total`   | Compras con total calculado (`SUM(Cantidad * Precio_Venta)`) y nombres resueltos vía JOIN. |

## ✅ Cobertura de la rúbrica

| Requerimiento | Dónde está |
| ------------- | ---------- |
| JOIN entre múltiples tablas              | `ProductoController`, `CompraController`, `ReporteController` |
| Subqueries                                | `ReporteController@index` (clientes destacados con AVG anidado), `vw_producto_stock` |
| GROUP BY + agregación                     | `ReporteController`, `vw_compra_total` |
| CTE (WITH)                                | `ReporteController@index` (top productos), `08-extra-data.sql` |
| VIEW                                      | `vw_producto_stock`, `vw_compra_total` (`04-views.sql`) |
| Transacción con manejo de errores         | `registrar_compra()` (`05-functions.sql`), `anular_compra()` (`07-anular-compra.sql`) |
| Trigger                                   | `audit_precio_producto` sobre `PRODUCTO` (`06-audit-trigger.sql`) |
| Índices                                   | 7 en `03-indexes.sql` |
| CRUD ≥ 2 entidades                        | Producto (CRUD completo) y Compra (create + delete por anulación) |
| Datos de prueba realistas                 | 65 compras, 145 líneas, inventario completo en 25 locales |
| Diagrama ER                               | `documentos/` |

## 📁 Estructura del proyecto

```text
24531-GameStore/
├── app/Http/Controllers/
│   ├── DashboardController.php   # Métricas globales
│   ├── ProductoController.php    # CRUD producto + stock por local
│   ├── CompraController.php      # Crear (transacción) + anular
│   └── ReporteController.php     # Reportes con CTE y subqueries
├── database/sql/                 # Scripts cargados por Postgres en orden
│   ├── 01-init.sql               # DDL
│   ├── 02-contenidos.sql         # Datos base
│   ├── 03-indexes.sql            # Índices
│   ├── 04-views.sql              # Vistas
│   ├── 05-functions.sql          # registrar_compra
│   ├── 06-audit-trigger.sql      # Trigger de precios
│   ├── 07-anular-compra.sql      # anular_compra
│   └── 08-extra-data.sql         # Inventario y compras adicionales
├── resources/views/
│   ├── layouts/app.blade.php     # Layout con CSS embebido
│   └── pages/                    # Páginas y formularios
│       ├── dashboard.blade.php
│       ├── productos.blade.php
│       ├── productos/
│       │   ├── _form.blade.php   # Partial reutilizado por create y edit
│       │   ├── create.blade.php
│       │   └── edit.blade.php
│       ├── compras.blade.php
│       ├── compras/
│       │   └── create.blade.php  # Formulario con JS para líneas dinámicas
│       └── reportes.blade.php
├── routes/web.php                # 12 rutas (4 GET informativos + CRUD)
├── docker/start.sh               # Entrypoint: composer install, key:generate, serve
├── docker-compose.yml            # Servicios app + db
├── Dockerfile                    # Imagen PHP 8.3 + pdo_pgsql
├── documentos/                   # Diagrama ER
└── .env / .env.example           # Configuración Laravel
```

## 🧪 Probar el flujo end-to-end

1. **Crear producto**

   En `/productos/crear`, completa los campos y asigna stock a 2-3 locales (deja el resto vacío). Al guardar, regresa al listado y el "Stock" del nuevo producto suma solo los locales con valores.

2. **Registrar compra exitosa**

   En `/compras/crear`, selecciona uno de los locales con stock asignado. Agrega 1-2 líneas con productos disponibles. Al guardar, el inventario se descuenta automáticamente.

3. **Provocar un rollback**

   Intenta registrar una compra con cantidad mayor al stock disponible. La función `registrar_compra()` falla con violación del `CHECK (Cantidad_Actual >= 0)` y el INSERT en `COMPRA` que ya había ocurrido se revierte. La UI muestra el banner de error.

4. **Anular compra**

   Desde `/compras`, presiona "Anular" en cualquier fila. La función `anular_compra()` devuelve las cantidades al inventario del local correspondiente y borra la compra. El stock del producto vuelve al valor previo.

5. **Auditoría de precios**

   Edita el precio de cualquier producto y luego consulta:

   ```sql
   SELECT * FROM LOG_PRECIOS_PRODUCTO ORDER BY Fecha_Cambio DESC LIMIT 5;
   ```

   El trigger registra automáticamente el cambio con precio anterior, nuevo y timestamp.

## 🛠️ Troubleshooting

### El contenedor `app` no responde en el puerto 8000

```bash
docker compose logs app --tail=50
```

Si ves errores de "vendor not found", es que `composer install` no terminó. Reinicia con `docker compose restart app`.

### Los cambios en SQL no se aplican

Recuerda que los scripts en `database/sql/` solo corren en volúmenes nuevos. Para forzar:

```bash
docker compose down -v && docker compose up -d
```

### Cambié un archivo .blade.php y no lo veo

Limpia las caches de Laravel:

```bash
docker exec gamestore_app php artisan view:clear
docker exec gamestore_app php artisan config:clear
```

## 📝 Notas técnicas

- Todos los identificadores SQL se declaran sin comillas, por lo que PostgreSQL los pliega a minúsculas internamente. Las reglas de validación `exists:` de Laravel también usan minúsculas.
- Los controllers usan `DB::select` / `DB::insert` / `DB::update` con SQL crudo. No se usa Eloquent para el dominio, en línea con el enfoque del curso.
- El layout (`app.blade.php`) incluye CSS embebido con paleta crema/marrón. No se usan frameworks CSS externos (Tailwind, Bootstrap).
- El JS de líneas dinámicas en `compras/create.blade.php` es vanilla, sin dependencias.

## 🎓 Curso

Universidad — Quinto Semestre — Base de Datos
