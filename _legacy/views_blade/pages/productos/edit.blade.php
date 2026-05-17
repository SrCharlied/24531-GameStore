@extends('layouts.app')

@section('title', 'Editar producto | GameStore')

@section('content')
    <section class="panel">
        <h2>Editar producto #{{ $producto->id }}</h2>
        <p class="lead">Cualquier cambio de precio quedará registrado en <code>LOG_PRECIOS_PRODUCTO</code> por el trigger.</p>

        <form method="POST" action="{{ route('productos.update', $producto->id) }}" style="margin-top: 16px;">
            @csrf
            @method('PUT')
            @include('pages.productos._form')

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a href="{{ route('productos.index') }}" class="btn">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
