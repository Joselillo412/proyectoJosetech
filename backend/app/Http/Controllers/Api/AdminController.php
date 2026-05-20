<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\User;
use App\Models\Dispositivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Servicio;
use App\Models\Contabilidad;

class AdminController extends Controller
{
    /**
     * Validador de seguridad interno.
     * Expulsa cualquier petición de un token que no pertenezca a un Admin.
     */
    private function checkAdmin(Request $request)
    {
        if ($request->user()->rol !== 'admin') {
            abort(403, 'Acceso denegado. No tienes privilegios de administrador.');
        }
    }

    // ==========================================
    // MÓDULO 1: PEDIDOS
    // ==========================================
    public function getPedidos(Request $request)
    {
        $this->checkAdmin($request);
        // Traemos los pedidos con la info del cliente y el modelo de móvil
        $pedidos = Pedido::with(['usuario', 'dispositivo'])->latest()->get();
        return response()->json($pedidos);
    }

    public function actualizarEstadoPedido(Request $request, $id)
    {
        $this->checkAdmin($request);

        $request->validate(['estado' => 'required|string']);
        $pedido = Pedido::findOrFail($id);
        $pedido->update(['estado' => $request->estado]);

        return response()->json(['message' => 'Estado del pedido actualizado.']);
    }

    // ==========================================
    // MÓDULO 2: USUARIOS
    // ==========================================
    public function getUsuarios(Request $request)
    {
        $this->checkAdmin($request);
        $usuarios = User::latest()->get();
        return response()->json($usuarios);
    }

    public function actualizarRolUsuario(Request $request, $id)
    {
        $this->checkAdmin($request);

        $request->validate(['rol' => 'required|in:admin,cliente']);
        $user = User::findOrFail($id);
        $user->update(['rol' => $request->rol]);

        return response()->json(['message' => 'Rol de usuario actualizado.']);
    }

    public function actualizarPasswordUsuario(Request $request, $id)
    {
        $this->checkAdmin($request);

        $request->validate(['password' => 'required|string|min:6']);
        $user = User::findOrFail($id);

        // Encriptamos la nueva contraseña antes de guardarla en la BD
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'Contraseña forzada con éxito.']);
    }

    // ==========================================
    // MÓDULO 3: DISPOSITIVOS (CATÁLOGO)
    // ==========================================
    public function storeDispositivo(Request $request)
    {
        $this->checkAdmin($request);

        $request->validate([
            'marca' => 'required|string',
            'modelo' => 'required|string',
            'tipo' => 'required|string',
            'imagen_url' => 'required|url'
        ]);

        $dispositivo = Dispositivo::create($request->all());
        return response()->json($dispositivo, 201);
    }

    public function updateDispositivo(Request $request, $id)
    {
        $this->checkAdmin($request);

        $request->validate([
            'marca' => 'required|string',
            'modelo' => 'required|string',
            'tipo' => 'required|string',
            'imagen_url' => 'required|url'
        ]);

        $dispositivo = Dispositivo::findOrFail($id);
        $dispositivo->update($request->all());

        return response()->json(['message' => 'Dispositivo actualizado en el catálogo.']);
    }

    public function destroyDispositivo(Request $request, $id)
    {
        $this->checkAdmin($request);

        $dispositivo = Dispositivo::findOrFail($id);
        $dispositivo->delete();

        return response()->json(['message' => 'Dispositivo eliminado del catálogo.']);
    }
    // ==========================================
    // MÓDULO 4: PRECIOS Y SERVICIOS
    // ==========================================
    public function getServicios()
    {
        return response()->json(Servicio::all());
    }

    public function storeServicio(Request $request)
    {
        $this->checkAdmin($request);
        $data = $request->validate([
            'nombre' => 'required|string',
            'precio' => 'required|numeric',
            'icono' => 'nullable|string',
            'extra_nota' => 'nullable|string',
            'categoria' => 'required|string'
        ]);
        return response()->json(Servicio::create($data), 201);
    }

    public function updateServicio(Request $request, $id)
    {
        $this->checkAdmin($request);
        $servicio = Servicio::findOrFail($id);
        $servicio->update($request->only('precio')); // Solo actualizamos el precio rápidamente
        return response()->json(['message' => 'Precio actualizado']);
    }

    public function destroyServicio(Request $request, $id)
    {
        $this->checkAdmin($request);
        Servicio::findOrFail($id)->delete();
        return response()->json(['message' => 'Servicio eliminado']);
    }
    public function getContabilidad(Request $request)
    {
        $this->checkAdmin($request);

        // Obtenemos los registros contables ordenados por la fecha más reciente
        $registros = Contabilidad::latest()->get();

        return response()->json($registros);
    }
    public function storeContabilidad(Request $request)
    {
        $this->checkAdmin($request);

        $data = $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',
            'piezas_cambiadas' => 'nullable|string',
            'coste_piezas' => 'required|numeric',
            'total_cobrado' => 'required|numeric',
            'metodo_pago' => 'required|string'
        ]);

        // Las ganancias ya vienen calculadas desde Vue, pero por seguridad 
        // las recalculamos también en el servidor
        $data['ganancias'] = $data['total_cobrado'] - $data['coste_piezas'];

        Contabilidad::create($data);

        return response()->json(['message' => 'Asiento contable guardado correctamente']);
    }
}
