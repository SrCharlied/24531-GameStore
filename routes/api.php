<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogoController;
use App\Http\Controllers\Api\CompraController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\ReporteController;
use Illuminate\Support\Facades\Route;

// Públicas
Route::post('/login',  [AuthController::class, 'login']);

// Autenticadas (cualquier rol)
Route::middleware('auth.session')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    Route::get('/catalogos', [CatalogoController::class, 'index']);

    Route::get('/compras',             [CompraController::class, 'index']);
    Route::post('/compras',            [CompraController::class, 'store']);
    Route::delete('/compras/{id}',     [CompraController::class, 'destroy']);
    Route::get('/compras/export.csv',  [CompraController::class, 'export']);
});

// Solo admin
Route::middleware('auth.session:admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/reportes',  [ReporteController::class, 'index']);

    Route::get('/productos',           [ProductoController::class, 'index']);
    Route::post('/productos',          [ProductoController::class, 'store']);
    Route::get('/productos/{id}',      [ProductoController::class, 'show']);
    Route::put('/productos/{id}',      [ProductoController::class, 'update']);
    Route::delete('/productos/{id}',   [ProductoController::class, 'destroy']);
});
