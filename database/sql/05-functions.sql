-- Fase 1.2 - Función transaccional para registrar compras
-- Archivo: 05-functions.sql

-- Función: registrar_compra
-- Permite registrar una compra completa con transaccionalidad completa
-- Parámetros: arrays paralelos de productos, cantidades y precios
DROP FUNCTION IF EXISTS registrar_compra;

CREATE OR REPLACE FUNCTION registrar_compra(
    p_cliente INT,
    p_empleado INT,
    p_metodo INT,
    p_local INT,
    p_productos INT[],  -- [1, 5, 8]
    p_cantidades INT[],  -- [2, 1, 3]
    p_precios NUMERIC[]  -- [10.00, 25.50, 8.00]
) 
RETURNS INT AS $$
DECLARE
    v_id_compra INT;
    i INT;
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
    
    -- Insertar compra
    INSERT INTO COMPRA (ID_Cliente, ID_Empleado, ID_Metodo, ID_Local)
    VALUES (p_cliente, p_empleado, p_metodo, p_local)
    RETURNING ID_Compra INTO v_id_compra;
    
    -- Insertar productos de la compra y actualizar inventario
    FOR i IN 1 .. array_length(p_productos, 1) LOOP
        INSERT INTO COMPRA_PRODUCTOS (ID_Compra, ID_Producto, Cantidad, Precio_Venta)
        VALUES (v_id_compra, p_productos[i], p_cantidades[i], p_precios[i]);
        
        UPDATE INVENTARIO 
        SET Cantidad_Actual = Cantidad_Actual - p_cantidades[i]
        WHERE ID_Producto = p_productos[i] AND ID_Local = p_local;
        
        IF NOT FOUND THEN
            RAISE EXCEPTION 'Sin inventario registrado para producto % en local %', 
                          p_productos[i], p_local;
        END IF;
    END LOOP;
    
    RETURN v_id_compra;
END;
$$ LANGUAGE plpgsql;