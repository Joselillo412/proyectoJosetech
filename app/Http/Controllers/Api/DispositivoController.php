<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dispositivo;
use App\Services\Api\IFixitService; 
use App\Services\Api\IcecatService; // ¡Restauramos Icecat!

class DispositivoController extends Controller
{
    public function index()
    {
        $catalogo = Dispositivo::all();
        return response()->json($catalogo);
    }

    public function show(IFixitService $ifixitService, IcecatService $icecatService, $id)
    {
        $dispositivo = Dispositivo::find($id);

        if (!$dispositivo) {
            return response()->json(['error' => 'Dispositivo no encontrado'], 404);
        }

        $datosIFixit = null;
        $datosIcecat = null;
        $sobrecoste = 0;

        // === 1. LÓGICA PARA MÓVILES Y TABLETS (iFixit) ===
        if ($dispositivo->tipo === 'Móvil' || $dispositivo->tipo === 'Tablet') {
            
            $dificultad = $ifixitService->obtenerDificultad($dispositivo->modelo);
            $dificultadLimpia = strtolower($dificultad);

            if ($dificultadLimpia == 'difficult' || $dificultadLimpia == 'very difficult') {
                $sobrecoste = 50;
            } elseif ($dificultadLimpia == 'moderate') {
                $sobrecoste = 20;
            }

            $datosIFixit = [
                'dificultad_original' => $dificultad,
                'coste_extra_mano_obra' => $sobrecoste
            ];
        }

        // === 2. LÓGICA PARA PORTÁTILES (Icecat) ===
        if ($dispositivo->tipo === 'Portátil') {
            $infoPortatil = $icecatService->obtenerDatosPortatil($dispositivo->marca, $dispositivo->modelo);
            
            if ($infoPortatil) {
                $esAntiguo = false;
                if ($infoPortatil['fecha_lanzamiento']) {
                    $anyoDespliegue = (int) date('Y', strtotime($infoPortatil['fecha_lanzamiento']));
                    $anyoActual = (int) date('Y');
                    if (($anyoActual - $anyoDespliegue) >= 5) {
                        $esAntiguo = true; // Si tiene 5 años o más, disparamos la alerta
                    }
                }

                $datosIcecat = [
                    'lanzamiento' => $infoPortatil['fecha_lanzamiento'],
                    'aviso_obsolescencia' => $esAntiguo,
                    'detalles' => $infoPortatil['nombre_oficial']
                ];
            }
        }

        return response()->json([
            'dispositivo' => $dispositivo,
            'api_ifixit' => $datosIFixit,
            'api_icecat' => $datosIcecat // Enviamos los datos de Icecat a Vue
        ]);
    }
}