<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        [$where, $params] = $this->buildCompraFilters($request, 'c');

        $ventasPorLocal = DB::select(
            "SELECT l.Nombre AS local_nombre,
                    COUNT(DISTINCT c.ID_Compra) AS total_compras,
                    COALESCE(SUM(cp.Cantidad * cp.Precio_Venta), 0) AS ingreso_total
             FROM LOCAL l
             INNER JOIN COMPRA c ON c.ID_Local = l.ID_Local
             INNER JOIN COMPRA_PRODUCTOS cp ON cp.ID_Compra = c.ID_Compra
             {$where}
             GROUP BY l.ID_Local, l.Nombre
             ORDER BY ingreso_total DESC LIMIT 5",
            $params
        );

        $topProductos = DB::select(
            "WITH ventas_producto AS (
                SELECT p.ID_Producto, p.Nombre,
                       SUM(cp.Cantidad) AS unidades_vendidas,
                       SUM(cp.Cantidad * cp.Precio_Venta) AS ingreso_generado
                FROM PRODUCTO p
                INNER JOIN COMPRA_PRODUCTOS cp ON cp.ID_Producto = p.ID_Producto
                INNER JOIN COMPRA c ON c.ID_Compra = cp.ID_Compra
                {$where}
                GROUP BY p.ID_Producto, p.Nombre
             )
             SELECT * FROM ventas_producto
             ORDER BY unidades_vendidas DESC, ingreso_generado DESC LIMIT 5",
            $params
        );

        $clientesDestacados = DB::select(
            "SELECT resumen.Nombre_Cliente, resumen.total_compras, resumen.total_gastado
             FROM (
                SELECT cl.ID_Cliente, cl.Nombre_Cliente,
                       COUNT(DISTINCT c.ID_Compra) AS total_compras,
                       SUM(cp.Cantidad * cp.Precio_Venta) AS total_gastado
                FROM CLIENTE cl
                INNER JOIN COMPRA c ON c.ID_Cliente = cl.ID_Cliente
                INNER JOIN COMPRA_PRODUCTOS cp ON cp.ID_Compra = c.ID_Compra
                {$where}
                GROUP BY cl.ID_Cliente, cl.Nombre_Cliente
             ) AS resumen
             WHERE resumen.total_gastado > (
                SELECT COALESCE(AVG(cliente_total.total_gastado), 0)
                FROM (
                    SELECT c.ID_Cliente, SUM(cp.Cantidad * cp.Precio_Venta) AS total_gastado
                    FROM COMPRA c
                    INNER JOIN COMPRA_PRODUCTOS cp ON cp.ID_Compra = c.ID_Compra
                    {$where}
                    GROUP BY c.ID_Cliente
                ) AS cliente_total
             )
             ORDER BY resumen.total_gastado DESC LIMIT 5",
            [...$params, ...$params]
        );

        return response()->json([
            'ventas_por_local'    => $ventasPorLocal,
            'top_productos'       => $topProductos,
            'clientes_destacados' => $clientesDestacados,
            'filtros'             => $request->only(['local', 'desde', 'hasta']),
        ]);
    }

    private function buildCompraFilters(Request $request, ?string $tableAlias = null): array
    {
        $prefix = $tableAlias ? $tableAlias . '.' : '';
        $where = [];
        $params = [];

        if ($request->filled('local')) {
            $where[] = $prefix . 'ID_Local = ?';
            $params[] = (int) $request->query('local');
        }

        if ($request->filled('desde')) {
            $where[] = $prefix . 'Fecha_Compra::date >= ?';
            $params[] = $request->query('desde');
        }

        if ($request->filled('hasta')) {
            $where[] = $prefix . 'Fecha_Compra::date <= ?';
            $params[] = $request->query('hasta');
        }

        return [count($where) ? 'WHERE ' . implode(' AND ', $where) : '', $params];
    }
}
