<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [];
        $activity = [];
        $dbError = null;

        try {
            $summary = DB::selectOne(
                'SELECT
                    (SELECT COUNT(*) FROM PRODUCTO) AS productos_activos,
                    (SELECT COUNT(*) FROM COMPRA) AS compras_registradas,
                    (SELECT COUNT(*) FROM LOCAL) AS locales_monitoreados,
                    (SELECT COALESCE(SUM(Cantidad_Actual), 0) FROM INVENTARIO) AS unidades_stock'
            );

            $stats = [
                ['label' => 'Productos activos', 'value' => $summary->productos_activos],
                ['label' => 'Compras registradas', 'value' => $summary->compras_registradas],
                ['label' => 'Locales monitoreados', 'value' => $summary->locales_monitoreados],
                ['label' => 'Unidades en stock', 'value' => $summary->unidades_stock],
            ];

            $activity = DB::select(
                'SELECT
                    c.ID_Compra,
                    cl.Nombre_Cliente,
                    l.Nombre AS local_nombre,
                    c.Fecha_Compra
                FROM COMPRA c
                INNER JOIN CLIENTE cl ON cl.ID_Cliente = c.ID_Cliente
                INNER JOIN LOCAL l ON l.ID_Local = c.ID_Local
                ORDER BY c.Fecha_Compra DESC
                LIMIT 5'
            );
        } catch (\Throwable $exception) {
            $dbError = 'No fue posible cargar el dashboard desde PostgreSQL.';
        }

        return view('pages.dashboard', compact('stats', 'activity', 'dbError'));
    }
}
