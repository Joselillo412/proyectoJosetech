<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Servicio;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        $servicios = [
            ['nombre' => 'Cambio de Batería', 'precio' => 60, 'icono' => 'fa-solid fa-battery-half', 'categoria' => 'moviles'],
            ['nombre' => 'Cambio de Pantalla', 'precio' => 90, 'icono' => 'fa-solid fa-mobile-screen', 'categoria' => 'moviles', 'extra_nota' => '60€-120€ según el tipo de pantalla'],
            ['nombre' => 'Cristal Trasero', 'precio' => 55, 'icono' => 'fa-solid fa-clone', 'categoria' => 'moviles'],
            ['nombre' => 'Puerto de Carga', 'precio' => 50, 'icono' => 'fa-solid fa-plug', 'categoria' => 'moviles'],
            ['nombre' => 'Cámara Trasera', 'precio' => 70, 'icono' => 'fa-solid fa-camera', 'categoria' => 'moviles'],
            ['nombre' => 'Cámara Frontal', 'precio' => 50, 'icono' => 'fa-solid fa-camera-rotate', 'categoria' => 'moviles'],
            ['nombre' => 'Botones Encendido/Volumen', 'precio' => 50, 'icono' => 'fa-solid fa-toggle-on', 'categoria' => 'moviles'],
            ['nombre' => 'Altavoz Principal', 'precio' => 20, 'icono' => 'fa-solid fa-volume-high', 'categoria' => 'moviles'],
            ['nombre' => 'Auricular Llamadas', 'precio' => 20, 'icono' => 'fa-solid fa-phone-volume', 'categoria' => 'moviles'],
            ['nombre' => 'Mantenimiento Térmico', 'precio' => 45, 'icono' => 'fa-solid fa-fan', 'categoria' => 'consolas', 'extra_nota' => 'Limpieza + Pasta térmica/Metal Líquido'],
            ['nombre' => 'Reparación Puerto HDMI', 'precio' => 65, 'icono' => 'fa-solid fa-plug', 'categoria' => 'consolas'],
            ['nombre' => 'Reparación Lector Discos', 'precio' => 80, 'icono' => 'fa-solid fa-compact-disc', 'categoria' => 'consolas'],
            ['nombre' => 'Fallo Placa Base / Corto', 'precio' => 120, 'icono' => 'fa-solid fa-microchip', 'categoria' => 'consolas', 'extra_nota' => 'Reballing APU o sustitución de integrados'],
            ['nombre' => 'Reparación Mando/Drift', 'precio' => 25, 'icono' => 'fa-solid fa-gamepad', 'categoria' => 'consolas'],
            ['nombre' => 'Formateo SO + Drivers', 'precio' => 40, 'icono' => 'fa-brands fa-windows', 'categoria' => 'ordenadores'],
            ['nombre' => 'Limpieza Hardware', 'precio' => 35, 'icono' => 'fa-solid fa-broom', 'categoria' => 'ordenadores'],
            ['nombre' => 'Cambio Disco / SSD', 'precio' => 70, 'icono' => 'fa-solid fa-hard-drive', 'categoria' => 'ordenadores'],
            ['nombre' => 'Ampliación Memoria RAM', 'precio' => 50, 'icono' => 'fa-solid fa-memory', 'categoria' => 'ordenadores'],
        ];

        foreach ($servicios as $s) {
            Servicio::updateOrCreate(
                ['nombre' => $s['nombre'], 'categoria' => $s['categoria']],
                $s
            );
        }
    }
}
