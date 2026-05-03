document.addEventListener('DOMContentLoaded', function() {
    const productosContainer = document.getElementById('productos-container');
    const btnAgregarProducto = document.getElementById('agregar-producto');
    const productos = @json($productos); // Array de productos disponibles para autocompletado
    
    // Función para crear una nueva línea de producto
    function crearLineaProducto() {
        const lineaIndex = document.querySelectorAll('.linea-producto').length;
        
        const lineaDiv = document.createElement('div');
        lineaDiv.className = 'linea-producto mb-4 p-4 border border-gray-300 rounded';
        lineaDiv.innerHTML = `
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Producto</label>
                    <input type="text" class="producto-input shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           data-index="${lineaIndex}" placeholder="Buscar producto...">
                    <div class="sugerencias-productos mt-1"></div>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Cantidad</label>
                    <input type="number" class="cantidad-input shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           min="1" value="1">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Precio</label>
                    <input type="number" step="0.01" class="precio-input shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           value="0.00">
                </div>
            </div>
            <button type="button" class="quitar-linea bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded focus:outline-none focus:shadow-outline">
                Quitar
            </button>
        `;
        
        productosContainer.appendChild(lineaDiv);
        
        // Añadir event listeners para el autocompletado
        const productoInput = lineaDiv.querySelector('.producto-input');
        productoInput.addEventListener('input', function() {
            const valor = this.value.toLowerCase();
            const sugerencias = productos.filter(p => 
                p.nombre.toLowerCase().includes(valor)
            );
            
            mostrarSugerencias(sugerencias, this);
        });
        
        // Añadir event listener para el botón de quitar
        const btnQuitar = lineaDiv.querySelector('.quitar-linea');
        btnQuitar.addEventListener('click', function() {
            lineaDiv.remove();
        });
    }
    
    // Event listener para el botón de agregar producto
    btnAgregarProducto.addEventListener('click', crearLineaProducto);
    
    // Función para mostrar sugerencias de productos
    function mostrarSugerencias(sugerencias, input) {
        const sugerenciasDiv = input.nextElementSibling;
        sugerenciasDiv.innerHTML = '';
        
        if (sugerencias.length > 0) {
            const ul = document.createElement('ul');
            ul.className = 'bg-white border border-gray-300 rounded absolute z-10';
            
            sugerencias.forEach(s => {
                const li = document.createElement('li');
                li.className = 'p-2 hover:bg-gray-100 cursor-pointer';
                li.textContent = s.nombre;
                li.addEventListener('click', () => {
                    input.value = s.nombre;
                    // Completar automáticamente cantidad y precio
                    input.parentElement.parentElement.querySelector('.cantidad-input').value = 1;
                    input.parentElement.parentElement.querySelector('.precio-input').value = s.precio_actual;
                    sugerenciasDiv.innerHTML = '';
                });
                ul.appendChild(li);
            });
            
            sugerenciasDiv.appendChild(ul);
        }
    }
    
    // Inicializar con una línea de producto
    crearLineaProducto();
});