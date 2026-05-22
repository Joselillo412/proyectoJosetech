<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class IFixitService
{
    /**
     * Se conecta a iFixit, busca el modelo y devuelve su nivel de dificultad.
     */
    public function obtenerDificultad($modelo)
    {
        try {
            // 1. Limpiamos el nombre para que la URL no se rompa con los espacios (Ej: "iPhone 13" -> "iPhone+13")
            $query = urlencode($modelo);
            
            // 2. Hacemos una petición GET al buscador oficial de iFixit, filtrando solo por "guías de reparación"
            $respuesta = Http::withoutVerifying()->get("https://www.ifixit.com/api/2.0/search/{$query}?filter=guide");

            // 3. Si iFixit nos responde bien...
            if ($respuesta->successful()) {
                $resultados = $respuesta->json()['results'];
                
                // Si ha encontrado al menos una guía para este dispositivo
                if (count($resultados) > 0) {
                    // Extraemos la dificultad de la primera guía que aparece
                    $dificultad = $resultados[0]['difficulty']; 
                    
                    // iFixit devuelve palabras en inglés como "Easy", "Moderate", "Difficult"
                    return $dificultad; 
                }
            }

            // Si no encuentra el móvil en iFixit, devolvemos un valor por defecto
            return 'Desconocida';

        } catch (\Exception $e) {
            // Si nos quedamos sin internet, que la web no se caiga
            return 'Desconocida';
        }
    }
}