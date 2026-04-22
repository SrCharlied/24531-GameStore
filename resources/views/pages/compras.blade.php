@extends('layouts.app')

@section('title', 'Compras | GameStore')

@section('content')
    <section class="panel">
        <h2>Compras</h2>
        <p class="lead">
            Vista preliminar del historial de compras. Por ahora los registros provienen de arrays dummy del controlador.
        </p>

        <table>
            <thead>
                <tr>
                    <th>Numero</th>
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
                        <td>{{ $compra['numero'] }}</td>
                        <td>{{ $compra['cliente'] }}</td>
                        <td>{{ $compra['empleado'] }}</td>
                        <td>{{ $compra['local'] }}</td>
                        <td>{{ $compra['fecha'] }}</td>
                        <td>${{ number_format($compra['total'], 2) }}</td>
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
