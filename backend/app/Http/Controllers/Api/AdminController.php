<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Averia;

class AdminController extends Controller
{
    /**
     * Ver TODOS los pedidos (Para la tabla del panel de admin)
     */
    public function getPedidos(Request $request)
    {
        // Seguridad: ¿Es administrador?
        if ($request->user()->rol !== 'admin') {
            return response()->json(['mensaje' => 'Acceso denegado. No eres administrador.'], 403);
        }

        // Traemos todos los pedidos, ordenados por los más recientes, y cargamos los datos del cliente
        $pedidos = Pedido::with('user')->orderBy('created_at', 'desc')->get();
        
        return response()->json($pedidos, 200);
    }

    /**
     * Cambiar el estado de un pedido (Ej: de 'recibido' a 'reparado')
     */
    public function updateEstadoPedido(Request $request, $id)
    {
        if ($request->user()->rol !== 'admin') {
            return response()->json(['mensaje' => 'Acceso denegado.'], 403);
        }

        // Validamos que el estado sea uno de los permitidos
        $request->validate([
            'estado' => 'required|in:recibido,en_diagnostico,esperando_piezas,reparado,enviado'
        ]);

        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json(['mensaje' => 'Pedido no encontrado'], 404);
        }

        // Actualizamos y guardamos
        $pedido->estado = $request->estado;
        $pedido->save();

        return response()->json([
            'mensaje' => 'Estado del pedido actualizado correctamente',
            'pedido' => $pedido
        ], 200);
    }

    /**
     * Añadir una nueva avería y precio a un dispositivo existente
     */
    public function storeAveria(Request $request)
    {
        if ($request->user()->rol !== 'admin') {
            return response()->json(['mensaje' => 'Acceso denegado.'], 403);
        }

        $request->validate([
            'dispositivo_id' => 'required|exists:dispositivos,id',
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0'
        ]);

        $averia = Averia::create([
            'dispositivo_id' => $request->dispositivo_id,
            'nombre' => $request->nombre,
            'precio' => $request->precio
        ]);

        return response()->json([
            'mensaje' => 'Nueva tarifa de avería creada',
            'averia' => $averia
        ], 201);
    }
}