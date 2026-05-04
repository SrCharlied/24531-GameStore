<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

// Rutas públicas (login)
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login.show');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Empleado y admin: gestión de compras
Route::middleware('auth.session')->group(function () {
    Route::get('/compras',           [CompraController::class, 'index'])->name('compras.index');
    Route::get('/compras/crear',     [CompraController::class, 'create'])->name('compras.crear');
    Route::get('/compras/exportar',  [CompraController::class, 'export'])->name('compras.export');
    Route::post('/compras',          [CompraController::class, 'store'])->name('compras.store');
    Route::delete('/compras/{id}',   [CompraController::class, 'destroy'])->name('compras.destroy');
});

// Solo admin: dashboard, productos, reportes
Route::middleware('auth.session:admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/productos',                [ProductoController::class, 'index'])->name('productos.index');
    Route::get('/productos/crear',          [ProductoController::class, 'create'])->name('productos.crear');
    Route::post('/productos',               [ProductoController::class, 'store'])->name('productos.store');
    Route::get('/productos/{id}/editar',    [ProductoController::class, 'edit'])->name('productos.edit');
    Route::put('/productos/{id}',           [ProductoController::class, 'update'])->name('productos.update');
    Route::delete('/productos/{id}',        [ProductoController::class, 'destroy'])->name('productos.destroy');

    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
});
