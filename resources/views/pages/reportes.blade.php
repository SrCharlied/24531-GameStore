@extends('layouts.app')

@section('title', 'Reportes | GameStore')

@section('content')
    <section class="panel">
        <h2>Reportes</h2>
        <p class="lead">
            Reportes reales construidos con SQL explicito sobre PostgreSQL.
        </p>
    </section>

    @if (!empty($dbError))
        <section class="alert">
            {{ $dbError }}
        </section>
    @endif

    <section class="panel">
        <p class="eyebrow">GROUP BY + SUM</p>
        <h3>Ventas por local</h3>

        <table>
            <thead>
                <tr>
                    <th>Local</th>
                    <th>Compras</th>
                    <th>Ingreso total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ventasPorLocal as $fila)
                    <tr>
                        <td>{{ $fila->local_nombre }}</td>
                        <td>{{ $fila->total_compras }}</td>
                        <td>${{ number_format($fila->ingreso_total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No hay datos para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="panel">
        <p class="eyebrow">CTE</p>
        <h3>Top productos vendidos</h3>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Unidades</th>
                    <th>Ingreso</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($topProductos as $fila)
                    <tr>
                        <td>#{{ $fila->id_producto }}</td>
                        <td>{{ $fila->nombre }}</td>
                        <td>{{ $fila->unidades_vendidas }}</td>
                        <td>${{ number_format($fila->ingreso_generado, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No hay datos para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="panel">
        <p class="eyebrow">Subquery</p>
        <h3>Clientes por encima del promedio de gasto</h3>

        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Compras</th>
                    <th>Total gastado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clientesDestacados as $fila)
                    <tr>
                        <td>{{ $fila->nombre_cliente }}</td>
                        <td>{{ $fila->total_compras }}</td>
                        <td>${{ number_format($fila->total_gastado, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No hay datos para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
