@extends('layouts.app')

@section('title', 'Compras | GameStore')

@section('content')
    <section class="panel">
        <h2>Compras</h2>
        <p class="lead">
            Historial real de compras calculado desde PostgreSQL.
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
                    <th>Cliente</th>
                    <th>Empleado</th>
                    <th>Local</th>
                    <th>Fecha</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($compras as $compra)
                    <tr>
                        <td>#{{ $compra->id_compra }}</td>
                        <td>{{ $compra->nombre_cliente }}</td>
                        <td>{{ $compra->nombre_empleado }}</td>
                        <td>{{ $compra->local_nombre }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($compra->fecha_compra)->format('Y-m-d H:i') }}</td>
                        <td>${{ number_format($compra->total_compra, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No hay compras para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
