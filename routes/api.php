<?php

use App\Http\Controllers\Api\AuditoriaController;
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
});

// Compras: lectura para roles comerciales/auditoría; escritura solo admin/vendedor.
Route::middleware('auth.session:admin,gerente,vendedor,auditor')->group(function () {
    Route::get('/compras',             [CompraController::class, 'index']);
    Route::get('/compras/export.csv',  [CompraController::class, 'export']);
    Route::get('/compras/{id}',        [CompraController::class, 'show']);
});

Route::middleware('auth.session:admin,vendedor')->group(function () {
    Route::post('/compras',            [CompraController::class, 'store']);
    Route::delete('/compras/{id}',     [CompraController::class, 'destroy']);
});

// Reportes: roles con permiso de lectura global.
Route::middleware('auth.session:admin,gerente,auditor')->group(function () {
    Route::get('/dashboard',           [DashboardController::class, 'index']);
    Route::get('/reportes',            [ReporteController::class, 'index']);
    Route::get('/reportes/export.csv', [ReporteController::class, 'export']);
});

// Auditoría de cambios sensibles.
Route::middleware('auth.session:admin,auditor')->group(function () {
    Route::get('/auditoria/precios', [AuditoriaController::class, 'precios']);
});

// Productos: consulta para lectura; mutaciones solo admin/bodega.
Route::middleware('auth.session:admin,gerente,bodega,auditor')->group(function () {
    Route::get('/productos',           [ProductoController::class, 'index']);
    Route::get('/productos/{id}',      [ProductoController::class, 'show']);
});

Route::middleware('auth.session:admin,bodega')->group(function () {
    Route::post('/productos',          [ProductoController::class, 'store']);
    Route::put('/productos/{id}',      [ProductoController::class, 'update']);
    Route::delete('/productos/{id}',   [ProductoController::class, 'destroy']);
});
