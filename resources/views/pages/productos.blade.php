@extends('layouts.app')

@section('title', 'Productos | GameStore')

@section('content')
    <section class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div>
                <h2 style="margin-bottom: 4px;">Productos</h2>
                <p class="lead">Listado real de productos con JOIN a franquicias, categorias e inventario.</p>
            </div>
            <a href="{{ route('productos.crear') }}" class="btn btn-primary">+ Crear producto</a>
        </div>

        @if (!empty($dbError))
            <div class="alert" style="margin-top: 16px;">{{ $dbError }}</div>
        @endif

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Franquicia</th>
                    <th>Categoria</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($productos as $producto)
                    <tr>
                        <td>#{{ $producto->id_producto }}</td>
                        <td>{{ $producto->nombre }}</td>
                        <td>{{ $producto->franquicia }}</td>
                        <td><span class="tag">{{ $producto->categoria }}</span></td>
                        <td>${{ number_format($producto->precio_actual, 2) }}</td>
                        <td>{{ $producto->stock_total }}</td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('productos.edit', $producto->id_producto) }}" class="btn btn-sm">Editar</a>
                                <form method="POST" action="{{ route('productos.destroy', $producto->id_producto) }}" class="inline-form" onsubmit="return confirm('¿Eliminar este producto?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No hay productos para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
