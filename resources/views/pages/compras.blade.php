@extends('layouts.app')

@section('title', 'Compras | GameStore')

@section('content')
    <section class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div>
                <h2 style="margin-bottom: 4px;">Compras</h2>
                <p class="lead">Historial real de compras calculado desde PostgreSQL.</p>
            </div>
            <a href="{{ route('compras.crear') }}" class="btn btn-primary">+ Registrar compra</a>
        </div>

        @if (!empty($dbError))
            <div class="alert" style="margin-top: 16px;">{{ $dbError }}</div>
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
                    <th>Acciones</th>
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
                        <td>
                            <form method="POST" action="{{ route('compras.destroy', $compra->id_compra) }}" class="inline-form" onsubmit="return confirm('¿Anular la compra #{{ $compra->id_compra }}? Se devolverá el inventario.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Anular</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No hay compras para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
