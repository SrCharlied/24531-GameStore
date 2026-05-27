<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditoriaController extends Controller
{
    public function precios(Request $request)
    {
        $sql = 'SELECT
                    lp.ID_Log,
                    lp.ID_Producto,
                    p.Nombre AS producto,
                    lp.Precio_Anterior,
                    lp.Precio_Nuevo,
                    lp.Fecha_Cambio
                FROM LOG_PRECIOS_PRODUCTO lp
                INNER JOIN PRODUCTO p ON p.ID_Producto = lp.ID_Producto
                WHERE 1 = 1';
        $params = [];

        if ($request->filled('q')) {
            $term = '%' . trim($request->query('q')) . '%';
            $sql .= ' AND p.Nombre ILIKE ?';
            $params[] = $term;
        }

        if ($request->filled('desde')) {
            $sql .= ' AND lp.Fecha_Cambio::date >= ?';
            $params[] = $request->query('desde');
        }

        if ($request->filled('hasta')) {
            $sql .= ' AND lp.Fecha_Cambio::date <= ?';
            $params[] = $request->query('hasta');
        }

        $sql .= ' ORDER BY lp.Fecha_Cambio DESC, lp.ID_Log DESC LIMIT 100';

        return response()->json([
            'logs' => DB::select($sql, $params),
        ]);
    }
}
