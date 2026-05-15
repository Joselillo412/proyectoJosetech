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
Route::get('/pedidos/seguimiento/{codigo}', [PedidoController::class, 'rastrear']);


//  RUTAS PRIVADAS
Route::middleware('auth:sanctum')->group(function () {

    // --- Rutas Generales del Cliente ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/pedidos', [PedidoController::class, 'store']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/mis-pedidos', [PedidoController::class, 'getMisPedidos']);
    Route::put('/user/update', [UserController::class, 'updateProfile']);

    // Administrador 
    Route::prefix('admin')->group(function () {
        // Pedidos
        Route::get('/pedidos', [AdminController::class, 'getPedidos']);
        Route::put('/pedidos/{id}/estado', [AdminController::class, 'actualizarEstadoPedido']);

        // Usuarios
        Route::get('/usuarios', [AdminController::class, 'getUsuarios']);
        Route::put('/usuarios/{id}/rol', [AdminController::class, 'actualizarRolUsuario']);
        Route::put('/usuarios/{id}/password', [AdminController::class, 'actualizarPasswordUsuario']);

        // Dispositivos (Crear, Editar, Borrar)
        Route::post('/dispositivos', [AdminController::class, 'storeDispositivo']);
        Route::put('/dispositivos/{id}', [AdminController::class, 'updateDispositivo']);
        Route::delete('/dispositivos/{id}', [AdminController::class, 'destroyDispositivo']);
    });
});
