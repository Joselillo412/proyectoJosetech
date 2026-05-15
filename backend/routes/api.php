<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DispositivoController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;

// ==========================================
// 🔓 RUTAS PÚBLICAS (No exigen Login)
// ==========================================

// Autenticación y Recuperación
Route::post('/registro', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password-forgot', [AuthController::class, 'solicitarReset']); // <-- Movida a pública

// Catálogo
Route::get('/dispositivos', [DispositivoController::class, 'index']);
Route::get('/dispositivos/{id}', [DispositivoController::class, 'show']);

// Seguimiento de pedido por código
Route::get('/pedidos/track/{codigo}', [PedidoController::class, 'track']);



//  RUTAS PRIVADAS
Route::middleware('auth:sanctum')->group(function () {

    // --- Rutas Generales del Cliente ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/pedidos', [PedidoController::class, 'store']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Administrador 
    Route::middleware('admin')->group(function () {
        Route::get('/admin/pedidos', [AdminController::class, 'getPedidos']);
        Route::put('/admin/pedidos/{id}/estado', [AdminController::class, 'updateEstadoPedido']);
        Route::post('/admin/averias', [AdminController::class, 'storeAveria']);
        Route::get('/admin/solicitudes-password', [AdminController::class, 'getSolicitudesPassword']);
    });
});
