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

        $productos = [];
        $cantidades = [];
        $precios = [];
        foreach ($request->input('productos') as $item) {
            $productos[]  = (int) $item['id'];
            $cantidades[] = (int) $item['cantidad'];
            $precios[]    = (float) $item['precio'];
        }

        try {
            DB::beginTransaction();

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

            DB::commit();

            return redirect()->route('compras.index')->with('success', "Compra #{$row->id_compra} registrada exitosamente.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al registrar la compra: ' . $this->humanizeDbError($e));
        }
    }

    public function export()
    {
        $rows = DB::select(
            'SELECT
                c.ID_Compra,
                c.Fecha_Compra,
                cl.Nombre_Cliente,
                e.Nombre_Empleado,
                l.Nombre AS local_nombre,
                mp.Nombre AS metodo_pago,
                p.Nombre AS producto,
                cp.Cantidad,
                cp.Precio_Venta,
                (cp.Cantidad * cp.Precio_Venta) AS subtotal
            FROM COMPRA c
            INNER JOIN CLIENTE cl ON cl.ID_Cliente = c.ID_Cliente
            INNER JOIN EMPLEADO e ON e.ID_Empleado = c.ID_Empleado
            INNER JOIN LOCAL l ON l.ID_Local = c.ID_Local
            INNER JOIN METODO_PAGO mp ON mp.ID_Metodo = c.ID_Metodo
            INNER JOIN COMPRA_PRODUCTOS cp ON cp.ID_Compra = c.ID_Compra
            INNER JOIN PRODUCTO p ON p.ID_Producto = cp.ID_Producto
            ORDER BY c.Fecha_Compra DESC, c.ID_Compra DESC, p.Nombre'
        );

        $filename = 'compras_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            // BOM para que Excel reconozca UTF-8
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'ID Compra', 'Fecha', 'Cliente', 'Empleado', 'Local',
                'Metodo', 'Producto', 'Cantidad', 'Precio venta', 'Subtotal',
            ]);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id_compra,
                    $r->fecha_compra,
                    $r->nombre_cliente,
                    $r->nombre_empleado,
                    $r->local_nombre,
                    $r->metodo_pago,
                    $r->producto,
                    $r->cantidad,
                    $r->precio_venta,
                    $r->subtotal,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            DB::statement('SELECT anular_compra(?)', [$id]);
            DB::commit();

            return redirect()->route('compras.index')->with('success', "Compra #{$id} anulada exitosamente.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al anular la compra: ' . $this->humanizeDbError($e));
        }
    }
}
