<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DatabaseRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        [$where, $params] = $this->buildCompraFilters($request);
        $compras = DB::select(
            "SELECT * FROM vw_compra_total {$where} ORDER BY fecha_compra DESC, id_compra DESC",
            $params
        );
        return response()->json(['compras' => $compras]);
    }

    public function show($id)
    {
        $compra = DB::selectOne(
            'SELECT
                c.ID_Compra, c.Fecha_Compra,
                cl.ID_Cliente, cl.Nombre_Cliente,
                e.ID_Empleado, e.Nombre_Empleado,
                l.ID_Local, l.Nombre AS local_nombre,
                mp.ID_Metodo, mp.Nombre AS metodo_pago,
                SUM(cp.Cantidad * cp.Precio_Venta) AS total_compra
             FROM COMPRA c
             INNER JOIN CLIENTE cl ON cl.ID_Cliente = c.ID_Cliente
             INNER JOIN EMPLEADO e ON e.ID_Empleado = c.ID_Empleado
             INNER JOIN LOCAL l ON l.ID_Local = c.ID_Local
             INNER JOIN METODO_PAGO mp ON mp.ID_Metodo = c.ID_Metodo
             INNER JOIN COMPRA_PRODUCTOS cp ON cp.ID_Compra = c.ID_Compra
             WHERE c.ID_Compra = ?
             GROUP BY c.ID_Compra, cl.ID_Cliente, cl.Nombre_Cliente,
                      e.ID_Empleado, e.Nombre_Empleado,
                      l.ID_Local, l.Nombre, mp.ID_Metodo, mp.Nombre',
            [$id]
        );

        if (!$compra) {
            return response()->json(['message' => 'Compra no encontrada.'], 404);
        }

        $lineas = DB::select(
            'SELECT
                p.ID_Producto, p.Nombre AS producto,
                cp.Cantidad, cp.Precio_Venta,
                (cp.Cantidad * cp.Precio_Venta) AS subtotal
             FROM COMPRA_PRODUCTOS cp
             INNER JOIN PRODUCTO p ON p.ID_Producto = cp.ID_Producto
             WHERE cp.ID_Compra = ?
             ORDER BY p.Nombre',
            [$id]
        );

        return response()->json([
            'compra' => $compra,
            'lineas' => $lineas,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cliente'  => 'required|exists:cliente,id_cliente',
            'empleado' => 'required|exists:empleado,id_empleado',
            'local'    => 'required|exists:local,id_local',
            'metodo'   => 'required|exists:metodo_pago,id_metodo',
            'productos'            => 'required|array|min:1',
            'productos.*.id'       => 'required|exists:producto,id_producto',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio'   => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
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
            DatabaseRole::applyForUser($request->user());

            // Invocación del stored procedure sp_registrar_compra.
            // El último argumento (NULL) corresponde al parámetro OUT p_id_compra,
            // que Postgres devuelve como resultado del CALL.
            $row = DB::selectOne(
                'CALL sp_registrar_compra(?, ?, ?, ?, ?::INT[], ?::INT[], ?::NUMERIC[], NULL)',
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
            // El SP devuelve la columna `p_id_compra` con el OUT.
            $row = (object) ['id_compra' => $row->p_id_compra ?? $row->id_compra ?? null];

            DB::commit();

            return response()->json([
                'message'   => "Compra #{$row->id_compra} registrada exitosamente.",
                'id_compra' => $row->id_compra,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => $this->humanizeDbError($e)], 422);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            DatabaseRole::applyForUser($request->user());
            // Invocación del stored procedure sp_anular_compra (no tiene OUT).
            DB::statement('CALL sp_anular_compra(?)', [$id]);
            DB::commit();

            return response()->json(['message' => "Compra #{$id} anulada exitosamente."]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => $this->humanizeDbError($e)], 422);
        }
    }

    public function export(Request $request)
    {
        [$where, $params] = $this->buildCompraFilters($request, 'c');
        $rows = DB::select(
            "SELECT
                c.ID_Compra, c.Fecha_Compra, cl.Nombre_Cliente, e.Nombre_Empleado,
                l.Nombre AS local_nombre, mp.Nombre AS metodo_pago,
                p.Nombre AS producto, cp.Cantidad, cp.Precio_Venta,
                (cp.Cantidad * cp.Precio_Venta) AS subtotal
            FROM COMPRA c
            INNER JOIN CLIENTE cl ON cl.ID_Cliente = c.ID_Cliente
            INNER JOIN EMPLEADO e ON e.ID_Empleado = c.ID_Empleado
            INNER JOIN LOCAL l ON l.ID_Local = c.ID_Local
            INNER JOIN METODO_PAGO mp ON mp.ID_Metodo = c.ID_Metodo
            INNER JOIN COMPRA_PRODUCTOS cp ON cp.ID_Compra = c.ID_Compra
            INNER JOIN PRODUCTO p ON p.ID_Producto = cp.ID_Producto
            {$where}
            ORDER BY c.Fecha_Compra DESC, c.ID_Compra DESC, p.Nombre",
            $params
        );

        $filename = 'compras_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'ID Compra', 'Fecha', 'Cliente', 'Empleado', 'Local',
                'Metodo', 'Producto', 'Cantidad', 'Precio venta', 'Subtotal',
            ]);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id_compra, $r->fecha_compra, $r->nombre_cliente,
                    $r->nombre_empleado, $r->local_nombre, $r->metodo_pago,
                    $r->producto, $r->cantidad, $r->precio_venta, $r->subtotal,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function buildCompraFilters(Request $request, ?string $tableAlias = null): array
    {
        $prefix = $tableAlias ? $tableAlias . '.' : '';
        $where = [];
        $params = [];

        if ($request->filled('q')) {
            $term = '%' . trim($request->query('q')) . '%';
            if ($tableAlias) {
                $where[] = '(cl.Nombre_Cliente ILIKE ? OR e.Nombre_Empleado ILIKE ? OR l.Nombre ILIKE ?)';
            } else {
                $where[] = '(nombre_cliente ILIKE ? OR nombre_empleado ILIKE ? OR local_nombre ILIKE ?)';
            }
            array_push($params, $term, $term, $term);
        }

        if ($request->filled('local')) {
            $where[] = $prefix . 'id_local = ?';
            $params[] = (int) $request->query('local');
        }

        if ($request->filled('desde')) {
            $where[] = $prefix . 'fecha_compra::date >= ?';
            $params[] = $request->query('desde');
        }

        if ($request->filled('hasta')) {
            $where[] = $prefix . 'fecha_compra::date <= ?';
            $params[] = $request->query('hasta');
        }

        return [count($where) ? 'WHERE ' . implode(' AND ', $where) : '', $params];
    }
}
