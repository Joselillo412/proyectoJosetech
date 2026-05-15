<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\Http;

class IFixitService 
{
    public function obtenerDificultad($modelo)
    {
        try {
            $query = urlencode($modelo);
            
            // Ruta pública de iFixit
            $respuesta = Http::withoutVerifying()->get("https://www.ifixit.com/api/2.0/search/{$query}?filter=guide");

            if ($respuesta->successful()) {
                $resultados = $respuesta->json()['results'];
                
                if (count($resultados) > 0) {
                    return $resultados[0]['difficulty']; 
                }
            }
            return 'Desconocida';

        } catch (\Exception $e) {
            return 'Desconocida';
        }
    }
}