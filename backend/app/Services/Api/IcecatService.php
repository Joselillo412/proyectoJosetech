<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\Http;

class IcecatService 
{
    /**
     * Busca información de un portátil en la inmensa base de datos de Icecat
     */
    public function obtenerDatosPortatil($marca, $modelo)
    {
        // Cogemos el usuario de tu archivo .env
        $username = env('ICECAT_USERNAME', 'openicecat-live');

        try {
            // Icecat es muy estricto con los espacios y caracteres raros, así que los limpiamos
            $marcaLimpia = urlencode(str_replace(' ', '', $marca));
            $modeloLimpio = urlencode($modelo);
            
            // Hacemos la llamada al endpoint oficial en formato JSON y en español
            $url = "https://live.icecat.biz/api/?UserName={$username}&Language=es&Brand={$marcaLimpia}&ProductCode={$modeloLimpio}";
            
            $respuesta = Http::withoutVerifying()->get($url);

            // Icecat devuelve un campo 'msg' que dice 'OK' si lo encuentra o 'FAIL' si no
            if ($respuesta->successful() && $respuesta->json('msg') === 'OK') {
                $datos = $respuesta->json('data');
                
                // Extraemos solo lo que nos interesa para Josetech
                return [
                    'nombre_oficial' => $datos['GeneralInfo']['Title'] ?? $modelo,
                    'fecha_lanzamiento' => $datos['GeneralInfo']['ReleaseDate'] ?? null,
                    'descripcion' => $datos['GeneralInfo']['Description']['LongDesc'] ?? 'Sin descripción oficial.'
                ];
            }

            return null; // No lo ha encontrado

        } catch (\Exception $e) {
            return null;
        }
    }
}