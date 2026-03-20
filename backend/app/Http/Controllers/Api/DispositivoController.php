<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dispositivo;
use Illuminate\Http\Request;

class DispositivoController extends Controller
{
    /**
     * Devuelve la lista de todos los dispositivos (Para el buscador de la Home)
     */
    public function index()
    {
        // Buscamos todos los dispositivos en la base de datos
        $dispositivos = Dispositivo::all();
        
        // Los devolvemos en formato JSON con un código 200 (OK)
        return response()->json($dispositivos, 200);
    }

    /**
     * Devuelve un dispositivo concreto y sus averías (Para la vista de Presupuesto)
     */
    public function show($id)
    {
        // Buscamos el dispositivo por su ID y le "cargamos" sus averías asociadas
        $dispositivo = Dispositivo::with('averias')->find($id);

        if (!$dispositivo) {
            return response()->json(['mensaje' => 'Dispositivo no encontrado'], 404);
        }

        return response()->json($dispositivo, 200);
    }
}