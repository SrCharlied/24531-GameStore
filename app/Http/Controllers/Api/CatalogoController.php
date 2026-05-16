<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CatalogoController extends Controller
{
    public function index()
    {
        return response()->json([
            'franquicias'  => DB::select('SELECT ID_Franquicia, Nombre_Franquicia FROM FRANQUICIA ORDER BY Nombre_Franquicia'),
            'categorias'   => DB::select('SELECT ID_Categoria, Nombre_Categoria FROM CATEGORIA ORDER BY Nombre_Categoria'),
            'locales'      => DB::select('SELECT ID_Local, Nombre, Zona FROM LOCAL ORDER BY Nombre'),
            'clientes'     => DB::select('SELECT ID_Cliente, Nombre_Cliente FROM CLIENTE ORDER BY Nombre_Cliente'),
            'empleados'    => DB::select('SELECT ID_Empleado, Nombre_Empleado FROM EMPLEADO ORDER BY Nombre_Empleado'),
            'metodos_pago' => DB::select('SELECT ID_Metodo, Nombre FROM METODO_PAGO ORDER BY Nombre'),
            'productos'    => DB::select('SELECT ID_Producto, Nombre, Precio_Actual FROM PRODUCTO ORDER BY Nombre'),
        ]);
    }
}
