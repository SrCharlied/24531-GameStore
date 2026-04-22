@extends('layouts.app')

@section('title', 'Dashboard | GameStore')

@section('content')
    <section class="panel">
        <h2>Dashboard</h2>
        <p class="lead">
            Base inicial del proyecto. Esta vista muestra datos placeholder mientras conectamos Laravel con PostgreSQL
            y convertimos los reportes en consultas SQL reales.
        </p>
    </section>

    <section class="grid cols-4">
        @foreach ($stats as $stat)
            <article class="card">
                <p class="eyebrow">{{ $stat['label'] }}</p>
                <p class="metric">{{ $stat['value'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="panel">
        <h3>Estado actual</h3>
        <ul class="simple-list">
            @foreach ($activity as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    </section>
@endsection
