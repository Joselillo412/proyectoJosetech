<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\User;
use App\Mail\PedidoClienteMail;
use App\Mail\PedidoAdminMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PedidoController extends Controller
{
    /**
     * Guarda un nuevo pedido de reparación en la base de datos y notifica por email.
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
        // Añadimos ->load() para que tenga listos los datos del cliente y móvil para el correo
        $pedido = Pedido::create([
            'user_id' => $request->user()->id,
            'dispositivo_id' => $request->dispositivo_id,
            'tipo_reparacion' => $request->tipo_reparacion,
            'descripcion' => $request->descripcion,
            'precio_estimado' => $request->precio_estimado,
            'estado' => 'Pendiente'
        ])->load(['usuario', 'dispositivo']);

        // --- BLOQUE DE ENVÍO DE CORREOS ---
        try {
            // 1. Disparamos el correo bonito hacia el email del cliente
            Mail::to($pedido->usuario->email)->send(new PedidoClienteMail($pedido));

            // 2. Buscamos todos los correos de tu base de datos que tengan rol 'admin'
            $admins = User::where('rol', 'admin')->pluck('email');

            // Si hay administradores, les mandamos la alerta de nuevo pedido
            if ($admins->isNotEmpty()) {
                Mail::to($admins)->send(new PedidoAdminMail($pedido));
            }
        } catch (\Exception $e) {
            // Si hay algún problema con el servidor de correo o internet, se guarda en el log
            // de Laravel, pero el pedido se confirma igual para no molestar al cliente.
            \Log::error('Error al enviar los emails de notificación: ' . $e->getMessage());
        }

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
