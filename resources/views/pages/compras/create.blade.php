@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-4">Registrar Nueva Compra</h1>
        
        <form id="compra-form" action="{{ route('compras.store') }}" method="POST" class="bg-white p-6 rounded-lg shadow-md">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="cliente">
                    Cliente
                </label>
                <select id="cliente" name="cliente" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @foreach ($clientes as $c)
                        <option value="{{ $c->id_cliente }}">{{ $c->nombre_cliente }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="empleado">
                    Empleado
                </label>
                <select id="empleado" name="empleado" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @foreach ($empleados as $e)
                        <option value="{{ $e->id_empleado }}">{{ $e->nombre_empleado }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="local">
                    Local
                </label>
                <select id="local" name="local" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @foreach ($locales as $l)
                        <option value="{{ $l->id_local }}">{{ $l->nombre }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="metodo">
                    Método de Pago
                </label>
                <select id="metodo" name="metodo" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @foreach ($metodos_pago as $mp)
                        <option value="{{ $mp->id_metodo }}">{{ $mp->nombre }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Productos
                </label>
                <div id="productos-container">
                    <!-- Las líneas de productos se agregarán dinámicamente con JavaScript -->
                </div>
                <button type="button" id="agregar-producto" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Agregar Producto
                </button>
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Registrar Compra
                </button>
                <a href="{{ route('compras.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Volver al listado
                </a>
            </div>
        </form>
    </div>
</div>
@endsection