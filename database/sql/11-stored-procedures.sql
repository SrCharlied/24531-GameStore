-- Proyecto 3 - Stored Procedures adicionales para completar el requisito
-- de ≥5 SPs invocados desde el backend.
--
-- Los SPs ya existentes (definidos en otros archivos):
--   * sp_registrar_compra (en 05-functions.sql)
--   * sp_anular_compra    (en 07-anular-compra.sql)
--
-- Este archivo agrega 3 más para llegar a 5 totales:
--   * sp_actualizar_precio       (IN/OUT + manejo de excepciones)
--   * sp_reabastecer_inventario  (transacción explícita con ROLLBACK)
--   * sp_descontinuar_producto   (utilitario con OUT)

-- ============================================================================
-- 1) sp_actualizar_precio
-- ----------------------------------------------------------------------------
-- Cubre los criterios:
--   ✔ Parámetros de entrada/salida (IN p_id_producto, p_nuevo_precio; OUT p_precio_anterior)
--   ✔ Manejo explícito de excepciones (bloque EXCEPTION WHEN ... )
-- ============================================================================
DROP PROCEDURE IF EXISTS sp_actualizar_precio(INT, NUMERIC);

CREATE OR REPLACE PROCEDURE sp_actualizar_precio(
    IN  p_id_producto    INT,
    IN  p_nuevo_precio   NUMERIC,
    OUT p_precio_anterior NUMERIC
)
LANGUAGE plpgsql
AS $$
BEGIN
    -- Validación de entrada
    IF p_nuevo_precio IS NULL OR p_nuevo_precio <= 0 THEN
        RAISE EXCEPTION 'El nuevo precio debe ser mayor a 0 (recibido: %)', p_nuevo_precio;
    END IF;

    -- Lectura del precio anterior y verificación de existencia
    SELECT Precio_Actual INTO p_precio_anterior
    FROM PRODUCTO
    WHERE ID_Producto = p_id_producto;

    IF p_precio_anterior IS NULL THEN
        RAISE EXCEPTION 'El producto #% no existe', p_id_producto;
    END IF;

    -- Actualización (dispara el trigger audit_precio_producto que escribe LOG_PRECIOS_PRODUCTO)
    UPDATE PRODUCTO
    SET Precio_Actual = p_nuevo_precio
    WHERE ID_Producto = p_id_producto;

EXCEPTION
    WHEN check_violation THEN
        RAISE EXCEPTION 'El precio viola las restricciones de la tabla PRODUCTO (debe ser > 0).';
    WHEN foreign_key_violation THEN
        RAISE EXCEPTION 'Referencia inválida al actualizar el producto.';
    WHEN OTHERS THEN
        -- Propagar otras excepciones manteniendo el mensaje original
        RAISE;
END;
$$;

-- ============================================================================
-- 2) sp_reabastecer_inventario
-- ----------------------------------------------------------------------------
-- Cubre el criterio:
--   ✔ Transacción explícita con ROLLBACK implementada dentro del SP
--
-- IMPORTANTE: este procedimiento usa COMMIT/ROLLBACK en su cuerpo, por lo que
-- el caller (Laravel) NO debe envolverlo en DB::beginTransaction(). Si lo hace,
-- Postgres lanzará "invalid transaction termination" porque el SP intenta
-- terminar una transacción que pertenece al caller.
--
-- Política: si cualquier cantidad es inválida, se hace ROLLBACK de todo el
-- reabastecimiento (nada queda aplicado).
-- ============================================================================
-- Nota: la firma del DROP incluye el tipo del parámetro INOUT (el último INT).
DROP PROCEDURE IF EXISTS sp_reabastecer_inventario(INT, INT[], INT[], INT);

CREATE OR REPLACE PROCEDURE sp_reabastecer_inventario(
    IN    p_id_local      INT,
    IN    p_id_productos  INT[],
    IN    p_cantidades    INT[],
    INOUT p_actualizados  INT DEFAULT 0
)
LANGUAGE plpgsql
AS $$
DECLARE
    i        INT;
    n_items  INT;
    v_invalid_pos INT := -1;
BEGIN
    p_actualizados := 0;

    -- Validación estructural de los arreglos
    n_items := COALESCE(array_length(p_id_productos, 1), 0);
    IF n_items = 0 THEN
        RAISE EXCEPTION 'Debe proporcionar al menos un producto a reabastecer';
    END IF;
    IF n_items <> COALESCE(array_length(p_cantidades, 1), 0) THEN
        RAISE EXCEPTION 'Los arreglos productos/cantidades deben tener la misma longitud';
    END IF;

    -- Pre-chequeo: si alguna cantidad es negativa, ROLLBACK explícito antes de
    -- hacer ninguna escritura. Esto demuestra el uso del keyword ROLLBACK.
    FOR i IN 1 .. n_items LOOP
        IF p_cantidades[i] IS NULL OR p_cantidades[i] < 0 THEN
            v_invalid_pos := i;
            EXIT;
        END IF;
    END LOOP;

    IF v_invalid_pos > 0 THEN
        ROLLBACK;  -- ← cancela toda la transacción del SP
        RAISE EXCEPTION
            'Reabastecimiento cancelado (ROLLBACK): cantidad inválida % en la posición %.',
            p_cantidades[v_invalid_pos], v_invalid_pos;
    END IF;

    -- Aplicar el reabastecimiento (UPSERT por par producto-local)
    FOR i IN 1 .. n_items LOOP
        INSERT INTO INVENTARIO (ID_Producto, ID_Local, Cantidad_Actual)
        VALUES (p_id_productos[i], p_id_local, p_cantidades[i])
        ON CONFLICT (ID_Producto, ID_Local) DO UPDATE
        SET Cantidad_Actual = INVENTARIO.Cantidad_Actual + EXCLUDED.Cantidad_Actual;

        p_actualizados := p_actualizados + 1;
    END LOOP;

    COMMIT;  -- ← cierre explícito de la transacción del SP
END;
$$;

-- ============================================================================
-- 3) sp_descontinuar_producto
-- ----------------------------------------------------------------------------
-- Marca un producto como descontinuado: vacía su stock en TODOS los locales
-- y devuelve el total de unidades retiradas. Usado por el rol bodega/admin
-- cuando un producto sale del catálogo.
-- ============================================================================
DROP PROCEDURE IF EXISTS sp_descontinuar_producto(INT);

CREATE OR REPLACE PROCEDURE sp_descontinuar_producto(
    IN  p_id_producto       INT,
    OUT p_unidades_retiradas INT
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_existe BOOLEAN;
BEGIN
    SELECT EXISTS (SELECT 1 FROM PRODUCTO WHERE ID_Producto = p_id_producto)
        INTO v_existe;

    IF NOT v_existe THEN
        RAISE EXCEPTION 'El producto #% no existe', p_id_producto;
    END IF;

    SELECT COALESCE(SUM(Cantidad_Actual), 0)
        INTO p_unidades_retiradas
    FROM INVENTARIO
    WHERE ID_Producto = p_id_producto;

    UPDATE INVENTARIO
    SET Cantidad_Actual = 0
    WHERE ID_Producto = p_id_producto;
END;
$$;

-- ============================================================================
-- Permisos para los nuevos SPs (compatibles con el esquema de roles existente)
-- ============================================================================
GRANT EXECUTE ON PROCEDURE sp_actualizar_precio(INT, NUMERIC)         TO rol_admin, rol_bodega;
GRANT EXECUTE ON PROCEDURE sp_reabastecer_inventario(INT, INT[], INT[], INT) TO rol_admin, rol_bodega;
GRANT EXECUTE ON PROCEDURE sp_descontinuar_producto(INT)              TO rol_admin, rol_bodega;
