CREATE INDEX in_producto_franquicia ON PRODUCTO (ID_Franquicia);

CREATE INDEX in_producto_categoria_categoria_producto
ON PRODUCTO_CATEGORIA (ID_Categoria, ID_Producto);

CREATE INDEX in_inventario_local_producto
ON INVENTARIO (ID_Local, ID_Producto);

CREATE INDEX in_compra_local_fecha
ON COMPRA (ID_Local, Fecha_Compra);

CREATE INDEX in_compra_cliente_fecha
ON COMPRA (ID_Cliente, Fecha_Compra);

CREATE INDEX in_producto_nombre
ON PRODUCTO (Nombre);

CREATE INDEX idx_compra_empleado 
ON COMPRA(ID_Empleado);