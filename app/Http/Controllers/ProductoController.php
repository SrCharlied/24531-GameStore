<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = [];
        $dbError = null;

        try {
            $sql = <<<'SQL'
SELECT
    p.ID_Producto,
    p.Nombre,
    f.Nombre_Franquicia AS franquicia,
    COALESCE(STRING_AGG(DISTINCT c.Nombre_Categoria, ', ' ORDER BY c.Nombre_Categoria), 'Sin categoria') AS categoria,
    p.Precio_Actual,
    COALESCE(SUM(i.Cantidad_Actual), 0) AS stock_total
FROM PRODUCTO p
INNER JOIN FRANQUICIA f ON f.ID_Franquicia = p.ID_Franquicia
LEFT JOIN PRODUCTO_CATEGORIA pc ON pc.ID_Producto = p.ID_Producto
LEFT JOIN CATEGORIA c ON c.ID_Categoria = pc.ID_Categoria
LEFT JOIN INVENTARIO i ON i.ID_Producto = p.ID_Producto
GROUP BY p.ID_Producto, p.Nombre, f.Nombre_Franquicia, p.Precio_Actual
ORDER BY p.ID_Producto
SQL;

            $productos = DB::select($sql);
        } catch (\Throwable $exception) {
            $dbError = 'No fue posible cargar el listado de productos.';
        }

        return view('pages.productos', compact('productos', 'dbError'));
    }
}
