-- Fase 1.2 - Función transaccional para registrar compras
-- Archivo: 05-functions.sql

-- Función: registrar_compra
-- Permite registrar una compra completa con transaccionalidad completa
-- Parámetros: arrays paralelos de productos, cantidades y precios
DROP FUNCTION IF EXISTS registrar_compra(INT, INT, INT, INT, INT[], INT[], NUMERIC[]);

CREATE OR REPLACE FUNCTION registrar_compra(
    p_cliente INT,
    p_empleado INT,
    p_metodo INT,
    p_local INT,
    p_productos INT[],   -- [1, 5, 8]
    p_cantidades INT[],  -- [2, 1, 3]
    p_precios NUMERIC[]  -- [10.00, 25.50, 8.00]
)
RETURNS INT AS $$
DECLARE
    v_id_compra        INT;
    i                  INT;
    v_stock_disp       INT;
    v_nombre_producto  VARCHAR;
    v_nombre_local     VARCHAR;
BEGIN
    -- Validación de longitudes consistentes
    IF array_length(p_productos, 1) IS NULL OR
       array_length(p_productos, 1) <> array_length(p_cantidades, 1) OR
       array_length(p_productos, 1) <> array_length(p_precios, 1) THEN
        RAISE EXCEPTION 'Los arreglos de productos, cantidades y precios deben tener la misma longitud y al menos un elemento';
    END IF;

    -- Validar que todos los elementos sean positivos
    FOR i IN 1 .. array_length(p_productos, 1) LOOP
        IF p_cantidades[i] <= 0 OR p_precios[i] <= 0 THEN
            RAISE EXCEPTION 'Cantidades y precios deben ser mayores a 0';
        END IF;
    END LOOP;

    -- Resolver nombre del local (si no existe, abortar antes del INSERT)
    SELECT Nombre INTO v_nombre_local FROM LOCAL WHERE ID_Local = p_local;
    IF v_nombre_local IS NULL THEN
        RAISE EXCEPTION 'El local con ID % no existe', p_local;
    END IF;

    -- Insertar compra
    INSERT INTO COMPRA (ID_Cliente, ID_Empleado, ID_Metodo, ID_Local)
    VALUES (p_cliente, p_empleado, p_metodo, p_local)
    RETURNING ID_Compra INTO v_id_compra;

    -- Insertar productos de la compra y actualizar inventario
    FOR i IN 1 .. array_length(p_productos, 1) LOOP
        -- Resolver nombre del producto y stock disponible en el local
        SELECT prod.Nombre, inv.Cantidad_Actual
          INTO v_nombre_producto, v_stock_disp
          FROM PRODUCTO prod
          LEFT JOIN INVENTARIO inv
            ON inv.ID_Producto = prod.ID_Producto AND inv.ID_Local = p_local
         WHERE prod.ID_Producto = p_productos[i];

        IF v_nombre_producto IS NULL THEN
            RAISE EXCEPTION 'El producto con ID % no existe', p_productos[i];
        END IF;

        IF v_stock_disp IS NULL THEN
            RAISE EXCEPTION 'No hay inventario de "%" en el local "%"',
                v_nombre_producto, v_nombre_local;
        END IF;

        IF v_stock_disp < p_cantidades[i] THEN
            RAISE EXCEPTION 'Stock insuficiente de "%" en el local "%": disponibles %, solicitados %',
                v_nombre_producto, v_nombre_local, v_stock_disp, p_cantidades[i];
        END IF;

        INSERT INTO COMPRA_PRODUCTOS (ID_Compra, ID_Producto, Cantidad, Precio_Venta)
        VALUES (v_id_compra, p_productos[i], p_cantidades[i], p_precios[i]);

        UPDATE INVENTARIO
        SET Cantidad_Actual = Cantidad_Actual - p_cantidades[i]
        WHERE ID_Producto = p_productos[i] AND ID_Local = p_local;
    END LOOP;

    RETURN v_id_compra;
END;
$$ LANGUAGE plpgsql;
