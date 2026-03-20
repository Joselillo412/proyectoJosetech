<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * REGISTRO DE NUEVO CLIENTE
     */
    public function register(Request $request)
    {
        // 1. Validamos los datos que llegan desde Vue
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'telefono' => 'nullable|string|max:20',
        ]);

        // 2. Creamos el usuario en la base de datos (por defecto rol 'cliente')
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Encriptamos la contraseña
            'telefono' => $request->telefono,
            'rol' => 'cliente',
        ]);

        // 3. Generamos su Token de acceso
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'mensaje' => 'Usuario registrado con éxito',
            'usuario' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * INICIO DE SESIÓN
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Buscamos al usuario por su email
        $user = User::where('email', $request->email)->first();

        // Comprobamos si el usuario existe y si la contraseña coincide
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Borramos tokens antiguos por seguridad (opcional, pero buena práctica)
        $user->tokens()->delete();

        // Generamos un nuevo token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'mensaje' => 'Inicio de sesión correcto',
            'usuario' => $user,
            'token' => $token
        ], 200);
    }

    /**
     * CERRAR SESIÓN
     */
    public function logout(Request $request)
    {
        // Destruimos el token que está usando actualmente
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'mensaje' => 'Sesión cerrada correctamente'
        ], 200);
    }
}