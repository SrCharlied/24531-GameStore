# 24531-GameStore

Sistema de inventario y ventas para una tienda de figuras de videojuegos. Proyecto académico del curso de Base de Datos: el énfasis está en el diseño relacional y SQL explícito, no en el frontend.

## Stack

- **Base de datos:** PostgreSQL 15
- **Backend:** Laravel (PHP 8.3) usando `DB::select / DB::insert / DB::update` con SQL crudo (sin Eloquent para el dominio)
- **Vistas:** Blade
- **Infraestructura:** Docker Compose (`app` + `db`)

## Cómo levantar el proyecto

```bash
docker compose up --build -d
```

La aplicación queda en `http://localhost:8000`. PostgreSQL queda expuesto en `localhost:5432` (`gamestore` / `proy2` / `secret`).

Al iniciar el volumen por primera vez, PostgreSQL ejecuta automáticamente todos los scripts de `database/sql/` en orden:

| Archivo | Contenido |
|---|---|
| `01-init.sql` | DDL: 13 tablas con PK, FK y CHECK constraints |
| `02-contenidos.sql` | Datos base de prueba |
| `03-indexes.sql` | 7 índices sobre columnas frecuentemente consultadas |
| `04-views.sql` | Vistas `vw_producto_stock` y `vw_compra_total` |
| `05-functions.sql` | Función transaccional `registrar_compra()` |
| `06-audit-trigger.sql` | Tabla `LOG_PRECIOS_PRODUCTO` y trigger de auditoría |
| `07-anular-compra.sql` | Función `anular_compra()` que reversa una compra |
| `08-extra-data.sql` | Inventario completo y compras adicionales (Ene-Abr 2026) |

Para recrear desde cero (perdiendo todos los datos del volumen):

```bash
docker compose down -v && docker compose up --build -d
```

## Rutas

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/` | Dashboard con métricas globales y compras recientes |
| GET | `/productos` | Listado con stock total y categorías (consume `vw_producto_stock`) |
| GET | `/productos/crear` | Formulario para crear producto |
| POST | `/productos` | Inserta producto + categorías + stock por local |
| GET | `/productos/{id}/editar` | Formulario de edición pre-cargado |
| PUT | `/productos/{id}` | Actualiza producto, categorías y stock |
| DELETE | `/productos/{id}` | Elimina producto (CASCADE borra inventario y categorías) |
| GET | `/compras` | Historial con totales (consume `vw_compra_total`) |
| GET | `/compras/crear` | Formulario con líneas dinámicas |
| POST | `/compras` | Llama `registrar_compra()` dentro de transacción |
| DELETE | `/compras/{id}` | Llama `anular_compra()` (devuelve inventario) |
| GET | `/reportes` | Reportes con CTE, subqueries y JOINs múltiples |

## Lógica en la base de datos

El proyecto pone deliberadamente la lógica transaccional en PostgreSQL en lugar de en PHP:

- **`registrar_compra(...)`** — recibe arreglos paralelos `INT[]`, `INT[]`, `NUMERIC[]` con productos, cantidades y precios. Inserta en `COMPRA`, agrega líneas a `COMPRA_PRODUCTOS` y descuenta `INVENTARIO` en una sola operación. Si falla (sin inventario en el local, stock insuficiente, longitudes inconsistentes) se hace rollback automático. Doble protección: la función valida explícitamente, y el `CHECK (Cantidad_Actual >= 0)` de `INVENTARIO` actúa como red de seguridad.

- **`anular_compra(id)`** — devuelve las cantidades al inventario del local correspondiente y borra la compra. Lanza excepción si la compra no existe.

- **Trigger `audit_precio_producto`** — registra automáticamente cualquier cambio de `Precio_Actual` en `LOG_PRECIOS_PRODUCTO` con precio anterior, nuevo y timestamp. Solo registra cuando el precio efectivamente cambia.

- **Vistas** — encapsulan los JOINs y agregaciones que usan los listados, manteniendo los controllers limpios. `vw_producto_stock` usa subqueries en `SELECT` para evitar multiplicar filas en el SUM cuando un producto tiene varias categorías y varios locales.

## Cobertura de la rúbrica

| Requerimiento | Dónde está |
|---|---|
| JOIN entre múltiples tablas | `ProductoController@index`, `CompraController@index`, `ReporteController@index` |
| Subqueries | `ReporteController@index` (clientes destacados con AVG anidado), `vw_producto_stock` |
| GROUP BY + agregación | `ReporteController`, `vw_compra_total` |
| CTE (WITH) | `ReporteController@index` (top productos), `08-extra-data.sql` |
| VIEW | `vw_producto_stock`, `vw_compra_total` (`04-views.sql`) |
| Transacción con manejo de errores | `registrar_compra()` (`05-functions.sql`), `anular_compra()` (`07-anular-compra.sql`) |
| Trigger | `audit_precio_producto` sobre `PRODUCTO` (`06-audit-trigger.sql`) |
| Índices | 7 en `03-indexes.sql` (FKs frecuentes y columnas de ordenamiento) |
| CRUD | Producto (create/read/update/delete con stock por local) y Compra (create con transacción + delete vía anulación) |
| Datos de prueba realistas | 26 productos, 65 compras, 145 líneas, inventario completo (650 filas) |

## Diagrama ER

Disponible en `documentos/`.

## Probar el flujo transaccional

1. **Crear producto** en `/productos/crear` con stock asignado a 2-3 locales y dejar el resto vacío.
2. **Registrar compra** en `/compras/crear` para uno de los locales con stock — debe descontar el inventario.
3. Repetir intentando comprar más unidades de las disponibles — la función debe fallar con `inventario_cantidad_actual_check` (rollback automático).
4. **Anular compra** desde `/compras` — el inventario regresa al estado anterior.
5. **Editar precio** de cualquier producto y consultar:
   ```sql
   SELECT * FROM LOG_PRECIOS_PRODUCTO ORDER BY Fecha_Cambio DESC LIMIT 5;
   ```
   El trigger registra el cambio.
