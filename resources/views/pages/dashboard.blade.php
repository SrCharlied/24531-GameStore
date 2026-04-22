@extends('layouts.app')

@section('title', 'Dashboard | GameStore')

@section('content')
    <section class="panel">
        <h2>Dashboard</h2>
        <p class="lead">
            Resumen general cargado desde PostgreSQL. Esta pagina toma datos reales de productos, compras, locales
            e inventario.
        </p>
    </section>

    @if (!empty($dbError))
        <section class="alert">
            {{ $dbError }}
        </section>
    @endif

    <section class="grid cols-4">
        @foreach ($stats as $stat)
            <article class="card">
                <p class="eyebrow">{{ $stat['label'] }}</p>
                <p class="metric">{{ $stat['value'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="panel">
        <h3>Compras recientes</h3>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Local</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($activity as $item)
                    <tr>
                        <td>#{{ $item->id_compra }}</td>
                        <td>{{ $item->nombre_cliente }}</td>
                        <td>{{ $item->local_nombre }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($item->fecha_compra)->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No hay actividad para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
