<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use App\Models\Producto;
use App\Support\DatabaseRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $sql = 'SELECT * FROM vw_producto_stock WHERE 1 = 1';
        $params = [];

        if ($request->filled('q')) {
            $term = '%' . trim($request->query('q')) . '%';
            $sql .= ' AND (nombre ILIKE ? OR franquicia ILIKE ? OR categoria ILIKE ?)';
            array_push($params, $term, $term, $term);
        }

        if ($request->filled('franquicia')) {
            $sql .= ' AND id_franquicia = ?';
            $params[] = (int) $request->query('franquicia');
        }

        if ($request->filled('categoria')) {
            $sql .= ' AND EXISTS (
                SELECT 1
                FROM producto_categoria pc
                WHERE pc.id_producto = vw_producto_stock.id_producto
                  AND pc.id_categoria = ?
            )';
            $params[] = (int) $request->query('categoria');
        }

        if ($request->filled('stock')) {
            $sql .= match ($request->query('stock')) {
                'sin_stock' => ' AND stock_total = 0',
                'bajo' => ' AND stock_total BETWEEN 1 AND 10',
                'disponible' => ' AND stock_total > 10',
                default => '',
            };
        }

        $sql .= ' ORDER BY id_producto';

        $productos = DB::select($sql, $params);
        return response()->json(['productos' => $productos]);
    }

    public function show($id)
    {
        $producto = Producto::with([
            'categorias:id_categoria',
            'inventarios:id_producto,id_local,cantidad_actual',
        ])->find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        $stock_por_local = [];
        foreach ($producto->inventarios as $inventario) {
            $stock_por_local[$inventario->id_local] = $inventario->cantidad_actual;
        }

        return response()->json([
            'producto' => [
                'id' => $producto->id_producto,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio_actual' => $producto->precio_actual,
                'franquicia_id' => $producto->id_franquicia,
            ],
            'categorias_ids' => $producto->categorias
                ->pluck('id_categoria')
                ->values()
                ->all(),
            'stock_por_local' => $stock_por_local,
        ]);
    }

    public function store(Request $request)
    {
        $validator = $this->makeValidator($request);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            DatabaseRole::applyForUser($request->user());

            $producto = Producto::create([
                'nombre' => $request->input('nombre'),
                'descripcion' => $request->input('descripcion', ''),
                'id_franquicia' => $request->input('franquicia'),
                'precio_actual' => $request->input('precio_actual'),
            ]);

            $producto->categorias()->sync($request->input('categorias', []));
            $this->upsertInventario($producto->id_producto, $request->input('stock', []));

            DB::commit();

            return response()->json([
                'message' => 'Producto creado exitosamente.',
                'id'      => $producto->id_producto,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => $this->humanizeDbError($e),
            ], 422);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = $this->makeValidator($request);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            DatabaseRole::applyForUser($request->user());

            $producto = Producto::find($id);
            if (!$producto) {
                DB::rollBack();
                return response()->json(['message' => 'Producto no encontrado.'], 404);
            }

            $producto->fill([
                'nombre' => $request->input('nombre'),
                'descripcion' => $request->input('descripcion', ''),
                'id_franquicia' => $request->input('franquicia'),
                'precio_actual' => $request->input('precio_actual'),
            ])->save();

            $producto->categorias()->sync($request->input('categorias', []));
            $this->upsertInventario($producto->id_producto, $request->input('stock', []));

            DB::commit();

            return response()->json(['message' => 'Producto actualizado exitosamente.']);
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

            $producto = Producto::find($id);
            if (!$producto) {
                DB::rollBack();
                return response()->json(['message' => 'Producto no encontrado.'], 404);
            }

            $producto->categorias()->detach();
            $producto->delete();
            DB::commit();

            return response()->json(['message' => 'Producto eliminado exitosamente.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => $this->humanizeDbError($e)], 422);
        }
    }

    private function makeValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'nombre'        => 'required|string|max:150',
            'descripcion'   => 'nullable|string',
            'precio_actual' => 'required|numeric|min:0.01',
            'franquicia'    => 'required|exists:franquicia,id_franquicia',
            'categorias'    => 'required|array|min:1',
            'categorias.*'  => 'exists:categoria,id_categoria',
            'stock'         => 'array',
            'stock.*'       => 'nullable|integer|min:0',
        ]);
    }

    private function upsertInventario(int $idProducto, array $stock): void
    {
        foreach ($stock as $idLocal => $cantidad) {
            if ($cantidad === null || $cantidad === '') {
                continue;
            }

            Inventario::updateOrCreate(
                [
                    'id_producto' => $idProducto,
                    'id_local' => (int) $idLocal,
                ],
                [
                    'cantidad_actual' => (int) $cantidad,
                ]
            );
        }
    }
}
