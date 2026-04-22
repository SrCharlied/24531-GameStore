<?php

namespace App\Http\Controllers;

class ReporteController extends Controller
{
    public function index()
    {
        $reportes = [
            [
                'nombre' => 'Ventas por local',
                'descripcion' => 'Resumen mensual de ventas agrupadas por sucursal.',
                'tipo_sql' => 'GROUP BY + SUM',
            ],
            [
                'nombre' => 'Inventario por producto',
                'descripcion' => 'Consulta consolidada del stock actual por local.',
                'tipo_sql' => 'JOIN',
            ],
            [
                'nombre' => 'Top productos vendidos',
                'descripcion' => 'Reporte preparado para ranking por cantidad vendida.',
                'tipo_sql' => 'CTE',
            ],
        ];

        return view('pages.reportes', compact('reportes'));
    }
}
