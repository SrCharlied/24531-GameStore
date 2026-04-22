@extends('layouts.app')

@section('title', 'Reportes | GameStore')

@section('content')
    <section class="panel">
        <h2>Reportes</h2>
        <p class="lead">
            Esta seccion queda lista para incorporar consultas con JOIN, agregaciones, CTE, vistas y transacciones
            mas adelante.
        </p>
    </section>

    <section class="grid cols-3">
        @foreach ($reportes as $reporte)
            <article class="card">
                <p class="eyebrow">{{ $reporte['tipo_sql'] }}</p>
                <h3>{{ $reporte['nombre'] }}</h3>
                <p class="lead">{{ $reporte['descripcion'] }}</p>
            </article>
        @endforeach
    </section>
@endsection
