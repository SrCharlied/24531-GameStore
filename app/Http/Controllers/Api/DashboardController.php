<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $summary = DB::selectOne(
            "SELECT
                (SELECT COUNT(*) FROM PRODUCTO) AS productos_activos,
                (SELECT COUNT(*) FROM COMPRA) AS compras_registradas,
                (SELECT COUNT(*) FROM LOCAL) AS locales_monitoreados,
                (SELECT COALESCE(SUM(Cantidad_Actual), 0) FROM INVENTARIO) AS unidades_stock,
                (SELECT COUNT(*) FROM vw_producto_stock WHERE stock_total = 0) AS productos_sin_stock,
                (SELECT COUNT(*) FROM vw_producto_stock WHERE stock_total BETWEEN 1 AND 10) AS productos_stock_bajo,
                (SELECT COALESCE(SUM(total_compra), 0)
                 FROM vw_compra_total
                 WHERE fecha_compra >= date_trunc('month', CURRENT_DATE)) AS ingresos_mes"
        );

        $activity = DB::select(
            'SELECT c.ID_Compra, cl.Nombre_Cliente, l.Nombre AS local_nombre, c.Fecha_Compra
             FROM COMPRA c
             INNER JOIN CLIENTE cl ON cl.ID_Cliente = c.ID_Cliente
             INNER JOIN LOCAL l ON l.ID_Local = c.ID_Local
             ORDER BY c.Fecha_Compra DESC LIMIT 5'
        );

        $stockAlerts = DB::select(
            'SELECT id_producto, nombre, franquicia, categoria, precio_actual, stock_total
             FROM vw_producto_stock
             WHERE stock_total <= 10
             ORDER BY stock_total ASC, nombre ASC
             LIMIT 8'
        );

        return response()->json([
            'summary'      => $summary,
            'activity'     => $activity,
            'stock_alerts' => $stockAlerts,
        ]);
    }
}
