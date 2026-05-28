<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildReportData($request);

        return response()->json([
            ...$data,
            'filtros' => $request->only(['local', 'desde', 'hasta']),
        ]);
    }

    public function export(Request $request)
    {
        $data = $this->buildReportData($request);
        $filename = 'reportes_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($data, $request) {
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Filtros aplicados']);
            fputcsv($out, ['Local', $request->query('local', 'Todos')]);
            fputcsv($out, ['Desde', $request->query('desde', 'Sin inicio')]);
            fputcsv($out, ['Hasta', $request->query('hasta', 'Sin fin')]);
            fputcsv($out, []);

            fputcsv($out, ['Top 5 locales por ingreso']);
            fputcsv($out, ['Local', 'Compras', 'Ingreso total']);
            foreach ($data['ventas_por_local'] as $row) {
                fputcsv($out, [$row->local_nombre, $row->total_compras, $row->ingreso_total]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Top 5 productos mas vendidos']);
            fputcsv($out, ['Producto', 'Unidades vendidas', 'Ingreso generado']);
            foreach ($data['top_productos'] as $row) {
                fputcsv($out, [$row->nombre, $row->unidades_vendidas, $row->ingreso_generado]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Clientes destacados']);
            fputcsv($out, ['Cliente', 'Compras', 'Total gastado']);
            foreach ($data['clientes_destacados'] as $row) {
                fputcsv($out, [$row->nombre_cliente, $row->total_compras, $row->total_gastado]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function buildReportData(Request $request): array
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

        return [
            'ventas_por_local'    => $ventasPorLocal,
            'top_productos'       => $topProductos,
            'clientes_destacados' => $clientesDestacados,
        ];
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
