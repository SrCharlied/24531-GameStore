<?php

use App\Http\Controllers\CompraController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.dashboard', [
        'stats' => [
            ['label' => 'Productos activos', 'value' => 25],
            ['label' => 'Compras del mes', 'value' => 18],
            ['label' => 'Locales monitoreados', 'value' => 5],
            ['label' => 'Reportes disponibles', 'value' => 4],
        ],
        'activity' => [
            'Listado de productos listo para conectar a PostgreSQL.',
            'Modulo de compras preparado con datos de ejemplo.',
            'Seccion de reportes reservada para consultas SQL complejas.',
        ],
    ]);
})->name('dashboard');

Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
Route::get('/compras', [CompraController::class, 'index'])->name('compras.index');
Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
