# Guía rápida de entrega — GameStore

Esta guía resume cómo levantar, probar y presentar el proyecto sin depender de herramientas instaladas localmente fuera de Docker.

## 1. Levantar el sistema

```bash
docker compose up --build -d
```

Servicios esperados:

| Servicio | URL |
|---|---|
| Frontend React | http://localhost:5173 |
| API Laravel | http://localhost:8000 |
| PostgreSQL | localhost:5432 |

## 2. Credenciales de demo

| Usuario | Contraseña | Rol | Demo sugerida |
|---|---|---|---|
| `admin` | `admin123` | admin | flujo completo |
| `gerente` | `gerente123` | gerente | dashboard/reportes/lectura |
| `vendedor` | `vendedor123` | vendedor | registrar/anular compras |
| `bodega` | `bodega123` | bodega | CRUD de productos e inventario |
| `auditor` | `auditor123` | auditor | auditoría y lectura |

## 3. Recorrido recomendado para la demo

1. Entrar con `admin`.
2. Mostrar Dashboard:
   - métricas globales
   - ingresos del mes
   - alertas de inventario
3. Mostrar Productos:
   - filtros por búsqueda/franquicia/categoría/stock
   - crear o editar producto
   - explicar que el CRUD usa Eloquent ORM
4. Mostrar Compras:
   - filtros por búsqueda/local/fechas
   - export CSV
   - entrar al detalle de una compra
5. Registrar compra:
   - carrito con varias líneas
   - validación frontend
   - función transaccional `registrar_compra()` en PostgreSQL
6. Anular compra:
   - confirma acción
   - llama `anular_compra()`
   - devuelve inventario
7. Mostrar Reportes:
   - filtros por local/fechas
   - export CSV de reportes
   - explicar CTE/subqueries
8. Mostrar Auditoría:
   - cambios de precio registrados por trigger
   - filtros por producto/fechas
9. Cerrar sesión y entrar con otro rol:
   - `vendedor`: sin dashboard/reportes/productos
   - `bodega`: productos sí, compras no
   - `auditor`: lectura + auditoría, sin escritura

## 4. Checklist técnico de rúbrica

| Punto | Estado |
|---|---|
| Arquitectura 3 capas: PostgreSQL + Laravel API + React | ✅ |
| Docker Compose reproducible | ✅ |
| Autenticación con sesiones/cookies Sanctum | ✅ |
| Roles de aplicación | ✅ |
| Roles reales en PostgreSQL con `SET LOCAL ROLE` | ✅ |
| CRUD con ORM | ✅ Productos |
| Función transaccional para registrar compra | ✅ `registrar_compra()` |
| Anulación transaccional de compra | ✅ `anular_compra()` |
| Trigger de auditoría | ✅ cambios de precio |
| Vistas SQL para consultas agregadas | ✅ `vw_producto_stock`, `vw_compra_total` |
| Reportes con agregaciones, CTE y subqueries | ✅ |
| Export CSV | ✅ compras y reportes |
| Backup/restore | ✅ scripts en `scripts/` |
| Pruebas frontend | ✅ Vitest |
| README de instalación y API | ✅ |

## 5. Comandos útiles durante la revisión

```bash
# Ver servicios
docker compose ps

# Logs de Laravel
docker compose logs -f api

# Tests frontend
docker exec gamestore_web npm test

# Linter frontend
docker exec gamestore_web npm run lint

# Shell de PostgreSQL
docker exec -it gamestore_db psql -U proy3 -d gamestore

# Backup de base de datos
./scripts/db-backup.sh
```

## 6. Notas importantes

- Abrir la app desde `http://localhost:5173`; Vite proxea `/api/*` y `/sanctum/*` hacia Laravel.
- El usuario final no necesita instalar PHP, Composer, Node ni PostgreSQL localmente.
- `_legacy/` conserva la implementación Blade anterior como referencia histórica, pero no se ejecuta en la app actual.
- `database/backups/` está preparado para respaldos locales, pero los `.dump` quedan ignorados por Git.
