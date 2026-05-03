-- Función para anular una compra y devolver el inventario
DROP FUNCTION IF EXISTS anular_compra(INT);

CREATE OR REPLACE FUNCTION anular_compra(p_id_compra INT)
RETURNS VOID AS $$
DECLARE
    v_local INT;
    rec RECORD;
BEGIN
    -- Obtener el local de la compra
    SELECT ID_Local INTO v_local FROM COMPRA WHERE ID_Compra = p_id_compra;

    IF v_local IS NULL THEN
        RAISE EXCEPTION 'La compra % no existe', p_id_compra;
    END IF;

    -- Sumar de vuelta al inventario
    FOR rec IN
        SELECT ID_Producto, Cantidad
        FROM COMPRA_PRODUCTOS
        WHERE ID_Compra = p_id_compra
    LOOP
        UPDATE INVENTARIO
        SET Cantidad_Actual = Cantidad_Actual + rec.Cantidad
        WHERE ID_Producto = rec.ID_Producto
          AND ID_Local = v_local;
    END LOOP;

    -- Borrar registros de la compra
    DELETE FROM COMPRA_PRODUCTOS WHERE ID_Compra = p_id_compra;
    DELETE FROM COMPRA WHERE ID_Compra = p_id_compra;
END;
$$ LANGUAGE plpgsql;
