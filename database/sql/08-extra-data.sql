-- Fase 4 - Datos de prueba adicionales para reportes y consultas realistas

-- 1) Llenar INVENTARIO para todas las combinaciones producto-local que falten
INSERT INTO INVENTARIO (ID_Producto, ID_Local, Cantidad_Actual)
SELECT p.ID_Producto, l.ID_Local, FLOOR(RANDOM() * 28 + 3)::INT
FROM PRODUCTO p
CROSS JOIN LOCAL l
ON CONFLICT (ID_Producto, ID_Local) DO NOTHING;

-- 2) Forzar algunas filas con stock 0 (para probar el RAISE EXCEPTION de registrar_compra)
UPDATE INVENTARIO SET Cantidad_Actual = 0
WHERE (ID_Producto, ID_Local) IN ((2, 3), (5, 8), (15, 12), (20, 7));

-- 3) Insertar 40 compras adicionales con fechas distribuidas en Ene-May 2026,
--    y 2-3 líneas por compra con productos aleatorios
WITH nuevas AS (
    INSERT INTO COMPRA (ID_Cliente, ID_Empleado, ID_Metodo, ID_Local, Fecha_Compra)
    SELECT
        1 + (g % 25),
        1 + ((g * 7) % 25),
        1 + (g % 3),
        1 + ((g * 11) % 25),
        DATE '2026-01-01'
            + ((g * 3) % 120)  * INTERVAL '1 day'
            + ((g * 5) % 10)   * INTERVAL '1 hour'
            + ((g * 13) % 60)  * INTERVAL '1 minute'
    FROM generate_series(1, 40) g
    RETURNING ID_Compra
)
INSERT INTO COMPRA_PRODUCTOS (ID_Compra, ID_Producto, Cantidad, Precio_Venta)
SELECT
    n.ID_Compra,
    p.ID_Producto,
    1 + FLOOR(RANDOM() * 3)::INT,
    ROUND((40 + RANDOM() * 200)::NUMERIC, 2)
FROM nuevas n
CROSS JOIN LATERAL (
    SELECT ID_Producto FROM PRODUCTO ORDER BY RANDOM() LIMIT 3
) p
ON CONFLICT (ID_Compra, ID_Producto) DO NOTHING;
