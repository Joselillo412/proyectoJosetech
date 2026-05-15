<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    /**
     * Guarda un nuevo pedido de reparación en la base de datos.
     */
    public function store(Request $request)
    {
        // Validamos que lleguen los datos correctos desde Vue
        $request->validate([
            'dispositivo_id' => 'required|exists:dispositivos,id',
            'tipo_reparacion' => 'required|string',
            'descripcion' => 'nullable|string',
            'precio_estimado' => 'required|string'
        ]);

        // Creamos el pedido asignándolo automáticamente al usuario logueado
        $pedido = Pedido::create([
    'user_id' => $request->user()->id,
    'dispositivo_id' => $request->dispositivo_id,
    'tipo_reparacion' => $request->tipo_reparacion,
    'descripcion' => $request->descripcion,
    'precio_estimado' => $request->precio_estimado,
    'estado' => 'Pendiente'
]);

        return response()->json([
            'message' => 'Pedido registrado con éxito',
            'pedido' => $pedido
        ], 201);
    }
}