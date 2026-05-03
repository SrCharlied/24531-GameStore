@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-4">Crear Nuevo Producto</h1>
        
        <form action="{{ route('productos.store') }}" method="POST" class="bg-white p-6 rounded-lg shadow-md">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="nombre">
                    Nombre del Producto
                </label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                @error('nombre')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="precio_actual">
                    Precio
                </label>
                <input type="number" step="0.01" id="precio_actual" name="precio_actual" value="{{ old('precio_actual') }}" 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                @error('precio_actual')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="franquicia">
                    Franquicia
                </label>
                <select id="franquicia" name="franquicia" 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Seleccione una franquicia</option>
                    @foreach ($franquicias as $franquicia)
                        <option value="{{ $franquicia->id_franquicia }}" {{ old('franquicia') == $franquicia->id_franquicia ? 'selected' : '' }}>
                            {{ $franquicia->nombre_franquicia }}
                        </option>
                    @endforeach
                </select>
                @error('franquicia')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Categorías
                </label>
                <div id="categoria-container" class="grid grid-cols-2 gap-2">
                    @foreach ($categorias as $categoria)
                        <div class="categoria-item flex items-center">
                            <input type="checkbox" id="categoria_{{ $categoria->id_categoria }}" name="categorias[]" value="{{ $categoria->id_categoria }}" 
                                   class="mr-2" {{ in_array($categoria->id_categoria, old('categorias', [])) ? 'checked' : '' }}>
                            <label for="categoria_{{ $categoria->id_categoria }}" class="text-gray-700">{{ $categoria->nombre_categoria }}</label>
                        </div>
                    @endforeach
                </div>
                @error('categorias')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Crear Producto
                </button>
                <a href="{{ route('productos.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Volver al listado
                </a>
            </div>
        </form>
    </div>
</div>
@endsection