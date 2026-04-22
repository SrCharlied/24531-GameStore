@extends('layouts.app')

@section('title', 'Productos | GameStore')

@section('content')
    <section class="panel">
        <h2>Productos</h2>
        <p class="lead">
            Listado temporal de figuras para validar navegacion, estructura de vistas y flujo del modulo.
        </p>

        <table>
            <thead>
                <tr>
                    <th>Codigo</th>
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
                        <td>{{ $producto['codigo'] }}</td>
                        <td>{{ $producto['nombre'] }}</td>
                        <td>{{ $producto['franquicia'] }}</td>
                        <td><span class="tag">{{ $producto['categoria'] }}</span></td>
                        <td>${{ number_format($producto['precio'], 2) }}</td>
                        <td>{{ $producto['stock'] }}</td>
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
