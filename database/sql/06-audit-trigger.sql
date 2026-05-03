-- Fase 1.3 - Trigger de auditoría de precios
-- Archivo: 06-audit-trigger.sql

-- Tabla de log para cambios de precio
CREATE TABLE IF NOT EXISTS LOG_PRECIOS_PRODUCTO (
    ID_Log SERIAL PRIMARY KEY,
    ID_Producto INT NOT NULL,
    Precio_Anterior NUMERIC(10,2),
    Precio_Nuevo NUMERIC(10,2),
    Fecha_Cambio TIMESTAMP DEFAULT NOW()
);

-- Función del trigger
CREATE OR REPLACE FUNCTION trg_audit_precio_producto()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.Precio_Actual <> OLD.Precio_Actual THEN
        INSERT INTO LOG_PRECIOS_PRODUCTO (ID_Producto, Precio_Anterior, Precio_Nuevo)
        VALUES (NEW.ID_Producto, OLD.Precio_Actual, NEW.Precio_Actual);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger
DROP TRIGGER IF EXISTS audit_precio_producto ON PRODUCTO;
CREATE TRIGGER audit_precio_producto
AFTER UPDATE ON PRODUCTO
FOR EACH ROW
EXECUTE FUNCTION trg_audit_precio_producto();