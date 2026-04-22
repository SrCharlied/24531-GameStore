@extends('layouts.app')

@section('title', 'Productos | GameStore')

@section('content')
    <section class="panel">
        <h2>Productos</h2>
        <p class="lead">
            Listado real de productos con JOIN a franquicias, categorias e inventario.
        </p>

        @if (!empty($dbError))
            <div class="alert" style="margin-top: 16px;">
                {{ $dbError }}
            </div>
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
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No hay productos para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
