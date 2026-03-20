<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DispositivoController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;

// ==========================================
// 🔓 RUTAS PÚBLICAS (No hace falta Login)
// ==========================================

// Autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Catálogo
Route::get('/dispositivos', [DispositivoController::class, 'index']);
Route::get('/dispositivos/{id}', [DispositivoController::class, 'show']);

// Seguimiento de pedido por código (cualquiera con el código puede verlo)
Route::get('/pedidos/track/{codigo}', [PedidoController::class, 'track']);


// ==========================================
// 🔒 RUTAS PRIVADAS (Requieren Token de Login)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // --- Rutas del Cliente ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/pedidos', [PedidoController::class, 'store']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // --- Rutas del Administrador ---
    Route::get('/admin/pedidos', [AdminController::class, 'getPedidos']);
    Route::put('/admin/pedidos/{id}/estado', [AdminController::class, 'updateEstadoPedido']);
    Route::post('/admin/averias', [AdminController::class, 'storeAveria']);
});