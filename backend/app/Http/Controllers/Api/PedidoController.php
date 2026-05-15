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

    /**
     * Busca un pedido público por su código de seguimiento.
     */
    public function rastrear($codigo)
    {
        // Buscamos el pedido en MySQL donde el código coincida exactamente.
        // Además, usamos "with('dispositivo')" para traernos la marca y modelo del móvil en la misma consulta.
        $pedido = Pedido::with('dispositivo')
            ->where('codigo_seguimiento', $codigo)
            ->first();

        // Si no existe, devolvemos un error 404 para que Vue pinte el aviso en rojo
        if (!$pedido) {
            return response()->json([
                'message' => 'No se ha encontrado ninguna orden con ese código.'
            ], 404);
        }

        // Si existe, devolvemos todos los datos en formato JSON
        return response()->json($pedido);
    }

    /**
     * Devuelve únicamente los pedidos del usuario autenticado.
     */
    public function getMisPedidos(Request $request)
    {
        // Filtramos para que solo devuelva las órdenes donde user_id sea igual al ID del usuario que hace la petición
        $pedidos = Pedido::with('dispositivo')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($pedidos);
    }
}
