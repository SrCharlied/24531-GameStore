-- Fase 3 - Roles de base de datos y permisos granulares
-- Archivo: 10-permisos.sql

-- Roles sin LOGIN: representan perfiles de acceso dentro de PostgreSQL.
-- Se pueden asignar a usuarios reales de BD con: GRANT rol_vendedor TO usuario_bd;
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'rol_admin') THEN
        CREATE ROLE rol_admin;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'rol_gerente') THEN
        CREATE ROLE rol_gerente;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'rol_vendedor') THEN
        CREATE ROLE rol_vendedor;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'rol_bodega') THEN
        CREATE ROLE rol_bodega;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'rol_auditor') THEN
        CREATE ROLE rol_auditor;
    END IF;
END $$;

-- Permitir que el usuario de conexión de Laravel/PostgreSQL pueda activar
-- el perfil correspondiente con SET LOCAL ROLE dentro de cada transacción.
GRANT rol_admin, rol_gerente, rol_vendedor, rol_bodega, rol_auditor TO CURRENT_USER;

-- Partir de una base explícita para que el script sea reproducible.
REVOKE ALL ON SCHEMA public FROM PUBLIC;
GRANT USAGE ON SCHEMA public TO rol_admin, rol_gerente, rol_vendedor, rol_bodega, rol_auditor;

REVOKE ALL ON ALL TABLES IN SCHEMA public FROM PUBLIC;
REVOKE ALL ON ALL SEQUENCES IN SCHEMA public FROM PUBLIC;
REVOKE ALL ON ALL FUNCTIONS IN SCHEMA public FROM PUBLIC;
REVOKE ALL ON ALL PROCEDURES IN SCHEMA public FROM PUBLIC;

-- Admin: control total sobre objetos actuales.
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO rol_admin;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO rol_admin;
GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA public TO rol_admin;
GRANT EXECUTE ON ALL PROCEDURES IN SCHEMA public TO rol_admin;

-- Gerente: consulta general, reportes y dashboard. No modifica ventas ni catálogo.
GRANT SELECT ON
    FRANQUICIA, CATEGORIA, PROVEEDOR, CLIENTE, EMPLEADO, METODO_PAGO, LOCAL,
    PRODUCTO, PRODUCTO_CATEGORIA, PRODUCTO_PROVEEDOR, INVENTARIO,
    COMPRA, COMPRA_PRODUCTOS, USUARIO,
    vw_producto_stock, vw_compra_total
TO rol_gerente;

-- Vendedor: puede registrar/anular compras y consultar catálogos necesarios.
GRANT SELECT ON
    FRANQUICIA, CATEGORIA, CLIENTE, EMPLEADO, METODO_PAGO, LOCAL,
    PRODUCTO, INVENTARIO, COMPRA, COMPRA_PRODUCTOS,
    vw_producto_stock, vw_compra_total
TO rol_vendedor;
GRANT INSERT, DELETE ON COMPRA, COMPRA_PRODUCTOS TO rol_vendedor;
GRANT UPDATE (Cantidad_Actual) ON INVENTARIO TO rol_vendedor;
GRANT USAGE, SELECT ON SEQUENCE compra_id_compra_seq TO rol_vendedor;
GRANT EXECUTE ON FUNCTION registrar_compra(INT, INT, INT, INT, INT[], INT[], NUMERIC[]) TO rol_vendedor;
GRANT EXECUTE ON FUNCTION anular_compra(INT) TO rol_vendedor;
GRANT EXECUTE ON PROCEDURE sp_registrar_compra(INT, INT, INT, INT, INT[], INT[], NUMERIC[]) TO rol_vendedor;
GRANT EXECUTE ON PROCEDURE sp_anular_compra(INT) TO rol_vendedor;

-- Bodega: administra productos, categorías, proveedores e inventario.
GRANT SELECT ON
    FRANQUICIA, CATEGORIA, PROVEEDOR, LOCAL,
    PRODUCTO, PRODUCTO_CATEGORIA, PRODUCTO_PROVEEDOR, INVENTARIO,
    vw_producto_stock
TO rol_bodega;
GRANT INSERT, UPDATE, DELETE ON
    PRODUCTO, PRODUCTO_CATEGORIA, PRODUCTO_PROVEEDOR, INVENTARIO
TO rol_bodega;
GRANT USAGE, SELECT ON SEQUENCE producto_id_producto_seq TO rol_bodega;

-- Auditor: solo lectura, pensado para revisión y trazabilidad.
GRANT SELECT ON ALL TABLES IN SCHEMA public TO rol_auditor;
GRANT SELECT ON ALL SEQUENCES IN SCHEMA public TO rol_auditor;

-- Permisos por defecto para objetos futuros creados por el usuario dueño de la BD.
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO rol_auditor;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO rol_admin;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO rol_admin;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT EXECUTE ON FUNCTIONS TO rol_admin;
