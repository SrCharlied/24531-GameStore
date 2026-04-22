<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
Route::get('/compras', [CompraController::class, 'index'])->name('compras.index');
Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
