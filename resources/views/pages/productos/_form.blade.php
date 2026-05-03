document.addEventListener('DOMContentLoaded', function() {
    const categoriaContainer = document.getElementById('categoria-container');
    const categorias = categoriaContainer ? categoriaContainer.querySelectorAll('div.categoria-item input[type="checkbox"]') : [];
    
    // Añadir event listeners para las categorías
    if (categoriaContainer) {
        categorias.forEach(cbox => {
            cbox.addEventListener('change', function() {
                // Aquí puedes añadir lógica para manejar la selección de categorías
                // Por ejemplo, resaltar visualmente las categorías seleccionadas
            });
        });
    }
});