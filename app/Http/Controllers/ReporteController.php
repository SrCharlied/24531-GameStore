<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index()
    {
        $ventasPorLocal = [];
        $topProductos = [];
        $clientesDestacados = [];
        $dbError = null;

        try {
            $ventasPorLocal = DB::select(
                'SELECT
                    l.Nombre AS local_nombre,
                    COUNT(DISTINCT c.ID_Compra) AS total_compras,
                    SUM(cp.Cantidad * cp.Precio_Venta) AS ingreso_total
                FROM LOCAL l
                INNER JOIN COMPRA c ON c.ID_Local = l.ID_Local
                INNER JOIN COMPRA_PRODUCTOS cp ON cp.ID_Compra = c.ID_Compra
                GROUP BY l.ID_Local, l.Nombre
                ORDER BY ingreso_total DESC
                LIMIT 5'
            );

            $topProductos = DB::select(
                'WITH ventas_producto AS (
                    SELECT
                        p.ID_Producto,
                        p.Nombre,
                        SUM(cp.Cantidad) AS unidades_vendidas,
                        SUM(cp.Cantidad * cp.Precio_Venta) AS ingreso_generado
                    FROM PRODUCTO p
                    INNER JOIN COMPRA_PRODUCTOS cp ON cp.ID_Producto = p.ID_Producto
                    GROUP BY p.ID_Producto, p.Nombre
                )
                SELECT
                    ID_Producto,
                    Nombre,
                    unidades_vendidas,
                    ingreso_generado
                FROM ventas_producto
                ORDER BY unidades_vendidas DESC, ingreso_generado DESC
                LIMIT 5'
            );

            $clientesDestacados = DB::select(
                'SELECT
                    resumen.Nombre_Cliente,
                    resumen.total_compras,
                    resumen.total_gastado
                FROM (
                    SELECT
                        cl.ID_Cliente,
                        cl.Nombre_Cliente,
                        COUNT(DISTINCT c.ID_Compra) AS total_compras,
                        SUM(cp.Cantidad * cp.Precio_Venta) AS total_gastado
                    FROM CLIENTE cl
                    INNER JOIN COMPRA c ON c.ID_Cliente = cl.ID_Cliente
                    INNER JOIN COMPRA_PRODUCTOS cp ON cp.ID_Compra = c.ID_Compra
                    GROUP BY cl.ID_Cliente, cl.Nombre_Cliente
                ) AS resumen
                WHERE resumen.total_gastado > (
                    SELECT AVG(cliente_total.total_gastado)
                    FROM (
                        SELECT
                            c.ID_Cliente,
                            SUM(cp.Cantidad * cp.Precio_Venta) AS total_gastado
                        FROM COMPRA c
                        INNER JOIN COMPRA_PRODUCTOS cp ON cp.ID_Compra = c.ID_Compra
                        GROUP BY c.ID_Cliente
                    ) AS cliente_total
                )
                ORDER BY resumen.total_gastado DESC
                LIMIT 5'
            );
        } catch (\Throwable $exception) {
            $dbError = 'No fue posible cargar los reportes desde PostgreSQL.';
        }

        return view('pages.reportes', compact('ventasPorLocal', 'topProductos', 'clientesDestacados', 'dbError'));
    }
}
