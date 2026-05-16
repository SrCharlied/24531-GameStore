<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = DB::select('SELECT * FROM vw_producto_stock');
        return response()->json(['productos' => $productos]);
    }

    public function show($id)
    {
        $producto = DB::selectOne(
            'SELECT ID_Producto AS id, Nombre AS nombre, Descripcion AS descripcion,
                    Precio_Actual AS precio_actual, ID_Franquicia AS franquicia_id
             FROM PRODUCTO WHERE ID_Producto = ?',
            [$id]
        );

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        $rows = DB::select('SELECT ID_Categoria FROM PRODUCTO_CATEGORIA WHERE ID_Producto = ?', [$id]);
        $categorias_ids = array_map(fn ($r) => $r->id_categoria, $rows);

        $invRows = DB::select('SELECT ID_Local, Cantidad_Actual FROM INVENTARIO WHERE ID_Producto = ?', [$id]);
        $stock_por_local = [];
        foreach ($invRows as $r) {
            $stock_por_local[$r->id_local] = $r->cantidad_actual;
        }

        return response()->json([
            'producto'        => $producto,
            'categorias_ids'  => $categorias_ids,
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

            $producto = DB::selectOne(
                'INSERT INTO PRODUCTO (Nombre, Descripcion, ID_Franquicia, Precio_Actual)
                 VALUES (?, ?, ?, ?) RETURNING ID_Producto',
                [
                    $request->input('nombre'),
                    $request->input('descripcion', ''),
                    $request->input('franquicia'),
                    $request->input('precio_actual'),
                ]
            );

            foreach ($request->input('categorias', []) as $categoriaId) {
                DB::insert(
                    'INSERT INTO PRODUCTO_CATEGORIA (ID_Producto, ID_Categoria) VALUES (?, ?)',
                    [$producto->id_producto, $categoriaId]
                );
            }

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

            $affected = DB::update(
                'UPDATE PRODUCTO
                 SET Nombre = ?, Descripcion = ?, ID_Franquicia = ?, Precio_Actual = ?
                 WHERE ID_Producto = ?',
                [
                    $request->input('nombre'),
                    $request->input('descripcion', ''),
                    $request->input('franquicia'),
                    $request->input('precio_actual'),
                    $id,
                ]
            );

            if ($affected === 0) {
                DB::rollBack();
                return response()->json(['message' => 'Producto no encontrado.'], 404);
            }

            DB::delete('DELETE FROM PRODUCTO_CATEGORIA WHERE ID_Producto = ?', [$id]);
            foreach ($request->input('categorias', []) as $categoriaId) {
                DB::insert(
                    'INSERT INTO PRODUCTO_CATEGORIA (ID_Producto, ID_Categoria) VALUES (?, ?)',
                    [$id, $categoriaId]
                );
            }

            $this->upsertInventario($id, $request->input('stock', []));

            DB::commit();

            return response()->json(['message' => 'Producto actualizado exitosamente.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => $this->humanizeDbError($e)], 422);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            DB::delete('DELETE FROM PRODUCTO_CATEGORIA WHERE ID_Producto = ?', [$id]);
            $affected = DB::delete('DELETE FROM PRODUCTO WHERE ID_Producto = ?', [$id]);
            DB::commit();

            if ($affected === 0) {
                return response()->json(['message' => 'Producto no encontrado.'], 404);
            }
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
            DB::insert(
                'INSERT INTO INVENTARIO (ID_Producto, ID_Local, Cantidad_Actual)
                 VALUES (?, ?, ?)
                 ON CONFLICT (ID_Producto, ID_Local) DO UPDATE
                 SET Cantidad_Actual = EXCLUDED.Cantidad_Actual',
                [$idProducto, (int) $idLocal, (int) $cantidad]
            );
        }
    }
}
