<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    public function index()
    {
        $compras = [];
        $dbError = null;

        try {
            $compras = DB::select(
                'SELECT
                    c.ID_Compra,
                    cl.Nombre_Cliente,
                    e.Nombre_Empleado,
                    l.Nombre AS local_nombre,
                    c.Fecha_Compra,
                    SUM(cp.Cantidad * cp.Precio_Venta) AS total_compra
                FROM COMPRA c
                INNER JOIN CLIENTE cl ON cl.ID_Cliente = c.ID_Cliente
                INNER JOIN EMPLEADO e ON e.ID_Empleado = c.ID_Empleado
                INNER JOIN LOCAL l ON l.ID_Local = c.ID_Local
                INNER JOIN COMPRA_PRODUCTOS cp ON cp.ID_Compra = c.ID_Compra
                GROUP BY c.ID_Compra, cl.Nombre_Cliente, e.Nombre_Empleado, l.Nombre, c.Fecha_Compra
                ORDER BY c.Fecha_Compra DESC'
            );
        } catch (\Throwable $exception) {
            $dbError = 'No fue posible cargar el historial de compras.';
        }

        return view('pages.compras', compact('compras', 'dbError'));
    }
}
