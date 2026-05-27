-- Fase 4 - Datos de prueba adicionales para reportes y consultas realistas
-- Archivo: 08-extra-data.sql
--
-- Objetivo: dejar una base más poblada y determinística para dashboard,
-- reportes, exportación CSV y pruebas de stock insuficiente.

-- 1) Completar INVENTARIO para todas las combinaciones producto-local que falten.
-- La fórmula evita RANDOM() para que cada init del proyecto produzca los mismos datos.
INSERT INTO INVENTARIO (ID_Producto, ID_Local, Cantidad_Actual)
SELECT
    p.ID_Producto,
    l.ID_Local,
    (((p.ID_Producto * 7 + l.ID_Local * 11) % 28) + 3)::INT AS Cantidad_Actual
FROM PRODUCTO p
CROSS JOIN LOCAL l
ON CONFLICT (ID_Producto, ID_Local) DO NOTHING;

-- 2) Forzar algunas filas con stock 0 para probar el RAISE EXCEPTION de registrar_compra.
UPDATE INVENTARIO SET Cantidad_Actual = 0
WHERE (ID_Producto, ID_Local) IN ((2, 3), (5, 8), (15, 12), (20, 7));

-- 3) Insertar 40 compras adicionales con IDs fijos (26-65), fechas distribuidas
-- entre enero y abril de 2026 y locales/clientes/empleados variados.
INSERT INTO COMPRA (ID_Compra, ID_Cliente, ID_Empleado, ID_Metodo, ID_Local, Fecha_Compra)
SELECT
    g AS ID_Compra,
    1 + ((g - 1) % 25) AS ID_Cliente,
    1 + ((g * 7) % 25) AS ID_Empleado,
    1 + ((g * 5) % 25) AS ID_Metodo,
    1 + ((g * 11) % 25) AS ID_Local,
    DATE '2026-01-01'
        + ((g * 3) % 120) * INTERVAL '1 day'
        + ((g * 5) % 10)  * INTERVAL '1 hour'
        + ((g * 13) % 60) * INTERVAL '1 minute' AS Fecha_Compra
FROM generate_series(26, 65) AS g
ON CONFLICT (ID_Compra) DO UPDATE SET
    ID_Cliente = EXCLUDED.ID_Cliente,
    ID_Empleado = EXCLUDED.ID_Empleado,
    ID_Metodo = EXCLUDED.ID_Metodo,
    ID_Local = EXCLUDED.ID_Local,
    Fecha_Compra = EXCLUDED.Fecha_Compra;

-- 4) Agregar 3 líneas por compra nueva. Los productos se eligen con offsets
-- determinísticos para evitar duplicados dentro de la misma compra.
INSERT INTO COMPRA_PRODUCTOS (ID_Compra, ID_Producto, Cantidad, Precio_Venta)
SELECT
    c.ID_Compra,
    p.ID_Producto,
    1 + ((c.ID_Compra + producto_offset.offset_val) % 3) AS Cantidad,
    ROUND((p.Precio_Actual * (1 + producto_offset.offset_val * 0.03))::NUMERIC, 2) AS Precio_Venta
FROM COMPRA c
CROSS JOIN LATERAL (
    VALUES (0), (7), (14)
) AS producto_offset(offset_val)
INNER JOIN PRODUCTO p
    ON p.ID_Producto = 1 + ((c.ID_Compra + producto_offset.offset_val - 1) % 25)
WHERE c.ID_Compra BETWEEN 26 AND 65
ON CONFLICT (ID_Compra, ID_Producto) DO UPDATE SET
    Cantidad = EXCLUDED.Cantidad,
    Precio_Venta = EXCLUDED.Precio_Venta;

-- 5) Ajustar la secuencia para que las compras creadas desde la API sigan en 66+.
SELECT setval(
    pg_get_serial_sequence('compra', 'id_compra'),
    COALESCE((SELECT MAX(ID_Compra) FROM COMPRA), 1),
    true
);
