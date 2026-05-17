@extends('layouts.app')

@section('title', 'Nuevo producto | GameStore')

@section('content')
    <section class="panel">
        <h2>Crear nuevo producto</h2>
        <p class="lead">Completa los datos. Las categorías quedarán asociadas en <code>PRODUCTO_CATEGORIA</code>.</p>

        <form method="POST" action="{{ route('productos.store') }}" style="margin-top: 16px;">
            @csrf
            @include('pages.productos._form')

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar producto</button>
                <a href="{{ route('productos.index') }}" class="btn">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
