<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dispositivo;
use App\Models\Averia;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tu usuario Admin
        User::create([
            'name' => 'Admin Jose',
            'email' => 'admin@josetech.com',
            'password' => Hash::make('12345678'),
            'rol' => 'admin',
            'telefono' => '600123456'
        ]);

        // 2. Lista masiva de dispositivos reales
        $catalogo = [
            // --- APPLE (Móviles) ---
            ['marca' => 'Apple', 'modelo' => 'iPhone 15 Pro Max', 'tipo' => 'movil'],
            ['marca' => 'Apple', 'modelo' => 'iPhone 15 Pro', 'tipo' => 'movil'],
            ['marca' => 'Apple', 'modelo' => 'iPhone 15 Plus', 'tipo' => 'movil'],
            ['marca' => 'Apple', 'modelo' => 'iPhone 15', 'tipo' => 'movil'],
            ['marca' => 'Apple', 'modelo' => 'iPhone 14 Pro Max', 'tipo' => 'movil'],
            ['marca' => 'Apple', 'modelo' => 'iPhone 14 Pro', 'tipo' => 'movil'],
            ['marca' => 'Apple', 'modelo' => 'iPhone 14', 'tipo' => 'movil'],
            ['marca' => 'Apple', 'modelo' => 'iPhone 13 Pro Max', 'tipo' => 'movil'],
            ['marca' => 'Apple', 'modelo' => 'iPhone 13 Pro', 'tipo' => 'movil'],
            ['marca' => 'Apple', 'modelo' => 'iPhone 13 Mini', 'tipo' => 'movil'],
            ['marca' => 'Apple', 'modelo' => 'iPhone 12', 'tipo' => 'movil'],
            ['marca' => 'Apple', 'modelo' => 'iPhone 11', 'tipo' => 'movil'],
            ['marca' => 'Apple', 'modelo' => 'iPhone XR', 'tipo' => 'movil'],
            ['marca' => 'Apple', 'modelo' => 'iPhone SE (2022)', 'tipo' => 'movil'],

            // --- SAMSUNG (Móviles) ---
            ['marca' => 'Samsung', 'modelo' => 'Galaxy S24 Ultra', 'tipo' => 'movil'],
            ['marca' => 'Samsung', 'modelo' => 'Galaxy S24+', 'tipo' => 'movil'],
            ['marca' => 'Samsung', 'modelo' => 'Galaxy S24', 'tipo' => 'movil'],
            ['marca' => 'Samsung', 'modelo' => 'Galaxy S23 Ultra', 'tipo' => 'movil'],
            ['marca' => 'Samsung', 'modelo' => 'Galaxy S23 FE', 'tipo' => 'movil'],
            ['marca' => 'Samsung', 'modelo' => 'Galaxy S22 Ultra', 'tipo' => 'movil'],
            ['marca' => 'Samsung', 'modelo' => 'Galaxy Z Fold 5', 'tipo' => 'movil'],
            ['marca' => 'Samsung', 'modelo' => 'Galaxy Z Flip 5', 'tipo' => 'movil'],
            ['marca' => 'Samsung', 'modelo' => 'Galaxy A54 5G', 'tipo' => 'movil'],
            ['marca' => 'Samsung', 'modelo' => 'Galaxy A34', 'tipo' => 'movil'],
            ['marca' => 'Samsung', 'modelo' => 'Galaxy A14', 'tipo' => 'movil'],
            ['marca' => 'Samsung', 'modelo' => 'Galaxy M54', 'tipo' => 'movil'],

            // --- XIAOMI / POCO / REDMI (Móviles) ---
            ['marca' => 'Xiaomi', 'modelo' => '14 Ultra', 'tipo' => 'movil'],
            ['marca' => 'Xiaomi', 'modelo' => '14 Pro', 'tipo' => 'movil'],
            ['marca' => 'Xiaomi', 'modelo' => '13T Pro', 'tipo' => 'movil'],
            ['marca' => 'Xiaomi', 'modelo' => 'Redmi Note 13 Pro+', 'tipo' => 'movil'],
            ['marca' => 'Xiaomi', 'modelo' => 'Redmi Note 13 5G', 'tipo' => 'movil'],
            ['marca' => 'Xiaomi', 'modelo' => 'Redmi Note 12 Pro', 'tipo' => 'movil'],
            ['marca' => 'Xiaomi', 'modelo' => 'Redmi 12C', 'tipo' => 'movil'],
            ['marca' => 'POCO', 'modelo' => 'X6 Pro', 'tipo' => 'movil'],
            ['marca' => 'POCO', 'modelo' => 'F5 Pro', 'tipo' => 'movil'],
            ['marca' => 'POCO', 'modelo' => 'M6 Pro', 'tipo' => 'movil'],

            // --- GOOGLE / MOTOROLA / OTROS (Móviles) ---
            ['marca' => 'Google', 'modelo' => 'Pixel 8 Pro', 'tipo' => 'movil'],
            ['marca' => 'Google', 'modelo' => 'Pixel 8', 'tipo' => 'movil'],
            ['marca' => 'Google', 'modelo' => 'Pixel 7a', 'tipo' => 'movil'],
            ['marca' => 'Motorola', 'modelo' => 'Edge 40 Pro', 'tipo' => 'movil'],
            ['marca' => 'Motorola', 'modelo' => 'Moto G84', 'tipo' => 'movil'],
            ['marca' => 'OnePlus', 'modelo' => '12', 'tipo' => 'movil'],
            ['marca' => 'OnePlus', 'modelo' => 'Nord 3', 'tipo' => 'movil'],
            ['marca' => 'Realme', 'modelo' => '12 Pro+', 'tipo' => 'movil'],
            ['marca' => 'Oppo', 'modelo' => 'Find X5 Pro', 'tipo' => 'movil'],

            // --- CONSOLAS (Modernas y Retro) ---
            ['marca' => 'Sony', 'modelo' => 'PlayStation 5 (Lector)', 'tipo' => 'consola'],
            ['marca' => 'Sony', 'modelo' => 'PlayStation 5 (Digital)', 'tipo' => 'consola'],
            ['marca' => 'Sony', 'modelo' => 'PlayStation 4 Pro', 'tipo' => 'consola'],
            ['marca' => 'Sony', 'modelo' => 'PlayStation 4 Slim', 'tipo' => 'consola'],
            ['marca' => 'Sony', 'modelo' => 'PlayStation 3', 'tipo' => 'consola'],
            ['marca' => 'Microsoft', 'modelo' => 'Xbox Series X', 'tipo' => 'consola'],
            ['marca' => 'Microsoft', 'modelo' => 'Xbox Series S', 'tipo' => 'consola'],
            ['marca' => 'Microsoft', 'modelo' => 'Xbox One X', 'tipo' => 'consola'],
            ['marca' => 'Nintendo', 'modelo' => 'Switch OLED', 'tipo' => 'consola'],
            ['marca' => 'Nintendo', 'modelo' => 'Switch V2', 'tipo' => 'consola'],
            ['marca' => 'Nintendo', 'modelo' => 'Switch Lite', 'tipo' => 'consola'],
            ['marca' => 'Nintendo', 'modelo' => 'Wii U', 'tipo' => 'consola'],
            ['marca' => 'Valve', 'modelo' => 'Steam Deck OLED', 'tipo' => 'consola'],
            ['marca' => 'Asus', 'modelo' => 'ROG Ally', 'tipo' => 'consola'],

            // --- ORDENADORES APPLE ---
            ['marca' => 'Apple', 'modelo' => 'MacBook Pro 16" (M3 Max)', 'tipo' => 'ordenador'],
            ['marca' => 'Apple', 'modelo' => 'MacBook Pro 14" (M3)', 'tipo' => 'ordenador'],
            ['marca' => 'Apple', 'modelo' => 'MacBook Air 15" (M2)', 'tipo' => 'ordenador'],
            ['marca' => 'Apple', 'modelo' => 'MacBook Air 13" (M1)', 'tipo' => 'ordenador'],
            ['marca' => 'Apple', 'modelo' => 'iMac 24" (M3)', 'tipo' => 'ordenador'],
            ['marca' => 'Apple', 'modelo' => 'Mac Mini (M2)', 'tipo' => 'ordenador'],

            // --- ORDENADORES GAMING / OFIMÁTICA ---
            ['marca' => 'Asus', 'modelo' => 'ROG Zephyrus G14', 'tipo' => 'ordenador'],
            ['marca' => 'Asus', 'modelo' => 'TUF Gaming F15', 'tipo' => 'ordenador'],
            ['marca' => 'Asus', 'modelo' => 'ZenBook 14', 'tipo' => 'ordenador'],
            ['marca' => 'Lenovo', 'modelo' => 'Legion Pro 5i', 'tipo' => 'ordenador'],
            ['marca' => 'Lenovo', 'modelo' => 'IdeaPad 3', 'tipo' => 'ordenador'],
            ['marca' => 'Lenovo', 'modelo' => 'ThinkPad X1 Carbon', 'tipo' => 'ordenador'],
            ['marca' => 'HP', 'modelo' => 'Omen 16', 'tipo' => 'ordenador'],
            ['marca' => 'HP', 'modelo' => 'Victus 15', 'tipo' => 'ordenador'],
            ['marca' => 'HP', 'modelo' => 'Pavilion x360', 'tipo' => 'ordenador'],
            ['marca' => 'Dell', 'modelo' => 'XPS 15', 'tipo' => 'ordenador'],
            ['marca' => 'Dell', 'modelo' => 'Alienware m16', 'tipo' => 'ordenador'],
            ['marca' => 'Acer', 'modelo' => 'Nitro 5', 'tipo' => 'ordenador'],
            ['marca' => 'Acer', 'modelo' => 'Predator Helios', 'tipo' => 'ordenador'],
            ['marca' => 'MSI', 'modelo' => 'Katana 15', 'tipo' => 'ordenador'],
            ['marca' => 'MSI', 'modelo' => 'Stealth 16', 'tipo' => 'ordenador'],
        ];

        // 3. Bucle mágico para crearlos todos y asignarles averías
        foreach ($catalogo as $item) {
            // Creamos el dispositivo
            $dispositivo = Dispositivo::create([
                'marca' => $item['marca'],
                'modelo' => $item['modelo'],
                'tipo' => $item['tipo'],
                'imagen_url' => null // Dejamos la imagen vacía por ahora
            ]);

            // Le creamos averías genéricas con precios aleatorios realistas
            if ($item['tipo'] === 'movil') {
                Averia::create(['dispositivo_id' => $dispositivo->id, 'nombre' => 'Cambio de Pantalla Completa', 'precio' => rand(80, 250)]);
                Averia::create(['dispositivo_id' => $dispositivo->id, 'nombre' => 'Sustitución de Batería', 'precio' => rand(40, 80)]);
                Averia::create(['dispositivo_id' => $dispositivo->id, 'nombre' => 'Reparación Conector de Carga', 'precio' => rand(35, 60)]);
            } elseif ($item['tipo'] === 'consola') {
                Averia::create(['dispositivo_id' => $dispositivo->id, 'nombre' => 'Mantenimiento y Pasta Térmica', 'precio' => rand(40, 60)]);
                Averia::create(['dispositivo_id' => $dispositivo->id, 'nombre' => 'Cambio Puerto HDMI', 'precio' => rand(70, 100)]);
            } elseif ($item['tipo'] === 'ordenador') {
                Averia::create(['dispositivo_id' => $dispositivo->id, 'nombre' => 'Formateo e Instalación de SO', 'precio' => 50]);
                Averia::create(['dispositivo_id' => $dispositivo->id, 'nombre' => 'Ampliación Memoria RAM', 'precio' => rand(60, 150)]);
            }
        }
    }
}