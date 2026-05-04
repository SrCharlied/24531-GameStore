<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = [];
        $dbError = null;

        try {
            $productos = DB::select('SELECT * FROM vw_producto_stock');
        } catch (\Throwable $exception) {
            $dbError = 'No fue posible cargar el listado de productos.';
        }

        return view('pages.productos', compact('productos', 'dbError'));
    }

    public function create()
    {
        $franquicias = DB::select('SELECT ID_Franquicia, Nombre_Franquicia FROM FRANQUICIA ORDER BY Nombre_Franquicia');
        $categorias = DB::select('SELECT ID_Categoria, Nombre_Categoria FROM CATEGORIA ORDER BY Nombre_Categoria');
        $locales = DB::select('SELECT ID_Local, Nombre, Zona FROM LOCAL ORDER BY Nombre');
        $stock_por_local = [];

        return view('pages.productos.create', compact('franquicias', 'categorias', 'locales', 'stock_por_local'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'precio_actual' => 'required|numeric|min:0.01',
            'franquicia' => 'required|exists:franquicia,id_franquicia',
            'categorias' => 'required|array|min:1',
            'categorias.*' => 'exists:categoria,id_categoria',
            'stock' => 'array',
            'stock.*' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $producto = DB::selectOne(
                'INSERT INTO PRODUCTO (Nombre, Descripcion, ID_Franquicia, Precio_Actual)
                 VALUES (?, ?, ?, ?)
                 RETURNING ID_Producto',
                [
                    $request->input('nombre'),
                    $request->input('descripcion', ''),
                    $request->input('franquicia'),
                    $request->input('precio_actual'),
                ]
            );

            foreach ($request->input('categorias') as $categoriaId) {
                DB::insert(
                    'INSERT INTO PRODUCTO_CATEGORIA (ID_Producto, ID_Categoria) VALUES (?, ?)',
                    [$producto->id_producto, $categoriaId]
                );
            }

            $this->upsertInventario($producto->id_producto, $request->input('stock', []));

            DB::commit();

            return redirect()->route('productos.index')->with('success', 'Producto creado exitosamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al crear el producto: ' . $this->humanizeDbError($e));
        }
    }

    public function edit($id)
    {
        $producto = DB::selectOne(
            'SELECT ID_Producto AS id, Nombre AS nombre, Descripcion AS descripcion,
                    Precio_Actual AS precio_actual, ID_Franquicia AS franquicia_id
             FROM PRODUCTO
             WHERE ID_Producto = ?',
            [$id]
        );

        if (!$producto) {
            return redirect()->route('productos.index')->with('error', 'Producto no encontrado.');
        }

        $rows = DB::select('SELECT ID_Categoria FROM PRODUCTO_CATEGORIA WHERE ID_Producto = ?', [$id]);
        $categorias_ids = array_map(fn ($r) => $r->id_categoria, $rows);

        $franquicias = DB::select('SELECT ID_Franquicia, Nombre_Franquicia FROM FRANQUICIA ORDER BY Nombre_Franquicia');
        $categorias = DB::select('SELECT ID_Categoria, Nombre_Categoria FROM CATEGORIA ORDER BY Nombre_Categoria');
        $locales = DB::select('SELECT ID_Local, Nombre, Zona FROM LOCAL ORDER BY Nombre');

        $invRows = DB::select('SELECT ID_Local, Cantidad_Actual FROM INVENTARIO WHERE ID_Producto = ?', [$id]);
        $stock_por_local = [];
        foreach ($invRows as $r) {
            $stock_por_local[$r->id_local] = $r->cantidad_actual;
        }

        return view('pages.productos.edit', compact('producto', 'categorias_ids', 'franquicias', 'categorias', 'locales', 'stock_por_local'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'precio_actual' => 'required|numeric|min:0.01',
            'franquicia' => 'required|exists:franquicia,id_franquicia',
            'categorias' => 'required|array|min:1',
            'categorias.*' => 'exists:categoria,id_categoria',
            'stock' => 'array',
            'stock.*' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            DB::update(
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

            DB::delete('DELETE FROM PRODUCTO_CATEGORIA WHERE ID_Producto = ?', [$id]);

            foreach ($request->input('categorias') as $categoriaId) {
                DB::insert(
                    'INSERT INTO PRODUCTO_CATEGORIA (ID_Producto, ID_Categoria) VALUES (?, ?)',
                    [$id, $categoriaId]
                );
            }

            $this->upsertInventario($id, $request->input('stock', []));

            DB::commit();

            return redirect()->route('productos.index')->with('success', 'Producto actualizado exitosamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el producto: ' . $this->humanizeDbError($e));
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            DB::delete('DELETE FROM PRODUCTO_CATEGORIA WHERE ID_Producto = ?', [$id]);
            DB::delete('DELETE FROM PRODUCTO WHERE ID_Producto = ?', [$id]);
            DB::commit();

            return redirect()->route('productos.index')->with('success', 'Producto eliminado exitosamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al eliminar el producto: ' . $this->humanizeDbError($e));
        }
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
