<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InventarioController extends Controller
{
    /**
     * POST /api/inventario/reabastecer
     *
     * Llama al stored procedure sp_reabastecer_inventario, que utiliza
     * transacciones explícitas con COMMIT/ROLLBACK dentro del SP.
     *
     * IMPORTANTE: este endpoint NO envuelve la llamada en
     * DB::beginTransaction() porque el SP gestiona su propia transacción.
     * El control de acceso a este endpoint se hace a nivel de aplicación
     * (middleware auth.session:admin,bodega) y a nivel de DBMS por los
     * GRANT EXECUTE definidos en 11-stored-procedures.sql.
     *
     * Body esperado:
     *   {
     *     "local": 1,
     *     "items": [
     *       { "producto_id": 1, "cantidad": 10 },
     *       { "producto_id": 5, "cantidad": 4 }
     *     ]
     *   }
     */
    public function reabastecer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'local'              => 'required|exists:local,id_local',
            'items'              => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:producto,id_producto',
            'items.*.cantidad'    => 'required|integer|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $items = $request->input('items');
        $productos  = array_map(fn ($r) => (int) $r['producto_id'], $items);
        $cantidades = array_map(fn ($r) => (int) $r['cantidad'], $items);

        try {
            // NO DB::beginTransaction(): el SP usa COMMIT/ROLLBACK internamente.
            $row = DB::selectOne(
                'CALL sp_reabastecer_inventario(?, ?::INT[], ?::INT[], 0)',
                [
                    (int) $request->input('local'),
                    '{' . implode(',', $productos)  . '}',
                    '{' . implode(',', $cantidades) . '}',
                ]
            );

            return response()->json([
                'message'      => 'Inventario reabastecido.',
                'actualizados' => $row->p_actualizados ?? 0,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $this->humanizeDbError($e)], 422);
        }
    }
}
