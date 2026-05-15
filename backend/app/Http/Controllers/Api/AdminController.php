<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Obtiene todos los pedidos con la información del cliente y dispositivo.
     */
    public function getPedidos()
    {
        // Cargamos las relaciones para tener todos los datos en el panel [cite: 75]
        $pedidos = Pedido::with(['usuario', 'lineasPedido.averia.dispositivo'])->get();
        return response()->json($pedidos);
    }

    /**
     * Actualiza el estado de una reparación (Recibido, En taller, Reparado, etc.).
     */
    public function updateEstadoPedido(Request $request, $id)
    {
        $request->validate(['estado' => 'required|string']);

        $pedido = Pedido::findOrFail($id);
        $pedido->estado = $request->estado;
        $pedido->save();

        return response()->json(['message' => 'Estado actualizado correctamente']);
    }

    //Simulación de recepción de solicitudes de restablecimiento de contraseña.
    public function getSolicitudesPassword()
    {
        // Aquí podrías filtrar usuarios que tengan un flag 'pide_reset' 
        // o consultar una tabla específica de solicitudes.
        return response()->json([
            ['id' => 1, 'email' => 'cliente@ejemplo.com', 'fecha' => '2026-05-14', 'mensaje' => 'Olvidé mi clave']
        ]);
    }
}
