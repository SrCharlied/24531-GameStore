@extends('layouts.app')

@section('title', 'Registrar compra | GameStore')

@section('content')
    <section class="panel">
        <h2>Registrar nueva compra</h2>
        <p class="lead">La compra se registra dentro de una transacción mediante <code>registrar_compra()</code>: se inserta en <code>COMPRA</code>, se crean líneas en <code>COMPRA_PRODUCTOS</code> y se descuenta inventario, todo o nada.</p>

        <form method="POST" action="{{ route('compras.store') }}" style="margin-top: 16px;">
            @csrf

            <div class="grid cols-3">
                <div class="form-field">
                    <label for="cliente">Cliente</label>
                    <select id="cliente" name="cliente" required>
                        @foreach ($clientes as $c)
                            <option value="{{ $c->id_cliente }}" @selected(old('cliente') == $c->id_cliente)>{{ $c->nombre_cliente }}</option>
                        @endforeach
                    </select>
                    @error('cliente')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-field">
                    <label for="empleado">Empleado</label>
                    <select id="empleado" name="empleado" required>
                        @foreach ($empleados as $e)
                            <option value="{{ $e->id_empleado }}" @selected(old('empleado') == $e->id_empleado)>{{ $e->nombre_empleado }}</option>
                        @endforeach
                    </select>
                    @error('empleado')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-field">
                    <label for="local">Local</label>
                    <select id="local" name="local" required>
                        @foreach ($locales as $l)
                            <option value="{{ $l->id_local }}" @selected(old('local') == $l->id_local)>{{ $l->nombre }}</option>
                        @endforeach
                    </select>
                    @error('local')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-field">
                <label for="metodo">Método de pago</label>
                <select id="metodo" name="metodo" required>
                    @foreach ($metodos_pago as $mp)
                        <option value="{{ $mp->id_metodo }}" @selected(old('metodo') == $mp->id_metodo)>{{ $mp->nombre }}</option>
                    @endforeach
                </select>
                @error('metodo')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field">
                <label>Productos</label>
                <div id="productos-container"></div>
                <button type="button" id="agregar-producto" class="btn btn-sm" style="margin-top: 8px;">+ Agregar línea</button>
                @error('productos')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Registrar compra</button>
                <a href="{{ route('compras.index') }}" class="btn">Cancelar</a>
            </div>
        </form>

        <template id="linea-template">
            <div class="compra-line" data-line>
                <div class="form-field">
                    <label>Producto</label>
                    <select name="productos[__INDEX__][id]" data-role="producto" required>
                        <option value="">Seleccione un producto</option>
                        @foreach ($productos as $p)
                            <option value="{{ $p->id_producto }}" data-precio="{{ $p->precio_actual }}">{{ $p->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label>Cantidad</label>
                    <input type="number" min="1" name="productos[__INDEX__][cantidad]" data-role="cantidad" value="1" required>
                </div>
                <div class="form-field">
                    <label>Precio</label>
                    <input type="number" step="0.01" min="0.01" name="productos[__INDEX__][precio]" data-role="precio" required>
                </div>
                <button type="button" class="btn btn-sm btn-danger" data-role="quitar" title="Quitar línea">×</button>
            </div>
        </template>
    </section>

    <script>
        (function () {
            const container = document.getElementById('productos-container');
            const template = document.getElementById('linea-template');
            const addBtn = document.getElementById('agregar-producto');
            let counter = 0;

            function addLine() {
                const idx = counter++;
                const html = template.innerHTML.replace(/__INDEX__/g, idx);
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html;
                const line = wrapper.firstElementChild;
                container.appendChild(line);

                const select = line.querySelector('[data-role="producto"]');
                const precio = line.querySelector('[data-role="precio"]');
                select.addEventListener('change', function () {
                    const opt = select.options[select.selectedIndex];
                    const p = opt.getAttribute('data-precio');
                    if (p && !precio.value) precio.value = p;
                });

                line.querySelector('[data-role="quitar"]').addEventListener('click', function () {
                    line.remove();
                });
            }

            addBtn.addEventListener('click', addLine);
            addLine();
        })();
    </script>
@endsection
