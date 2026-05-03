<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompraController extends Controller
{
    public function index()
    {
        $compras = [];
        $dbError = null;

        try {
            $compras = DB::select('SELECT * FROM vw_compra_total');
        } catch (\Throwable $exception) {
            $dbError = 'No fue posible cargar el historial de compras.';
        }

        return view('pages.compras', compact('compras', 'dbError'));
    }

    public function create()
    {
        $clientes = DB::select('SELECT ID_Cliente, Nombre_Cliente FROM CLIENTE ORDER BY Nombre_Cliente');
        $empleados = DB::select('SELECT ID_Empleado, Nombre_Empleado FROM EMPLEADO ORDER BY Nombre_Empleado');
        $locales = DB::select('SELECT ID_Local, Nombre FROM LOCAL ORDER BY Nombre');
        $productos = DB::select('SELECT ID_Producto, Nombre, Precio_Actual FROM PRODUCTO ORDER BY Nombre');
        $metodos_pago = DB::select('SELECT ID_Metodo, Nombre FROM METODO_PAGO ORDER BY Nombre');

        return view('pages.compras.create', compact('clientes', 'empleados', 'locales', 'productos', 'metodos_pago'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cliente'  => 'required|exists:cliente,id_cliente',
            'empleado' => 'required|exists:empleado,id_empleado',
            'local'    => 'required|exists:local,id_local',
            'metodo'   => 'required|exists:metodo_pago,id_metodo',
            'productos' => 'required|array|min:1',
            'productos.*.id'       => 'required|exists:producto,id_producto',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio'   => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $idCompra = DB::transaction(function () use ($request) {
                $productos = [];
                $cantidades = [];
                $precios = [];

                foreach ($request->input('productos') as $item) {
                    $productos[]  = (int) $item['id'];
                    $cantidades[] = (int) $item['cantidad'];
                    $precios[]    = (float) $item['precio'];
                }

                $row = DB::selectOne(
                    'SELECT registrar_compra(?, ?, ?, ?, ?, ?, ?) AS id_compra',
                    [
                        $request->input('cliente'),
                        $request->input('empleado'),
                        $request->input('metodo'),
                        $request->input('local'),
                        '{' . implode(',', $productos)  . '}',
                        '{' . implode(',', $cantidades) . '}',
                        '{' . implode(',', $precios)    . '}',
                    ]
                );

                return $row->id_compra;
            });

            return redirect()->route('compras.index')->with('success', "Compra #{$idCompra} registrada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Error al registrar la compra: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::statement('SELECT anular_compra(?)', [$id]);
            return redirect()->route('compras.index')->with('success', "Compra #{$id} anulada exitosamente.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Error al anular la compra: ' . $e->getMessage());
        }
    }
}
