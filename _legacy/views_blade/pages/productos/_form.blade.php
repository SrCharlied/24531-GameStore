@php
    $isEdit = isset($producto);
    $valNombre = old('nombre', $isEdit ? $producto->nombre : '');
    $valDescripcion = old('descripcion', $isEdit ? ($producto->descripcion ?? '') : '');
    $valPrecio = old('precio_actual', $isEdit ? $producto->precio_actual : '');
    $valFranquicia = old('franquicia', $isEdit ? $producto->franquicia_id : '');
    $valCategorias = old('categorias', $isEdit ? ($categorias_ids ?? []) : []);
@endphp

<div class="form-field">
    <label for="nombre">Nombre del producto</label>
    <input type="text" id="nombre" name="nombre" value="{{ $valNombre }}" required>
    @error('nombre')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div class="form-field">
    <label for="descripcion">Descripción</label>
    <textarea id="descripcion" name="descripcion" rows="3">{{ $valDescripcion }}</textarea>
    @error('descripcion')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div class="form-field">
    <label for="precio_actual">Precio</label>
    <input type="number" step="0.01" min="0.01" id="precio_actual" name="precio_actual" value="{{ $valPrecio }}" required>
    @error('precio_actual')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div class="form-field">
    <label for="franquicia">Franquicia</label>
    <select id="franquicia" name="franquicia" required>
        <option value="">Seleccione una franquicia</option>
        @foreach ($franquicias as $f)
            <option value="{{ $f->id_franquicia }}" @selected((string) $valFranquicia === (string) $f->id_franquicia)>
                {{ $f->nombre_franquicia }}
            </option>
        @endforeach
    </select>
    @error('franquicia')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div class="form-field">
    <label>Categorías</label>
    <div class="checkbox-grid">
        @foreach ($categorias as $c)
            <label>
                <input type="checkbox" name="categorias[]" value="{{ $c->id_categoria }}"
                       @checked(in_array($c->id_categoria, $valCategorias))>
                {{ $c->nombre_categoria }}
            </label>
        @endforeach
    </div>
    @error('categorias')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div class="form-field">
    <label>Stock por local <span style="color: var(--muted); font-weight: normal; font-size: 0.85rem;">(deja vacío para no registrar inventario en ese local)</span></label>
    <div class="grid cols-3" style="gap: 12px;">
        @foreach ($locales as $l)
            @php
                $valStock = old("stock.{$l->id_local}", $stock_por_local[$l->id_local] ?? '');
            @endphp
            <div>
                <label style="font-weight: normal; font-size: 0.9rem; color: var(--muted);">
                    {{ $l->nombre }} <small>({{ $l->zona }})</small>
                </label>
                <input type="number" min="0" step="1" name="stock[{{ $l->id_local }}]" value="{{ $valStock }}" placeholder="0">
                @error("stock.{$l->id_local}")<p class="form-error">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>
</div>
