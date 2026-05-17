<?php

use Illuminate\Support\Facades\Route;

// El backend es solo API. Todas las rutas de la app están en routes/api.php.
// Esta ruta raíz responde con un mensaje informativo para visitantes que
// lleguen al puerto del backend desde el navegador.
Route::get('/', function () {
    return response()->json([
        'message' => 'GameStore API. Consulta /api/* para los endpoints.',
        'frontend' => 'http://localhost:5173',
    ]);
});
