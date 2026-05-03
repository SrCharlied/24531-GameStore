-- Fase 1.1 - Vistas para optimizar consultas de controladores
-- Archivo: 04-views.sql

-- Vista 1: Productos con stock total por franquicia
-- Usada por: ProductoController@index
DROP VIEW IF EXISTS vw_producto_stock CASCADE;
CREATE VIEW vw_producto_stock AS
SELECT 
    p.ID_Producto,
    p.Nombre,
    f.ID_Franquicia,
    f.Nombre_Franquicia AS franquicia,
    p.Precio_Actual,
    COALESCE(SUM(i.Cantidad_Actual), 0) AS stock_total,
    COALESCE(STRING_AGG(DISTINCT c.Nombre_Categoria, ', ' ORDER BY c.Nombre_Categoria), 'Sin categoria') AS categoria
FROM PRODUCTO p
INNER JOIN FRANQUICIA f ON f.ID_Franquicia = p.ID_Franquicia
LEFT JOIN PRODUCTO_CATEGORIA pc ON pc.ID_Producto = p.ID_Producto
LEFT JOIN CATEGORIA c ON c.ID_Categoria = pc.ID_Categoria
LEFT JOIN INVENTARIO i ON i.ID_Producto = p.ID_Producto
GROUP BY p.ID_Producto, p.Nombre, f.ID_Franquicia, f.Nombre_Franquicia, p.Precio_Actual
ORDER BY p.ID_Producto;

-- Vista 2: Compras con totales calculados
-- Usada por: CompraController@index y ReporteController
DROP VIEW IF EXISTS vw_compra_total CASCADE;
CREATE VIEW vw_compra_total AS
SELECT 
    c.ID_Compra,
    c.ID_Cliente,
    cl.Nombre_Cliente,
    c.ID_Empleado,
    e.Nombre_Empleado,
    c.ID_Local,
    l.Nombre AS local_nombre,
    c.Fecha_Compra,
    SUM(cp.Cantidad * cp.Precio_Venta) AS total_compra
FROM COMPRA c
INNER JOIN CLIENTE cl ON cl.ID_Cliente = c.ID_Cliente
INNER JOIN EMPLEADO e ON e.ID_Empleado = c.ID_Empleado
INNER JOIN LOCAL l ON l.ID_Local = c.ID_Local
INNER JOIN COMPRA_PRODUCTOS cp ON cp.ID_Compra = c.ID_Compra
GROUP BY c.ID_Compra, cl.Nombre_Cliente, e.Nombre_Empleado, l.Nombre, c.Fecha_Compra
ORDER BY c.Fecha_Compra DESC;